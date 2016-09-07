<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Logger;
use core\Model;
use core\classes\Cache;
use core\classes\Request;
use core\classes\Tool;
use core\models\Currency as ModelsCurrency;
use core\models\Image as ModelsImage;
use core\models\Language as ModelsLanguage;
use core\models\Price as ModelsPrice;
use core\models\Product as ModelsProduct;
use core\models\Store as ModelsStore;
use core\models\User as ModelsUser;
use core\models\Wishlist as ModelsWishlist;


/**
 * Manages basic behaviors and data related to user carts
 */
class Cart extends Model
{
    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

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
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

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
     * Array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsPrice $price
     * @param ModelsCurrency $currency
     * @param ModelsUser $user
     * @param ModelsWishlist $wishlist
     * @param ModelsLanguage $language
     * @param ModelsStore $store
     * @param ModelsImage $image
     * @param Request $request
     * @param Logger $logger
     */
    public function __construct(ModelsProduct $product, ModelsPrice $price,
            ModelsCurrency $currency, ModelsUser $user,
            ModelsWishlist $wishlist, ModelsLanguage $language,
            ModelsStore $store, ModelsImage $image, Request $request,
            Logger $logger)
    {
        parent::__construct();

        $this->user = $user;
        $this->store = $store;
        $this->price = $price;
        $this->image = $image;
        $this->logger = $logger;
        $this->product = $product;
        $this->request = $request;
        $this->currency = $currency;
        $this->wishlist = $wishlist;
        $this->language = $language;
    }

    /**
     * Returns a cart content for a given user ID
     * @param mixed $user_id
     * @param boolean $cached
     * @return array
     */
    public function getByUser($user_id = null, $cached = true)
    {
        if (!isset($user_id)) {
            $user_id = $this->uid();
        }

        //if ($cached) {
        $cart = &Cache::memory("cart.$user_id");

        if (isset($cart)) {
            return $cart;
        }

        /*
          $cache = Cache::get("cart.$user_id");

          if (isset($cache)) {
          $cart = $cache;
          return $cart;
          }
         * */

        //}

        $products = $this->getList(array('user_id' => $user_id));

        if (empty($products)) {
            return array();
        }

        $total = 0;
        $quantity = 0;
        $current_currency = $this->currency->get();

        $cart = array();
        foreach ($products as $cart_id => $item) {
            $item['product'] = $this->product->getBySku($item['sku'], $item['store_id']);

            // Invalid / disabled product
            if (empty($item['product']['status'])) {
                continue;
            }

            // Product store changed
            if ((int) $this->store->id() !== (int) $item['product']['store_id']) {
                continue;
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

        if ($cached) {
            //Cache::set("cart.$user_id", $cart);
        }

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

        $user_id = Tool::randomString(6);
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
        $data += array('order_id' => 0);

        $sql = '
            SELECT *, SUM(quantity) AS quantity
            FROM cart
            WHERE cart_id > 0';

        $where = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND user_id=?';
            $where[] = $data['user_id'];
        }

        if (isset($data['order_id'])) {
            $sql .= ' AND order_id=?';
            $where[] = $data['order_id'];
        }

        $sql .= ' GROUP BY sku ORDER BY created DESC';

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        $results = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $item['data'] = unserialize($item['data']);
            $results[$item['cart_id']] = $item;
        }

        return $results;
    }

    /**
     * Adds a product to the cart
     * @param array $data
     * @return boolean|string
     * Returns true on success,
     * false - needs more data (redirect to product page),
     * string - last validation error
     */
    public function submit(array $data)
    {
        $product = $this->product->get($data['product_id']);

        if (empty($product['status'])) {
            return $this->language->text('An error occurred');
        }

        if (!empty($product['combination'])) {
            return false;
        }

        if (empty($data['quantity'])) {
            $data['quantity'] = 1;
        }

        $result = $this->addProduct($data);

        if ($result === true) {
            return true;
        }

        $error = is_array($result) ? end($result) : $result;
        return $error;
    }

    /**
     * Adds a product to the cart
     * @param array $data
     * @return mixed
     */
    public function addProduct(array $data)
    {
        $this->hook->fire('add.cart.product.before', $data);

        if (empty($data['quantity'])) {
            return false;
        }

        $user_id = $this->uid();
        $product = $this->product->get($data['product_id']);
        $this->validate($data, $product, $user_id);
        $data = array('user_id' => $user_id, 'store_id' => $product['store_id']) + $data;

        $this->hook->fire('presave.cart.product', $data, $product, $this->errors);

        if (!empty($this->errors)) {
            return $this->errors;
        }

        $cart_id = $this->setProduct($data, $user_id);
        $this->hook->fire('add.cart.product.after', $data, $cart_id);

        $this->logAddToCart($data, $product, $user_id);
        $this->deleteCache($user_id);
        return true;
    }

