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
     * @return \gplcart\core\Controller
     */
    protected abstract function getController();

    /**
     * Adds/removes a product from the wishlist
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    protected function submitWishlist($wishlist_model)
    {
        $controller = $this->getController();
        $controller->setSubmitted('product');
        $controller->filterSubmitted(array('product_id'));

        if ($controller->isPosted('remove_from_wishlist')) {
            $this->deleteFromWishlist($wishlist_model);
        } else if ($controller->isPosted('add_to_wishlist')) {
            $this->validateAddToWishlist();
            $this->addToWishlist($wishlist_model);
        }
    }

    /**
     * Validates adding a submitted product to the wishlist
     */
    protected function validateAddToWishlist()
    {
        $controller = $this->getController();
        $controller->setSubmitted('user_id', $controller->getCartUid());
        $controller->setSubmitted('store_id', $controller->getStoreId());
        $controller->validateComponent('wishlist');
    }

    /**
     * Add a product to the wishlist
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function addToWishlist($wishlist_model)
    {
        $controller = $this->getController();
        $errors = $controller->error();

        if (empty($errors)) {
            $result = $wishlist_model->addProduct($controller->getSubmitted());
        } else {
            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $controller->format($errors)
            );
        }

        if ($controller->isAjax()) {
            $controller->outputJson($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the wishlist
     * @param \gplcart\core\models\Wishlist $wishlist_model
     */
    public function deleteFromWishlist($wishlist_model)
    {
        $controller = $this->getController();

        $product = array(
            'user_id' => $controller->getCartUid(),
            'store_id' => $controller->getStoreId(),
            'product_id' => $controller->getSubmitted('product_id')
        );

        $result = $wishlist_model->deleteProduct($product);

        if ($controller->isAjax()) {
            $controller->outputJson($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
