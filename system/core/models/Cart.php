<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Logger;
use core\helpers\Cache;
use core\helpers\Request;
use core\models\Sku as SkuModel;
use core\models\User as UserModel;
use core\models\Product as ProductModel;
use core\models\Currency as CurrencyModel;
use core\models\Language as LanguageModel;
use core\models\Wishlist as WishlistModel;

/**
 * Manages basic behaviors and data related to user carts
 */
class Cart extends Model
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Wishlist model instance
     * @var \core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Request model instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param ProductModel $product
     * @param SkuModel $sku
     * @param CurrencyModel $currency
     * @param UserModel $user
     * @param WishlistModel $wishlist
     * @param LanguageModel $language
     * @param Request $request
     * @param Logger $logger
     */
    public function __construct(
    ProductModel $product, SkuModel $sku, CurrencyModel $currency,
            UserModel $user, WishlistModel $wishlist,
            LanguageModel $language, Request $request, Logger $logger
    )
    {
        parent::__construct();

        $this->sku = $sku;
        $this->user = $user;
        $this->logger = $logger;
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
        $cache_key = 'cart.' . md5(json_encode($data));

        $cart = &Cache::memory($cache_key);

        if (isset($cart)) {
            return $cart;
        }

        $items = $this->getList($data);

        if (empty($items)) {
            return array();
        }

        $total = 0;
        $quantity = 0;
        $current_currency = $this->currency->get();

        $cart = array();
        foreach ($items as $sku => $item) {

            $item['product'] = $this->product->getBySku($item['sku'], $item['store_id']);

            if (empty($item['product']['status'])) {
                continue; // Invalid / disabled product
            }

            if (isset($data['store_id']) && $data['store_id'] != $item['product']['store_id']) {
                continue; // Store has been changed for this product
            }

            $currency = $item['product']['currency'];
            $price = $this->currency->convert($item['product']['price'], $currency, $current_currency);

            $item['price'] = $price;
            $item['total'] = $item['price'] * $item['quantity'];

            $total += (int) $item['total'];
            $quantity += (int) $item['quantity'];

            $cart['items'][$sku] = $item;
        }

        $cart['total'] = $total;
        $cart['quantity'] = $quantity;
        $cart['currency'] = $current_currency;

        $this->hook->fire('get.cart.after', $data, $cart);
        return $cart;
    }

    /**
     * Returns an array of cart items
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . ' p.status AS product_status, p.store_id AS product_store_id'
                . ' FROM cart c'
                . ' LEFT JOIN product p ON(c.product_id=p.product_id)'
                . ' LEFT JOIN product_translation pt ON(c.product_id = pt.product_id AND pt.language=?)'
                . ' WHERE cart_id > 0';

        $where = array($this->language->current());

        $data += array('order_id' => 0);

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

        $sql .= ' ORDER BY c.created DESC';

        $options = array('unserialize' => 'data', 'index' => 'sku');
        return $this->db->fetchAll($sql, $where, $options);
    }

    /**
     * Returns cart limit(s)
     * @param null|string $item
     * @return array|int
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
     * Adds a product to the cart
     * @param array $product
     * @param array $data
     * @return array
     */
    public function addProduct(array $product, array $data)
    {
        $this->hook->fire('add.product.cart.before', $product, $data);

        $result = array(
            'severity' => 'warning',
            'message' => $this->language->text('Product has not been added')
        );

        if (empty($product) || empty($data)) {
            return $result;
        }

        if (!empty($product['combination']) && empty($data['combination_id'])) {

            return array(
                'severity' => 'warning',
                'redirect' => "product/{$product['product_id']}",
                'message' => $this->language->text('Please select product options before adding to the cart')
            );
        }

        $data += array(
            'quantity' => 1,
            'user_id' => $this->uid(),
            'store_id' => $product['store_id'],
            'product_id' => $product['product_id']
        );

        $cart_id = $this->setProduct($data);

        if (!empty($cart_id)) {

            $options = array('user_id' => $data['user_id'], 'store_id' => $data['store_id']);
            $existing = $this->getQuantity($options);

            $result = array(
                'cart_id' => $cart_id,
                'severity' => 'success',
                'quantity' => $existing['total'],
                'message' => $this->language->text('Product has been added to your cart. <a href="!href">Checkout</a>', array(
                    '!href' => $this->request->base() . 'checkout'
                ))
            );

            $this->logAddToCart($product, $data);
        }

        $this->hook->fire('add.product.cart.after', $product, $data, $result);
        return $result;
    }

    /**
     * Returns a cart user ID
     * @return string
     */
    public function uid()
    {
        $user_id = $this->user->id();

        if (!empty($user_id)) {
            return (string) $user_id;
        }

        $cookie_name = $this->config->get('user_cookie_name', 'user_id');
        $user_id = $this->request->cookie($cookie_name);

        if (!empty($user_id)) {
            return (string) $user_id;
        }

        $user_id = '_' . String::random(6); // Add prefix to prevent from being "numeric"
        $this->request->setCookie($cookie_name, $user_id, $this->config->get('cart_cookie_lifespan', 31536000));
        return (string) $user_id;
    }

    /**
     * Adds/updates products in the cart
     * @param array $data
     * @return integer
     */
    protected function setProduct(array $data)
    {
        $sql = 'SELECT cart_id, quantity'
                . ' FROM cart'
                . ' WHERE sku=?'
                . ' AND user_id=?'
                . ' AND store_id=?'
                . ' AND order_id=?';

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
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.cart.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data['cart_id'] = $this->db->insert('cart', $data);

        Cache::clearMemory();

        $this->hook->fire('add.cart.after', $data);
        return $data['cart_id'];
    }

    /**
     * Updates a cart
     * @param integer $cart_id
     * @param array $data
     * @return boolean
     */
    public function update($cart_id, array $data)
    {
        $this->hook->fire('update.cart.before', $cart_id, $data);

        if (empty($cart_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;
        $result = $this->db->update('cart', $data, array('cart_id' => $cart_id));

        Cache::clearMemory();

        $this->hook->fire('update.cart.after', $cart_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array containing total number of products
     * and number of products per SKU for the given user and store
     * @param array $conditions
     * @param null|string $key
     * @return array|integer
     */
    public function getQuantity(array $conditions, $key = null)
    {
        $items = $this->getList($conditions);
        $result = array('total' => 0, 'sku' => array());

        foreach ($items as $item) {
            $result['total'] += (int) $item['quantity'];
            $result['sku'][$item['sku']] = (int) $item['quantity'];
        }

        if (isset($key)) {
            return $result[$key];
        }

        return $result;
    }

    /**
     * Logs adding products to the cart
     * @param array $product
     * @param array $data
     * @return boolean
     */
    protected function logAddToCart(array $product, array $data)
    {
        $log = array(
            'message' => 'User %uid added product <a target="_blank" href="@url">@product</a> (SKU: %sku) at store #%store',
            'variables' => array(
                '%sku' => $data['sku'],
                '%store' => $product['store_id'],
                '@url' => $this->request->base() . "product/{$product['product_id']}",
                '@product' => $product['product_id'],
                '%uid' => is_numeric($data['user_id']) ? $data['user_id'] : '**anonymous**'
            )
        );

        return $this->logger->log('cart', $log);
    }

    /**
     * Loads a cart from the database
     * @param integer $cart_id
     * @return array
     */
    public function get($cart_id)
    {
        return $this->db->fetch('SELECT * FROM cart WHERE cart_id=?', array($cart_id));
    }

    /**
     * Moves a cart item to the wishlist
     * @param array $data
     * @return array
     */
    public function moveToWishlist(array $data)
    {
        $this->hook->fire('move.cart.wishlist.before', $data);

        $result = array('redirect' => null, 'severity' => '', 'message' => '');

        if (empty($data)) {
            return $result;
        }

        $skuinfo = $this->sku->get($data['sku'], $data['store_id']);

        if (empty($skuinfo['product_id'])) {
            return $result;
        }

        $this->db->delete('cart', $data);

        $data['product_id'] = $skuinfo['product_id'];

        $conditions = $data;
        unset($conditions['sku']);
        $this->db->delete('wishlist', $conditions);

        $data['wishlist_id'] = $this->wishlist->addProduct($data);

        Cache::clearMemory();

        $url = $this->request->base() . 'wishlist';
        $message = $this->language->text('Product has been moved to your <a href="!href">wishlist</a>', array('!href' => $url));

        $result = array(
            'redirect' => '',
            'message' => $message,
            'severity' => 'success',
            'wishlist_id' => $data['wishlist_id']
        );

        $this->hook->fire('move.cart.wishlist.after', $data, $result);
        return $result;
    }

    /**
     * Performs all needed tastks when customer logged in during checkout
     * @param array $user
     * @param array $cart
     * @return array
     */
    public function login(array $user, array $cart)
    {
        $this->hook->fire('cart.login.before', $user, $cart);
        $result = array('redirect' => null, 'severity' => '', 'message' => '');

        if (empty($user) || empty($cart)) {
            return $result;
        }

        $conditions = array('user_id' => $user['user_id']);

        if (!$this->config->get('cart_login_merge', 0)) {
            $this->delete($conditions);
        }

        foreach ($cart['items'] as $item) {
            $this->update($item['cart_id'], $conditions);
        }

        $this->deleteCookie();
        $this->logLoginCheckout($user);

        $result = array(
            'user' => $user,
            'redirect' => 'checkout',
            'severity' => 'success',
            'message' => $this->language->text('Hello, %name. Now you\'re logged in', array(
                '%name' => $user['name']
            ))
        );

        $this->hook->fire('cart.login.after', $user, $cart, $result);
        return $result;
    }

    /**
     * Deletes a cart record from the database
     * @param array $arguments
     * @return boolean
     */
    public function delete(array $arguments)
    {
        $this->hook->fire('delete.cart.before', $arguments);

        if (empty($arguments)) {
            return false;
        }

        // Items with order_id = 0 are not linked to orders,
        // i.e before checkout
        $arguments += array('order_id' => 0);

        if (empty($arguments['user_id'])) {
            $conditions = array('cart_id' => $arguments['cart_id']);
        } else {

            $conditions = array(
                'user_id' => $arguments['user_id'],
                'order_id' => $arguments['order_id']
            );
        }

        $result = (bool) $this->db->delete('cart', $conditions);

        Cache::clearMemory();

        $this->hook->fire('delete.cart.after', $arguments, $result);
        return (bool) $result;
    }

    /**
     * Deletes a cart from the cookie
     * @return boolean
     */
    public function deleteCookie()
    {
        $cookie = $this->config->get('user_cookie_name', 'user_id');
        return $this->request->deleteCookie($cookie);
    }

    /**
     * Logs logging in during checkout
     * @param array $user
     */
    protected function logLoginCheckout(array $user)
    {
        $log = array(
            'message' => 'User has logged in during checkout using %email',
            'variables' => array('%email' => $user['email'])
        );

        $this->logger->log('checkout', $log);
    }

}
