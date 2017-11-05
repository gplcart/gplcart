<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\models\Sku as SkuModel,
    gplcart\core\models\User as UserModel,
    gplcart\core\models\Product as ProductModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\Wishlist as WishlistModel;
use gplcart\core\helpers\Request as RequestHelper;

/**
 * Manages basic behaviors and data related to user carts
 */
class Cart
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
     * SKU model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

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
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Request model instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param ProductModel $product
     * @param SkuModel $sku
     * @param CurrencyModel $currency
     * @param UserModel $user
     * @param WishlistModel $wishlist
     * @param LanguageModel $language
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Database $db, Config $config, ProductModel $product,
            SkuModel $sku, CurrencyModel $currency, UserModel $user, WishlistModel $wishlist,
            LanguageModel $language, RequestHelper $request)
    {

        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;

        $this->sku = $sku;
        $this->user = $user;
        $this->product = $product;
        $this->request = $request;
        $this->currency = $currency;
        $this->wishlist = $wishlist;
        $this->language = $language;
    }

    /**
     * Returns a cart content for a given user ID
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

        $total = 0;
        $quantity = 0;
        foreach ((array) $items as $sku => $item) {

            $prepared = $this->prepareItem($item, $result);

            if (empty($prepared)) {
                continue;
            }

            $result['items'][$sku] = $prepared;
            $total += (int) $prepared['total'];
            $quantity += (int) $prepared['quantity'];
        }

        $result['total'] = $total;
        $result['quantity'] = $quantity;

        $this->hook->attach('cart.get.content.after', $data, $result, $this);
        return $result;
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

        if ($calculated['total'] != $product['price']) {
            $item['original_price'] = $product['price'];
        }

        $item['product'] = $product;
        $item['price'] = $calculated['total'];
        $item['total'] = $item['price'] * $item['quantity'];

        return $item;
    }

    /**
     * Returns an array of cart items or counts them
     * @param array $data
     * @param string $index
     * @return array|integer
     */
    public function getList(array $data = array(), $index = 'sku')
    {
        $sql = 'SELECT c.*, COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . ' p.status AS product_status, p.store_id AS product_store_id,'
                . ' u.email AS user_email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(c.cart_id)';
        }

        $sql .= ' FROM cart c'
                . ' LEFT JOIN product p ON(c.product_id=p.product_id)'
                . ' LEFT JOIN product_translation pt ON(c.product_id = pt.product_id AND pt.language=?)'
                . ' LEFT JOIN user u ON(c.user_id = u.user_id)'
                . ' WHERE cart_id > 0';

        $where = array($this->language->getLangcode());

        if (isset($data['user_id'])) {
            $sql .= ' AND c.user_id=?';
            $where[] = $data['user_id'];
        }

        if (isset($data['order_id'])) {
            $sql .= ' AND c.order_id=?';
            $where[] = (int) $data['order_id'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND c.store_id=?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['user_email'])) {
            $sql .= ' AND u.email LIKE ?';
            $where[] = "%{$data['user_email']}%";
        }

        if (isset($data['sku'])) {
            $sql .= ' AND c.sku LIKE ?';
            $where[] = "%{$data['sku']}%";
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'sku' => 'c.sku',
            'created' => 'c.created',
            'user_id' => 'c.user_id',
            'user_email' => 'u.email',
            'store_id' => 'c.store_id',
            'order_id' => 'c.order_id',
            'quantity' => 'c.quantity',
            'product_id' => 'c.product_id'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= ' ORDER BY c.modified DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('unserialize' => 'data', 'index' => $index);
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->attach('cart.list', $list, $this);
        return $list;
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
     * Adds a product to the current cart
     * @param array $product
     * @param array $data
     * @return array
     */
    public function addProduct(array $product, array $data)
    {
        $result = array();
        $this->hook->attach('cart.add.product.before', $product, $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Unable to add product')
        );

        $data += array(
            'quantity' => 1,
            'user_id' => $this->getUid(),
            'store_id' => $product['store_id'],
            'product_id' => $product['product_id']
        );

        $cart_id = $this->setProduct($data);

        if (!empty($cart_id)) {

            $options = array(
                'user_id' => $data['user_id'],
                'store_id' => $data['store_id']
            );

            $existing = $this->getQuantity($options);
            $vars = array('@url' => $this->request->base() . 'checkout');

            $result = array(
                'redirect' => '',
                'cart_id' => $cart_id,
                'severity' => 'success',
                'quantity' => $existing['total'],
                'message' => $this->language->text('Product has been added to your cart. <a href="@url">Checkout</a>', $vars)
            );
        }

        $this->hook->attach('cart.add.product.after', $product, $data, $result, $this);
        return (array) $result;
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

        $user_id = '_' . gplcart_string_random(6);
        $lifespan = $this->getCookieLifespan();
        $this->request->setCookie($cookie_name, $user_id, $lifespan);
        return $user_id;
    }

    /**
     * Adds/updates products in cart
     * @param array $data
     * @return integer|boolean
     */
    protected function setProduct(array $data)
    {
        $sql = 'SELECT cart_id, quantity'
                . ' FROM cart'
                . ' WHERE sku=? AND user_id=? AND store_id=? AND order_id=?';

        $conditions = array($data['sku'], $data['user_id'], $data['store_id'], 0);
        $existing = $this->db->fetch($sql, $conditions);

        if (empty($existing['cart_id'])) {
            return $this->add($data);
        }

        $existing['quantity'] += $data['quantity'];

        $conditions2 = array('quantity' => $existing['quantity']);
        $this->update($existing['cart_id'], $conditions2);

        return $existing['cart_id'];
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
     * Returns an array containing a total number of products
     * and a number of products per SKU for the given user and store
     * @param array $options
     * @return array|integer
     */
    public function getQuantity(array $options)
    {
        $options += array('order_id' => 0);

        $items = $this->getList($options);
        $result = array('total' => 0, 'sku' => array());

        foreach ((array) $items as $item) {
            $result['total'] += (int) $item['quantity'];
            $result['sku'][$item['sku']] = (int) $item['quantity'];
        }

        return $result;
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

        $result = $this->db->fetch('SELECT * FROM cart WHERE cart_id=?', array($cart_id));

        $this->hook->attach('cart.get.after', $cart_id, $result, $this);
        return $result;
    }

    /**
     * Moves a cart item to the wishlist
     * @param array $data
     * @return array
     */
    public function moveToWishlist(array $data)
    {
        $result = array();
        $this->hook->attach('cart.move.wishlist.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $sku_info = $this->sku->get($data['sku'], $data['store_id']);

        if (empty($sku_info['product_id'])) {
            return array('redirect' => null, 'severity' => '', 'message' => '');
        }

        $this->db->delete('cart', $data);

        $data['product_id'] = $sku_info['product_id'];

        $conditions = $data;
        unset($conditions['sku']);
        $this->db->delete('wishlist', $conditions);

        $data['wishlist_id'] = $this->wishlist->addProduct($data);

        gplcart_static_clear();

        $url = $this->request->base() . 'wishlist';
        $message = $this->language->text('Product has been moved to your <a href="@url">wishlist</a>', array('@url' => $url));

        $result = array(
            'redirect' => '',
            'message' => $message,
            'severity' => 'success',
            'wishlist_id' => $data['wishlist_id']
        );

        $this->hook->attach('cart.move.wishlist.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Performs all needed tasks when customer logged in during checkout
     * @param array $user
     * @param array $cart
     * @return array
     */
    public function login(array $user, array $cart)
    {
        $result = array();
        $this->hook->attach('cart.login.before', $user, $cart, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (!$this->config->get('cart_login_merge', 0)) {
            $this->clear($user['user_id']);
        }

        if (!empty($cart['items'])) {
            foreach ($cart['items'] as $item) {
                $this->update($item['cart_id'], array('user_id' => $user['user_id']));
            }
        }

        $this->deleteCookie();

        $result = array(
            'user' => $user,
            'redirect' => 'checkout',
            'severity' => 'success',
            'message' => $this->language->text('Hello, %name. Now you are logged in', array(
                '%name' => $user['name']
            ))
        );

        $this->hook->attach('cart.login.after', $user, $cart, $result, $this);
        return (array) $result;
    }

    /**
     * Deletes a cart record from the database
     * @param integer $cart_id
     * @param bool $check
     * @return boolean
     */
    public function delete($cart_id, $check = true)
    {
        $result = null;
        $this->hook->attach('cart.delete.before', $cart_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($cart_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('cart', array('cart_id' => $cart_id));

        gplcart_static_clear();

        $this->hook->attach('cart.delete.after', $cart_id, $check, $result, $this);

        return (bool) $result;
    }

    /**
     * Delete all non-referenced cart items for the user ID
     * @param string $user_id
     * @return boolean
     */
    public function clear($user_id)
    {
        $result = null;
        $this->hook->attach('cart.clear.before', $user_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }


        $sql = 'DELETE FROM cart WHERE user_id=? AND order_id = 0';
        $result = (bool) $this->db->run($sql, array($user_id))->rowCount();

        $this->hook->attach('cart.clear.after', $user_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a cart item can be deleted
     * @param integer $cart_id
     * @return boolean
     */
    public function canDelete($cart_id)
    {
        $sql = 'SELECT order_id FROM cart WHERE cart_id=?';
        $result = $this->db->fetchColumn($sql, array($cart_id));

        return empty($result);
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

}
