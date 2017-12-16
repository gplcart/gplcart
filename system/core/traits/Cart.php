<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains controller methods for cart submissions
 */
trait Cart
{

    abstract public function isAjax();

    abstract public function getCartUid();

    abstract public function getStoreId();

    abstract public function getCartPreview();

    abstract public function isPosted($key = null);

    abstract public function filterSubmitted(array $allowed);

    abstract public function getSubmitted($key = null, $default = null);

    abstract public function outputJson($data, array $options = array());

    abstract public function setSubmitted($key = null, $value = null, $filter = true);

    abstract public function validateComponent($handler_id, array $options = array());

    abstract public function format($format, array $arguments = array(), $glue = '<br>');

    abstract public function error($key = null, $return_error = null, $return_no_error = '');

    abstract public function redirect($url = '', $message = '', $severity = 'info', $exclude = false);

    /**
     * Handles product cart submissions
     * @param \gplcart\core\models\CartAction $cart_action_model
     */
    public function submitCart($cart_action_model)
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('add_to_cart')) {
            $this->validateAddToCart();
            $this->addToCart($cart_action_model);
        } else if ($this->isPosted('remove_from_cart')) {
            $this->setSubmitted('cart');
            $this->deleteFromCart($cart_action_model);
        }
    }

    /**
     * Validates adding a product to cart
     */
    public function validateAddToCart()
    {
        $this->setSubmitted('user_id', $this->getCartUid());
        $this->setSubmitted('store_id', $this->getStoreId());
        $this->setSubmitted('quantity', $this->getSubmitted('quantity', 1));

        $this->validateComponent('cart');
    }

    /**
     * Adds a product to the cart
     * @param \gplcart\core\models\CartAction $cart_action_model
     */
    public function addToCart($cart_action_model)
    {
        $errors = $this->error();

        if (empty($errors)) {
            $submitted = $this->getSubmitted();
            $result = $cart_action_model->add($submitted['product'], $submitted);
        } else {

            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->format($errors)
            );
        }

        if ($this->isAjax()) {
            $result['modal'] = $this->getCartPreview();
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted cart item
     * @param \gplcart\core\models\CartAction $cart_action_model
     */
    public function deleteFromCart($cart_action_model)
    {
        $result = $cart_action_model->delete($this->getSubmitted('cart_id'));

        if (empty($result['quantity'])) {
            $result['message'] = '';
        }

        if ($this->isAjax()) {
            $result['modal'] = $this->getCartPreview();
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
