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

    /**
     * @see \gplcart\core\Controller::isAjax()
     */
    abstract public function isAjax();

    /**
     * @see \gplcart\core\Controller::getCartUid()
     */
    abstract public function getCartUid();

    /**
     * @see \gplcart\core\Controller::getStoreId()
     */
    abstract public function getStoreId();

    /**
     * @see \gplcart\core\Controller::isPosted()
     * @param null $key
     * @return
     */
    abstract public function isPosted($key = null);

    /**
     * @see \gplcart\core\Controller::filterSubmitted()
     * @param array $allowed
     * @return
     */
    abstract public function filterSubmitted(array $allowed);

    /**
     * @see \gplcart\core\Controller::getSubmitted()
     * @param null $key
     * @param null $default
     * @return
     */
    abstract public function getSubmitted($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::outputJson()
     * @param $data
     * @param array $options
     * @return
     */
    abstract public function outputJson($data, array $options = array());

    /**
     * @see \gplcart\core\Controller::setSubmitted()
     * @param null $key
     * @param null $value
     * @param bool $filter
     * @return
     */
    abstract public function setSubmitted($key = null, $value = null, $filter = true);

    /**
     * @see \gplcart\core\Controller::validateComponent()
     * @param $handler_id
     * @param array $options
     * @return
     */
    abstract public function validateComponent($handler_id, array $options = array());

    /**
     * @see \gplcart\core\Controller::format()
     * @param $format
     * @param array $arguments
     * @param string $glue
     * @return
     */
    abstract public function format($format, array $arguments = array(), $glue = '<br>');

    /**
     * @see \gplcart\core\Controller::error()
     * @param null $key
     * @param null $return_error
     * @param string $return_no_error
     * @return
     */
    abstract public function error($key = null, $return_error = null, $return_no_error = '');

    /**
     * @see \gplcart\core\Controller::redirect()
     * @param string $url
     * @param string $message
     * @param string $severity
     * @param bool $exclude
     * @return
     */
    abstract public function redirect($url = '', $message = '', $severity = 'info', $exclude = false);

    /**
     * Get cart preview
     */
    abstract public function getCartPreview();

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
