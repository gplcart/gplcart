<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\Hook;
use gplcart\core\models\Cart as CartModel;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\WishlistAction as WishlistActionModel;

/**
 * Manages basic behaviors and data related to shopping cart actions
 */
class CartAction
{

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
     * Wishlist action model instance
     * @var \gplcart\core\models\WishlistAction $wishlist_action
     */
    protected $wishlist_action;

    /**
     * Translation model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Cart model instance
     * @var \gplcart\core\models\Cart $cart
     */
    protected $cart;

    /**
     * URL model instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param CartModel $cart
     * @param WishlistActionModel $wishlist_action
     * @param TranslationModel $translation
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, Config $config, CartModel $cart,
                                WishlistActionModel $wishlist_action, TranslationModel $translation, UrlHelper $url)
    {
        $this->hook = $hook;
        $this->config = $config;

        $this->url = $url;
        $this->cart = $cart;
        $this->translation = $translation;
        $this->wishlist_action = $wishlist_action;
    }

    /**
     * Adds the product to the cart
     * @param array $product
     * @param array $data
     * @return array
     */
    public function add(array $product, array $data)
    {
        $result = array();
        $this->hook->attach('cart.add.product.before', $product, $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $data += array(
            'quantity' => 1,
            'user_id' => $this->cart->getUid(),
            'store_id' => $product['store_id'],
            'product_id' => $product['product_id']
        );

        $data['cart_id'] = $this->set($data);

        if (empty($data['cart_id'])) {
            return $this->getResultError();
        }

        $result = $this->getResultAdded($data);
        $this->hook->attach('cart.add.product.after', $product, $data, $result, $this);
        return (array) $result;
    }

    /**
     * Moves a cart item to a wishlist
     * @param int $cart_id
     * @return array
     */
    public function toWishlist($cart_id)
    {
        $result = null;
        $this->hook->attach('cart.move.wishlist.before', $cart_id, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $cart = $this->cart->get($cart_id);

        if (empty($cart) || !$this->cart->delete($cart_id)) {
            return $this->getResultError();
        }

        $result = $this->addToWishlist($cart);

        gplcart_static_clear();

        $this->hook->attach('cart.move.wishlist.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Deletes a cart item
     * @param int $cart_id
     * @return array
     */
    public function delete($cart_id)
    {
        $result = array();
        $this->hook->attach('cart.delete.item.before', $cart_id, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $cart = $this->cart->get($cart_id);

        if (empty($cart) || !$this->cart->delete($cart_id)) {
            return $this->getResultError();
        }

        $result = $this->getResultDelete($cart);
        $this->hook->attach('cart.delete.item.after', $cart_id, $result, $this);
        return $result;
    }

    /**
     * Performs all needed tasks when customer is logging in during checkout
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
            $this->cart->delete(array('user_id' => $user['user_id']));
        }

        if (!empty($cart['items'])) {
            foreach ($cart['items'] as $item) {
                $this->cart->update($item['cart_id'], array('user_id' => $user['user_id']));
            }
        }

        $this->cart->deleteCookie();
        $result = $this->getResultLogin($user);

        $this->hook->attach('cart.login.after', $user, $cart, $result, $this);
        return (array) $result;
    }

    /**
     * Adds/updates a cart item
     * @param array $data
     * @param bool $increment
     * @return integer
     */
    protected function set(array $data, $increment = true)
    {
        $options = array(
            'order_id' => 0,
            'sku' => $data['sku'],
            'user_id' => $data['user_id'],
            'store_id' => $data['store_id']
        );

        $list = $this->cart->getList($options);

        if (empty($list)) {
            return $this->cart->add($data);
        }

        $cart = reset($list);

        if ($increment) {
            $data['quantity'] += $cart['quantity'];
        }

        $this->cart->update($cart['cart_id'], array('quantity' => $data['quantity']));
        return $cart['cart_id'];
    }

    /**
     * Adds a product to the wishlist
     * @param array $cart
     * @return array
     */
    protected function addToWishlist(array $cart)
    {
        $data = array(
            'user_id' => $cart['user_id'],
            'store_id' => $cart['store_id'],
            'product_id' => $cart['product_id']
        );

        return $this->wishlist_action->add($data);
    }

    /**
     * Returns the error result
     * @return array
     */
    protected function getResultError()
    {
        return array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->translation->text('An error occured')
        );
    }

    /**
     * Returns an array of resulting data after a user has been logged in
     * @param array $user
     * @return array
     */
    protected function getResultLogin(array $user)
    {
        return array(
            'user' => $user,
            'redirect' => 'checkout',
            'severity' => 'success',
            'message' => $this->translation->text('Hello, %name. Now you are logged in', array('%name' => $user['name']))
        );
    }

    /**
     * Returns an array of resulting data after a product has been deleted from a cart
     * @param array $cart
     * @return array
     */
    protected function getResultDelete(array $cart)
    {
        $options = array(
            'user_id' => $cart['user_id'],
            'store_id' => $cart['store_id'],
        );

        $content = $this->cart->getContent($options);

        return array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => empty($content['quantity']) ? 0 : $content['quantity'],
            'message' => $this->translation->text('Product has been deleted from cart')
        );
    }

    /**
     * Returns an array of resulting data after a product has been added to a cart
     * @param array $data
     * @return array
     */
    protected function getResultAdded(array $data)
    {
        $options = array(
            'user_id' => $data['user_id'],
            'store_id' => $data['store_id']
        );

        $vars = array('@url' => $this->url->get('checkout'));
        $message = $this->translation->text('Product has been added to your cart. <a href="@url">Checkout</a>', $vars);

        return array(
            'redirect' => '',
            'message' => $message,
            'severity' => 'success',
            'cart_id' => $data['cart_id'],
            'quantity' => $this->cart->getQuantity($options, 'total')
        );
    }

}
