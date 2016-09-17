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