    /**
     * Validates a product before adding to the cart
     * @param array $data
     * @param array $product
     * @param string|integer $user_id
     * @return boolean
     */
    protected function validate(array &$data, array $product, $user_id)
    {
        if (!$this->validateProduct($product)) {
            return false;
        }

        if (empty($data['options'])) {
            $data['sku'] = $product['sku'];
            $data['stock'] = $product['stock'];
        } else {
            $data['combination_id'] = $this->product->getCombinationId($data['options'], $product['product_id']);

            if (!empty($product['combination'][$data['combination_id']]['sku'])) {
                $data['sku'] = $product['combination'][$data['combination_id']]['sku'];
                $data['stock'] = $product['combination'][$data['combination_id']]['stock'];
            }
        }

        if (!$this->validateSku($data)) {
            return false;
        }

        if (!$this->validateLimits($data, $product, $user_id)) {
            return false;
        }

        return true;
    }

    /**
     * Validates a product before adding to the cart
     * @param array $product
     * @return boolean
     */
    protected function validateProduct(array $product)
    {
        if (!empty($product['status'])) {
            return true;
        }

        $this->errors[] = $this->language->text('Invalid product');
        return false;
    }

    /**
     * Validates a product SKU before addingto the cart
     * @param array $data
     * @return boolean
     */
    protected function validateSku(array $data)
    {
        if (empty($data['sku'])) {
            $this->errors[] = $this->language->text('SKU not found');
            return false;
        }

        return true;
    }

    /**
     * Validates cart limits for the current cart
     * @param array $data
     * @param array $product
     * @param string|integer $user_id
     * @return boolean
     */
    protected function validateLimits(array $data, array $product, $user_id)
    {
        $total = (int) $data['quantity'];
        $skus = array($data['sku'] => true);

        foreach ($this->getList(array('user_id' => $user_id)) as $item) {
            $skus[$item['sku']] = true;
            $total += (int) $item['quantity'];
        }

        $limit_sku = (int) $this->config->get('cart_sku_limit', 10);
        $limit_total = (int) $this->config->get('cart_total_limit', 20);

        if (!empty($limit_sku) && (count($skus) > $limit_sku)) {
            $this->errors[] = $this->language->text('Sorry, you cannot have more than %num items per SKU in your cart', array(
                '%num' => $limit_sku));
        }

        if (!empty($limit_total) && ($total > $limit_total)) {
            $this->errors[] = $this->language->text('Sorry, you cannot have more than %num items in your cart', array(
                '%num' => $limit_total));
        }

        if ($product['subtract'] && ((int) $data['quantity'] > (int) $data['stock'])) {
            $this->errors[] = $this->language->text('Too low stock level');
        }

        return empty($this->errors);
    }

    /**
     * Adds/updates products in the cart
     * @param array $data
     * @param string|integer $user_id
     * @return integer
     */
    protected function setProduct(array $data, $user_id)
    {
        $sql = 'SELECT cart_id, quantity  FROM cart WHERE sku=:sku AND user_id=:user_id AND order_id=:order_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':sku' => $data['sku'], ':user_id' => $user_id, 'order_id' => 0));
        $existing = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($existing['cart_id'])) {
            $cart_id = $existing['cart_id'];
            $this->update($cart_id, array('quantity' => $existing['quantity'] ++));
            return $cart_id;
        }

        return $this->add($data);
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

        $values = $this->filterDbValues('cart', $data);

        if (empty($values)) {
            return false;
        }

        $result = $this->db->update('cart', $values, array('cart_id' => $cart_id));

        // Clear cached data
        $cart = $this->get($cart_id);
        $this->deleteCache($cart['user_id']);

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
        $sql = 'SELECT * FROM cart WHERE cart_id=:cart_id';
        $where = array(':cart_id' => (int) $cart_id);

        $sth = $this->db->prepare($sql);
        $sth->execute($where);
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Clears up cached cart content for a given user
     * @param string|integer $user_id
     */
    public function deleteCache($user_id)
    {
        Cache::clear("cart.$user_id");
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

        $cart_id = $this->db->insert('cart', array(
            'modified' => 0,
            'sku' => $data['sku'],
            'user_id' => $data['user_id'],
            'quantity' => (int) $data['quantity'],
            'store_id' => isset($data['store_id']) ? (int) $data['store_id'] : $this->config->get('store', 1),
            'product_id' => (int) $data['product_id'],
            'order_id' => isset($data['order_id']) ? (int) $data['order_id'] : 0,
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME,
            'data' => isset($data['data']) ? serialize((array) $data['data']) : serialize(array())
        ));

        $this->hook->fire('add.cart.after', $data, $cart_id);
        return $cart_id;
    }

