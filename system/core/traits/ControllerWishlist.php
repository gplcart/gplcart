<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods related to wishlist functionality
 */
trait ControllerWishlist
{

    /**
     * Adds/removes a product from the wishlist
     * @param \gplcart\core\Controller $controller
     * @return null
     */
    protected function submitWishlistTrait($controller)
    {
        /* @var $wishlist \gplcart\core\models\Wishlist */
        $wishlist = $controller->getInstance('wishlist');

        /* @var $response \gplcart\core\helpers\Response */
        $response = $controller->getInstance('response');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        // Goes before deleteWishlistTrait()
        $controller->setSubmitted('product');

        if ($controller->isPosted('remove_from_wishlist')) {
            $this->deleteWishlistTrait($controller, $wishlist, $request, $response);
            return null;
        }

        if ($controller->isPosted('add_to_wishlist')) {
            $this->validateAddToWishlistTrait($controller);
            $this->addWishlistTrait($controller, $wishlist, $request, $response);
        }
    }

    /**
     * Validates "Add to wishlist" action
     * @param \gplcart\core\Controller $controller
     */
    protected function validateAddToWishlistTrait($controller)
    {
        $controller->setSubmitted('user_id', $controller->cart('user_id'));
        $controller->setSubmitted('store_id', $controller->store('store_id'));
        $controller->validate('wishlist');
    }

    /**
     * Add a product to the wishlist
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Wishlist $wishlist
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     */
    protected function addWishlistTrait($controller, $wishlist, $request,
            $response)
    {

        $errors = $controller->error();

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $controller->text('An error occurred')
        );

        if (empty($errors)) {
            $submitted = $controller->getSubmitted();
            $result = $wishlist->addProduct($submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($request->isAjax()) {
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the wishlist
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Wishlist $wishlist
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     */
    protected function deleteWishlistTrait($controller, $wishlist, $request,
            $response)
    {
        $condititons = array(
            'user_id' => $controller->cart('user_id'),
            'store_id' => $controller->store('store_id'),
            'product_id' => $controller->getSubmitted('product_id')
        );

        $result = $wishlist->deleteProduct($condititons);

        if ($request->isAjax()) {
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Set "In wishlist" boolean flag
     * @param \gplcart\core\Controller $controller
     * @param array $product
     */
    protected function setInWishlistTrait($controller, &$product)
    {
        /* @var $wishlist \gplcart\core\models\Wishlist */
        $wishlist = $controller->getInstance('wishlist');

        $conditions = array(
            'product_id' => $product['product_id'],
            'user_id' => $controller->cart('user_id'),
            'store_id' => $controller->store('store_id')
        );

        $product['in_wishlist'] = $wishlist->exists($conditions);
    }

}
