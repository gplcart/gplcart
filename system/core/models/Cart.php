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
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Request;
use core\models\Sku as ModelsSku;
use core\models\User as ModelsUser;
use core\models\Product as ModelsProduct;
use core\models\Currency as ModelsCurrency;
use core\models\Language as ModelsLanguage;
use core\models\Wishlist as ModelsWishlist;

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
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsSku $sku
     * @param ModelsCurrency $currency
     * @param ModelsUser $user
     * @param ModelsWishlist $wishlist
     * @param ModelsLanguage $language
     * @param Request $request
     * @param Logger $logger
     */
    public function __construct(ModelsProduct $product, ModelsSku $sku,
            ModelsCurrency $currency, ModelsUser $user,
            ModelsWishlist $wishlist, ModelsLanguage $language,
            Request $request, Logger $logger)
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
     * @param string|integer $user_id
     * @param integer $store_id
     * @return array
     */
    public function getByUser($user_id, $store_id)
    {
        $cart = &Cache::memory("cart.$user_id.$store_id");

        if (isset($cart)) {
            return $cart;
        }

        $options = array(
            'user_id' => $user_id,
            'store_id' => $store_id
        );

        $products = $this->getList($options);

        if (empty($products)) {
            return array();
        }

        $total = 0;
        $quantity = 0;
        $current_currency = $this->currency->get();

        $cart = array();
        foreach ($products as $cart_id => $item) {

            if ($item['store_id'] != $store_id) {
                continue;
            }

            $item['product'] = $this->product->getBySku($item['sku'], $item['store_id']);

            if (empty($item['product']['status'])) {
                continue; // Invalid / disabled product
            }

            if ($store_id != $item['product']['store_id']) {
                continue; // Store has been changed for this product
            }

            $price = $item['product']['price'];
            $currency = $item['product']['currency'];

            if (empty($item['product']['combination_id'])) {
                $price = $this->currency->convert($price, $currency, $current_currency);
            } elseif (!empty($item['product']['option_file_id'])) {
                $price = $this->currency->convert($item['product']['option_price'], $currency, $current_currency);
            }

            $item['price'] = $price;
            $item['total'] = $item['price'] * $item['quantity'];

            $total += (int) $item['total'];
            $quantity += (int) $item['quantity'];

            $cart['items'][$cart_id] = $item;
        }

        $cart['total'] = $total;
        $cart['quantity'] = $quantity;
        $cart['currency'] = $current_currency;

        $this->hook->fire('get.cart.after', $user_id, $cart);

        return $cart;
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

        $user_id = '_' . Tool::randomString(6); // Add prefix to prevent from being "numeric"
        Tool::setCookie($cookie_name, $user_id, $this->config->get('cart_cookie_lifespan', 31536000));
        return (string) $user_id;
    }

    /**
     * Returns an array of cart items
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {

        $sql = 'SELECT * FROM cart WHERE cart_id > 0';

        $where = array();

        $data += array('order_id' => 0);

        if (isset($data['user_id'])) {
            $sql .= ' AND user_id=?';
            $where[] = $data['user_id'];
        }

        if (isset($data['order_id'])) {
            $sql .= ' AND order_id=?';
            $where[] = (int) $data['order_id'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id=?';
            $where[] = (int) $data['store_id'];
        }

        $sql .= ' ORDER BY created DESC';

        $options = array('unserialize' => 'data', 'index' => 'sku');
        return $this->db->fetchAll($sql, $where, $options);
    }

    /**
     * Returns an array containing total number of products
     * and number of products per SKU for the given user and store
     * @param string $user_id
     * @param integer $store_id
     * @return array
     */
    public function getQuantity($user_id, $store_id)
    {
        $conditions = array(
            'user_id' => $user_id,
            'store_id' => $store_id
        );

        $items = $this->getList($conditions);

        $result = array('total' => 0, 'sku' => array());

        foreach ($items as $item) {
            $result['total'] += (int) $item['quantity'];
            $result['sku'][$item['sku']] = (int) $item['quantity'];
        }

        return $result;
    }

    /**
     * Returns cart limit(s)
     * @return mixed
     */
    public function getLimits($item = null)
    {
        $limits = array(
            'sku' => (int) $this->config->get('cart_sku_limit', 10),
            'total' => (int) $this->config->get('cart_total_limit', 20)
        );

        return isset($item) ? $limits[$item] : $limits;
    }

    /**
     * Adds a product to the cart
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
                'redirect' => $this->request->base() . "product/{$product['product_id']}",
                'message' => $this->text('Please select product options before adding to the cart')
            );
        }

        $data += array(
            'quantity' => 1,
            'user_id' => $this->uid(),
            'store_id' => $product['store_id']
        );

        $cart_id = $this->setProduct($data);

        if (!empty($cart_id)) {

            $existing = $this->getQuantity($data['user_id'], $data['store_id']);

            $result = array(
                'cart_id' => $cart_id,
                'severity' => 'success',
                'quantity' => $existing['total'],
                'message' => $this->language->text('Product has been added to your cart. <a href="!href">Checkout</a>', array(
                    '!href' => $this->request->base() . 'checkout'))
            );

            $this->logAddToCart($product, $data);
        }

        $this->hook->fire('add.product.cart.after', $product, $data, $result);
        return $result;
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

        $data += array('modified' => GC_TIME);
        $result = $this->db->update('cart', $data, array('cart_id' => $cart_id));

        Cache::clearMemory();

        $this->hook->fire('update.cart.after', $cart_id, $data, $result);

        return (bool) $result;
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

        $data += array('created' => GC_TIME);

        $data['cart_id'] = $this->db->insert('cart', $data);

        Cache::clearMemory();

        $this->hook->fire('add.cart.after', $data);
        return $data['cart_id'];
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

    /**
     * Moves a cart item to the wishlist
     * @param string $sku
     * @param string|integer $user_id
     * @param insteger $store_id
     * @return boolean
     */
    public function moveToWishlist($sku, $user_id, $store_id)
    {
        $this->hook->fire('move.cart.wishlist.before', $sku, $user_id, $store_id);

        if (empty($sku)) {
            return false;
        }

        $skuinfo = $this->sku->get($sku, $store_id);

        if (empty($skuinfo['product_id'])) {
            return false;
        }

        $data = array(
            'user_id' => $user_id,
            'store_id' => $store_id,
            'product_id' => $skuinfo['product_id']
        );

        $this->db->delete('wishlist', $data);
        $wishlist_id = $this->wishlist->addProduct($data);

        $conditions = array(
            'sku' => $sku,
            'user_id' => $user_id
        );

        $this->db->delete('cart', $conditions);

        Cache::clearMemory();

        $this->hook->fire('move.cart.wishlist.after', $sku, $user_id, $store_id);
        return $wishlist_id;
    }

    /**
     * Performs all needed tastks when customer logged in during checkout
     * @param array $user
     * @param array $cart
     */
    public function login(array $user, array $cart)
    {
        $this->hook->fire('cart.login.before', $user, $cart);

        if (empty($user) || empty($cart)) {
            return false;
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

        $this->hook->fire('cart.login.after', $user, $cart);
        return true;
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
                'order_id' => $arguments['order_id']);
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
        return Tool::deleteCookie($cookie);
    }

}
