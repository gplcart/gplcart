<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Hook;
use core\Controller;
use core\models\Cart;
use core\models\Product;
use core\models\Bookmark;

class Action extends Controller
{

    /**
     * Cart model instance
     * @var \core\models\Cart $cart 
     */
    protected $cart;

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;
    
    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;
    
    /**
     * Constructor
     * @param Cart $cart
     * @param Bookmark $bookmark
     * @param Product $product
     * @param Hook $hook
     */
    public function __construct(Cart $cart, Bookmark $bookmark, Product $product, Hook $hook)
    {
        parent::__construct();

        $this->hook = $hook;
        $this->cart = $cart;
        $this->product = $product;
        $this->bookmark = $bookmark;
    }

    /**
     * Processes an action
     */
    public function action()
    {
        // Reject all requests with invalid token
        $this->controlToken();

        // Catch spam submits
        $this->controlSpam('action');

        $action = $this->request->get('action');
        
        $this->hook->fire('action.before', $action);

        // Action name = method name
        if (empty($action) || !method_exists($this, $action)) {
            $this->finishAction();
        }

        $result = $this->{$action}();
        
        $this->hook->fire('action.after', $action, $result);
        
        $this->finishAction((array) $result);
    }

    /**
     * Returns either JSON or redirects to a destination with a message
     * @param array $result
     */
    protected function finishAction(array $result = array())
    {
        $result += array(
            'redirect' => $this->request->get('redirect', '/'),
            'message' => $this->text('An error occurred'),
            'message_type' => 'danger',
        );

        if ($this->request->ajax()) {
            $this->response->json($result);
        }

        if (empty($result['message'])) {
            $this->redirect($result['redirect']);
        }

        $this->redirect($result['redirect'], $result['message'], $result['message_type']);
    }

    /**
     * Adds a product to the cart
     * @return array
     */
    protected function addToCart()
    {
        $product_id = $this->request->get('product_id');
        $quantity = $this->request->get('quantity', 1);

        $result = $this->cart->submit(array(
            'product_id' => $product_id, 'quantity' => $quantity));

        if ($result === true) {
            return array(
                'message_type' => 'success',
                'message' => $this->text('Product has been added to your cart. <a href="!href">Checkout</a>', array(
                    '!href' => $this->url('checkout'))));
        }

        if ($result === false) {
            return array(
                'redirect' => $this->url("product/$product_id", array(), true),
                'message' => $this->text('Please select product options before adding to the cart'),
                'message_type' => 'warning',
            );
        }

        return array('message' => $result);
    }

    /**
     * Adds a product to the wishlist
     * @return array
     */
    protected function addToWishlist()
    {
        $user_id = $this->cart->uid();
        $product_id = $this->request->get('product_id');
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return array();
        }

        $added = $this->bookmark->add(array(
            'user_id' => $this->cart->uid(),
            'id_key' => 'product_id',
            'id_value' => $product_id
                ), true); // true - check limit

        if ($added) {
            return array(
                'message_type' => 'success',
                'message' => $this->text('Product has been added to your <a href="!href">wishlist</a>', array(
                    '!href' => $this->url('wishlist'))));
        }

        return array(
            'message_type' => 'warning',
            'message' => $this->text('Oops, you\'re exceeding %limit items in <a href="!href">your wishlist</a>', array(
                '%limit' => $this->bookmark->getLimits($user_id),
                '!href' => $this->url('wishlist'))));
    }

    /**
     * Removes a product from wishlist
     * @return array
     */
    protected function removeFromWishlist()
    {
        $user_id = $this->cart->uid();
        $product_id = $this->request->get('product_id');

        $result = $this->bookmark->getList(array(
            'id_key' => 'product_id',
            'id_value' => $product_id,
            'user_id' => $user_id));

        if (empty($result)) {
            return array();
        }

        $this->bookmark->delete(array_keys($result));

        return array(
            'message_type' => 'success',
            'message' => $this->text('Product has been deleted from your wishlist'));
    }

    /**
     * Adds a product to comparison
     * @return array
     */
    protected function addToCompare()
    {
        $product_id = $this->request->get('product_id');
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return array();
        }

        $added = $this->product->addToCompare($product_id);

        if ($added) {
            return array(
                'message_type' => 'success',
                'message' => $this->text('Product has been added to <a href="!href">comparison</a>', array(
                    '!href' => $this->url('compare'))));
        }

        return array();
    }

    /**
     * Removes a product from comparison
     * @return array
     */
    protected function removeFromComparison()
    {
        $product_id = $this->request->get('product_id');
        $this->product->removeCompared($product_id);

        return array(
            'redirect' => $this->url('compare', array(), true),
            'message_type' => 'success',
            'message' => $this->text('Product has been removed from comparison'));
    }

}