    /**
     * Logs adding products to the cart
     * @param array $data
     * @param array $product
     * @param integer|string $user_id
     */
    protected function logAddToCart(array $data, array $product, $user_id)
    {
        $log = array(
            'message' => 'User %uid has added product %product (SKU: %sku) at %store',
            'variables' => array(
                '%uid' => is_numeric($user_id) ? $user_id : '',
                '%product' => $product['product_id'],
                '%sku' => $data['sku'],
                '%store' => $product['store_id']
            )
        );

        $this->logger->log('cart', $log);
    }

    /**
     * Moves a cart item to the wishlist
     * @param string $sku
     * @param integer|null $user_id
     * @return mixed
     */
    public function moveToWishlist($sku, $user_id = null)
    {
        $this->hook->fire('move.cart.wishlist.before', $sku, $user_id);

        if (empty($sku)) {
            return false;
        }

        if (!isset($user_id)) {
            $user_id = $this->uid();
        }

        $sth = $this->db->prepare('SELECT product_sku_id FROM product_sku WHERE sku=:sku');
        $sth->execute(array(':sku' => $sku));

        $product_sku_id = $sth->fetchColumn();

        if (empty($product_sku_id)) {
            return false;
        }

        $this->db->delete('wishlist', array(
            'product_id' => (int) $product_sku_id
        ));

        $wishlist_id = $this->wishlist->add(array(
            'product_id' => (int) $product_sku_id,
            'user_id' => $user_id
        ));

        $this->db->delete('cart', array('sku' => $sku, 'user_id' => $user_id));
        $this->deleteCache($user_id);
        $this->hook->fire('move.cart.wishlist.after', $sku, $user_id, $wishlist_id);

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

        $log = array(
            'message' => 'User has logged in during checkout using %email',
            'variables' => array('%email' => $user['email'])
        );

        $this->logger->log('checkout', $log);

        if (!$this->config->get('cart_login_merge', 0)) {
            $this->delete(false, $user['user_id']);
        }

        foreach ($cart['items'] as $item) {
            $this->update($item['cart_id'], array('user_id' => $user['user_id']));
        }

        $this->deleteCookie();

        $this->hook->fire('cart.login.after', $user, $cart);
        return true;
    }

    /**
     * Deletes a cart record from the database
     * @param integer $cart_id
     * @param mixed $user_id
     * @param integer $order_id
     * @return boolean
     */
    public function delete($cart_id, $user_id = null, $order_id = 0)
    {
        $arguments = func_get_args();

        $this->hook->fire('delete.cart.before', $arguments);

        if (empty($arguments)) {
            return false;
        }

        if (!empty($user_id)) {
            $this->deleteCache($user_id);
            // Cart orders with order_id = 0 are not linked to orders, i.e before checkout
            $where = array('user_id' => $user_id, 'order_id' => (int) $order_id);
        }

        $cart = $this->get($cart_id);

        if (!empty($cart)) {
            $this->deleteCache($cart['user_id']);
            $where = array('cart_id' => (int) $cart_id);
        }

        if (empty($where)) {
            return false;
        }

        $result = $this->db->delete('cart', $where);
        $this->hook->fire('delete.cart.after', $arguments, $result);
        return (bool) $result;
    }

    /**
     * Deletes a cart from the cookie
     * @return boolean
     */
    public function deleteCookie()
    {
        $cookie_name = $this->config->get('user_cookie_name', 'user_id');
        return Tool::deleteCookie($cookie_name);
    }

    /**
     * Prepares an array of cart items
     * @param array $cart
     * @param array $settings
     * @return array
     */
    public function prepareCartItems(array $cart, array $settings)
    {
        $imagestyle = isset($settings['image_style_cart']) ? (int) $settings['image_style_cart'] : 3;

        foreach ($cart['items'] as &$item) {

            $imagepath = '';

            if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
                $imagefile = reset($item['product']['images']);
                $imagepath = $imagefile['path'];
            }

            if (!empty($item['product']['option_file_id']) && !empty($item['product']['images'][$item['product']['option_file_id']]['path'])) {
                $imagepath = $item['product']['images'][$item['product']['option_file_id']]['path'];
            }

            $item['total_formatted'] = $this->price->format($item['total'], $cart['currency']);
            $item['price_formatted'] = $this->price->format($item['price'], $cart['currency']);

            if (empty($imagepath)) {
                $item['thumb'] = $this->image->placeholder($imagestyle);
            } else {
                $item['thumb'] = $this->image->url($imagestyle, $imagepath);
            }
        }

        $cart['total_formatted'] = $this->price->format($cart['total'], $cart['currency']);
        return $cart;
    }

}
