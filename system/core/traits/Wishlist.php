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

    abstract public function isAjax();

    abstract public function getCartUid();

    abstract public function getStoreId();

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
     * Adds/removes a product from the wishlist
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    protected function submitWishlist($wishlist_model)
    {
        $this->setSubmitted('product');
        $this->filterSubmitted(array('product_id'));

        if ($this->isPosted('remove_from_wishlist')) {
            $this->deleteFromWishlist($wishlist_model);
        } else if ($this->isPosted('add_to_wishlist')) {
            $this->validateAddToWishlist();
            $this->addToWishlist($wishlist_model);
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
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function addToWishlist($wishlist_model)
    {
        $errors = $this->error();

        if (empty($errors)) {
            $result = $wishlist_model->addProduct($this->getSubmitted());
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
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function deleteFromWishlist($wishlist_model)
    {
        $product = array(
            'user_id' => $this->getCartUid(),
            'store_id' => $this->getStoreId(),
            'product_id' => $this->getSubmitted('product_id')
        );

        $result = $wishlist_model->deleteProduct($product);

        if ($this->isAjax()) {
            $this->outputJson($result);
        }

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
