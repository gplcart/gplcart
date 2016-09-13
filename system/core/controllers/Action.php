<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user submitted actions
 */
class Action extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Processes an action
     */
    public function getResponseAction()
    {
        // Reject all requests with invalid token
        $this->controlToken();

        // Catch spam submits
        $this->controlSpam('action');

        $action = (string) $this->request->get('action', '');

        $this->hook->fire('action.before', $action);

        // Action name = method name
        if (empty($action) || !method_exists($this, $action)) {
            $this->finishAction();
        }

        try {
            $result = $this->{$action}();
        } catch (\BadMethodCallException $ex) {
            $result = array('message' => $this->text('An error occurred'), 'severity' => 'danger');
        }

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
            'severity' => 'danger',
            'message' => $this->text('An error occurred'),
            'redirect' => (string) $this->request->get('redirect', '/')
        );

        if ($this->request->isAjax()) {
            $this->response->json($result);
        }

        if (empty($result['message'])) {
            $this->redirect($result['redirect']);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Adds a product to the cart
     * @return array
     */
    protected function addToCartAction()
    {
        $product_id = (int) $this->request->get('product_id');
        $quantity = (int) $this->request->get('quantity', 1);

        $data = array(
            'quantity' => $quantity,
            'product_id' => $product_id
        );

        $result = $this->cart->submit($data);

        $response = array('message' => $result);

        if ($result === true) {

            $response = array(
                'severity' => 'success',
                'message' => $this->text('Product has been added to your cart. <a href="!href">Checkout</a>', array(
                    '!href' => $this->url('checkout')))
            );
        }

        if ($result === false) {

            $response = array(
                'severity' => 'warning',
                'redirect' => $this->url("product/$product_id", array(), true),
                'message' => $this->text('Please select product options before adding to the cart')
            );
        }

        return $response;
    }

    /**
     * Adds a product to the wishlist
     * @return array
     */
    protected function addToWishlistAction()
    {
        $user_id = $this->cart->uid();
        $product_id = (int) $this->request->get('product_id');
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return array();
        }

        $data = array(
            'product_id' => $product_id,
            'user_id' => $this->cart->uid()
        );

        $added = $this->wishlist->add($data, true); // true - check limit

        $response = array(
            'severity' => 'success',
            'message' => $this->text('Product has been added to your <a href="!href">wishlist</a>', array(
                '!href' => $this->url('wishlist')))
        );

        if (empty($added)) {

            $response = array(
                'severity' => 'warning',
                'message' => $this->text('Oops, you\'re exceeding %limit items in <a href="!href">your wishlist</a>', array(
                    '%limit' => $this->wishlist->getLimits($user_id),
                    '!href' => $this->url('wishlist')))
            );
        }

        return $response;
    }

    /**
     * Removes a product from wishlist
     * @return array
     */
    protected function removeFromWishlistAction()
    {
        $user_id = $this->cart->uid();
        $product_id = (int) $this->request->get('product_id');

        $data = array(
            'user_id' => $user_id,
            'product_id' => $product_id
        );

        $result = $this->wishlist->getList($data);

        if (empty($result)) {
            return array();
        }

        foreach (array_keys($result) as $wishlist_id) {
            $this->wishlist->delete($wishlist_id);
        }

        $response = array(
            'severity' => 'success',
            'message' => $this->text('Product has been deleted from your wishlist')
        );

        return $response;
    }

    /**
     * Adds a product to comparison
     * @return array
     */
    protected function addToCompareAction()
    {
        $product_id = (int) $this->request->get('product_id');
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return array();
        }

        $added = $this->product->addToCompare($product_id);

        $response = array();

        if (!empty($added)) {
            $response = array(
                'severity' => 'success',
                'message' => $this->text('Product has been added to <a href="!href">comparison</a>', array(
                    '!href' => $this->url('compare')))
            );
        }

        return $response;
    }

    /**
     * Removes a product from comparison
     * @return array
     */
    protected function removeFromComparisonAction()
    {
        $product_id = (int) $this->request->get('product_id');
        $this->product->removeCompared($product_id);

        $response = array(
            'severity' => 'success',
            'redirect' => $this->url('compare', array(), true),
            'message' => $this->text('Product has been removed from comparison')
        );

        return $response;
    }

}
