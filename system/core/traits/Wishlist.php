<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains controller methods for user wishlists
 */
trait Wishlist
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
     */
    abstract public function isPosted($key = null);

    /**
     * @see \gplcart\core\Controller::filterSubmitted()
     */
    abstract public function filterSubmitted(array $allowed);

    /**
     * @see \gplcart\core\Controller::getSubmitted()
     */
    abstract public function getSubmitted($key = null, $default = null);

    /**
     * @see \gplcart\core\Controller::outputJson()
     */
    abstract public function outputJson($data, array $options = array());

    /**
     * @see \gplcart\core\Controller::setSubmitted()
     */
    abstract public function setSubmitted($key = null, $value = null, $filter = true);

    /**
     * @see \gplcart\core\Controller::validateComponent()
     */
    abstract public function validateComponent($handler_id, array $options = array());

    /**
     * @see \gplcart\core\Controller::format()
     */
    abstract public function format($format, array $arguments = array(), $glue = '<br>');

    /**
     * @see \gplcart\core\Controller::error()
     */
    abstract public function error($key = null, $return_error = null, $return_no_error = '');

    /**
     * @see \gplcart\core\Controller::redirect()
     */
    abstract public function redirect($url = '', $message = '', $severity = 'info', $exclude = false);

    /**
     * Adds/removes a product from the wishlist
     * @param \gplcart\core\models\WishlistAction $wishlist_action_model
     */
    protected function submitWishlist($wishlist_action_model)
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('remove_from_wishlist')) {
            $this->deleteFromWishlist($wishlist_action_model);
        } else if ($this->isPosted('add_to_wishlist')) {
            $this->validateAddToWishlist();
            $this->addToWishlist($wishlist_action_model);
        }
    }

    /**
     * Validates adding a submitted product to the wishlist
     */
    protected function validateAddToWishlist()
    {
        $this->setSubmitted('user_id', $this->getCartUid());
        $this->setSubmitted('store_id', $this->getStoreId());
        $this->validateComponent('wishlist');
    }

    /**
     * Add a product to the wishlist
     * @param \gplcart\core\models\WishlistAction $wishlist_action_model
     */
    public function addToWishlist($wishlist_action_model)
    {
        $errors = $this->error();

        if (empty($errors)) {
            $result = $wishlist_action_model->add($this->getSubmitted());
        } else {
            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $this->format($errors)
            );
        }

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the wishlist
     * @param \gplcart\core\models\WishlistAction $wishlist_action_model
     */
    public function deleteFromWishlist($wishlist_action_model)
    {
        $product = array(
            'user_id' => $this->getCartUid(),
            'store_id' => $this->getStoreId(),
            'product_id' => $this->getSubmitted('product_id')
        );

        $result = $wishlist_action_model->delete($product);

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
