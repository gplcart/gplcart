<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\User as UserModel;

/**
 * Manages basic behaviors and data related to shopping carts
 */
class Cart implements CrudInterface
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Translation model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Request model instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param ProductModel $product
     * @param CurrencyModel $currency
     * @param UserModel $user
     * @param TranslationModel $translation
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Config $config, ProductModel $product,
                                CurrencyModel $currency, UserModel $user,
                                TranslationModel $translation, RequestHelper $request)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->user = $user;
        $this->product = $product;
        $this->request = $request;
        $this->currency = $currency;
        $this->translation = $translation;
    }

    /**
     * Adds a cart record to the database
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('cart.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $this->db->insert('cart', $data);

        gplcart_static_clear();

        $this->hook->attach('cart.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a cart
     * @param integer $cart_id
     * @param array $data
     * @return boolean
     */
    public function update($cart_id, array $data)
    {
        $result = null;
        $this->hook->attach('cart.update.before', $cart_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $result = (bool) $this->db->update('cart', $data, array('cart_id' => $cart_id));

        gplcart_static_clear();

        $this->hook->attach('cart.update.after', $cart_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Loads a cart from the database
     * @param integer $cart_id
     * @return array
     */
    public function get($cart_id)
    {
        $result = null;
        $this->hook->attach('cart.get.before', $cart_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM cart WHERE cart_id=?';
        $result = $this->db->fetch($sql, array($cart_id), array('unserialize' => 'data'));

        $this->hook->attach('cart.get.after', $cart_id, $result, $this);
        return $result;
    }

    /**
     * Deletes a cart record from the database
     * @param integer|array $condition
     * @param bool $check
     * @return boolean
     */
    public function delete($condition, $check = true)
    {
        $result = null;
        $this->hook->attach('cart.delete.before', $condition, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('cart_id' => $condition);
        }

        if ($check && isset($condition['cart_id']) && !$this->canDelete($condition['cart_id'])) {
            return false;
        }

        $result = (bool) $this->db->delete('cart', $condition);
        gplcart_static_clear();

        $this->hook->attach('cart.delete.after', $condition, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns a cart content
     * @param array $data
     * @return array
     */
    public function getContent(array $data)
    {
        $result = &gplcart_static(gplcart_array_hash(array('cart.get.content' => $data)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('cart.get.content.before', $data, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $data['order_id'] = 0;

        $items = $this->getList($data);

        if (empty($items)) {
            return array();
        }

        $result = array(
            'store_id' => $data['store_id'],
            'currency' => $this->currency->getCode()
        );

        $total = $quantity = 0;

        foreach ((array) $items as $item) {
            $prepared = $this->prepareItem($item, $result);
            if (!empty($prepared)) {
                $result['items'][$item['sku']] = $prepared;
                $total += (int) $prepared['total'];
                $quantity += (int) $prepared['quantity'];
            }
        }

        $result['total'] = $total;
        $result['quantity'] = $quantity;

        $this->hook->attach('cart.get.content.after', $data, $result, $this);
        return $result;
    }

    /**
     * Returns an array of cart items or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array(
            'index' => 'cart_id',
            'language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('cart.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT c.*, COALESCE(NULLIF(pt.title, ""), p.title) AS title,
                p.status AS product_status, p.store_id AS product_store_id,
                u.email AS user_email';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(c.cart_id)';
        }

        $sql .= ' FROM cart c
                  LEFT JOIN product p ON(c.product_id=p.product_id)
                  LEFT JOIN product_translation pt ON(c.product_id = pt.product_id AND pt.language=?)
                  LEFT JOIN user u ON(c.user_id = u.user_id)
                  WHERE cart_id IS NOT NULL';

        $conditions = array($options['language']);

        if (isset($options['user_id'])) {
            $sql .= ' AND c.user_id=?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['user_email'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$options['user_email']}%";
        }

        if (isset($options['order_id'])) {
            $sql .= ' AND c.order_id=?';
            $conditions[] = $options['order_id'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND c.store_id=?';
            $conditions[] = $options['store_id'];
        }

        if (isset($options['sku'])) {
            $sql .= ' AND c.sku=?';
            $conditions[] = $options['sku'];
        }

        if (isset($options['sku_like'])) {
            $sql .= ' AND c.sku LIKE ?';
            $conditions[] = "%{$options['sku_like']}%";
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array('sku' => 'c.sku', 'created' => 'c.created', 'modified' => 'c.modified',
            'user_id' => 'c.user_id', 'user_email' => 'u.email', 'store_id' => 'c.store_id',
            'order_id' => 'c.order_id', 'quantity' => 'c.quantity', 'product_id' => 'c.product_id'
        );

        if (isset($options['sort'])
            && isset($allowed_sort[$options['sort']])
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= ' ORDER BY c.modified DESC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('unserialize' => 'data', 'index' => $options['index']));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('cart.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns the cart user ID
     * @return string
     */
    public function getUid()
    {
        $session_user_id = $this->user->getId();

        if (!empty($session_user_id)) {
            return (string) $session_user_id;
        }

        $cookie_name = $this->getCookieUserName();
        $cookie_user_id = $this->request->cookie($cookie_name, '', 'string');

        if (!empty($cookie_user_id)) {
            return $cookie_user_id;
        }

        $generated_user_id = '_' . gplcart_string_random(6);
        $this->request->setCookie($cookie_name, $generated_user_id, $this->getCookieLifespan());
        return $generated_user_id;
    }

    /**
     * Whether a cart item can be deleted
     * @param integer $cart_id
     * @return boolean
     */
    public function canDelete($cart_id)
    {
        $result = $this->db->fetchColumn('SELECT order_id FROM cart WHERE cart_id=?', array($cart_id));
        return empty($result);
    }

    /**
     * Returns cart quantity
     * @param array $options
     * @param string $type
     * @return array|integer
     */
    public function getQuantity(array $options, $type = null)
    {
        $options += array('order_id' => 0);

        $result = array(
            'total' => 0,
            'sku' => array()
        );

        foreach ((array) $this->getList($options) as $item) {
            $result['total'] += (int) $item['quantity'];
            $result['sku'][$item['sku']] = (int) $item['quantity'];
        }

        if (isset($type)) {
            return $result[$type];
        }

        return $result;
    }

    /**
     * Returns cart limits
     * @param null|string $item
     * @return array|integer
     */
    public function getLimits($item = null)
    {
        $limits = array(
            'sku' => (int) $this->config->get('cart_sku_limit', 10),
            'item' => (int) $this->config->get('cart_item_limit', 20)
        );

        return isset($item) ? $limits[$item] : $limits;
    }

    /**
     * Deletes a cart user id from cookie
     * @return boolean
     */
    public function deleteCookie()
    {
        return $this->request->deleteCookie($this->getCookieUserName());
    }

    /**
     * Returns cookie name used to store the current user ID
     * @return string
     */
    public function getCookieUserName()
    {
        return $this->config->get('user_cookie_name', 'user_id');
    }

    /**
     * Returns cart cookie lifespan
     * @return integer
     */
    public function getCookieLifespan()
    {
        return $this->config->get('cart_cookie_lifespan', 365 * 24 * 60 * 60);
    }

    /**
     * Prepare a cart item
     * @param array $item
     * @param array $data
     * @return array
     */
    protected function prepareItem(array $item, array $data)
    {
        $product = $this->product->getBySku($item['sku'], $item['store_id']);

        if (empty($product['status']) || $data['store_id'] != $product['store_id']) {
            return array();
        }

        $product['price'] = $this->currency->convert($product['price'], $product['currency'], $data['currency']);

        $calculated = $this->product->calculate($product);

        if ($calculated != $product['price']) {
            $item['original_price'] = $product['price'];
        }

        $item['product'] = $product;
        $item['price'] = $calculated;
        $item['total'] = $item['price'] * $item['quantity'];

        return $item;
    }

}
