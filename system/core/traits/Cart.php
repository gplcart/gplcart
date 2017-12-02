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
     * @return \gplcart\core\Controller
     */
    protected abstract function getController();

    /**
     * Returns rendered cart preview
     */
    public abstract function getCartPreview();

    /**
     * Handles product cart submissions
     * @param \gplcart\core\models\Cart $cart_model
     */
    public function submitCart($cart_model)
    {
        $controller = $this->getController();
        $controller->setSubmitted('product');
        $controller->filterSubmitted(array('product_id'));

        if ($controller->isPosted('add_to_cart')) {
            $this->validateAddToCart();
            $this->addToCart($cart_model);
        } else if ($controller->isPosted('remove_from_cart')) {
            $controller->setSubmitted('cart');
            $this->deleteFromCart($cart_model);
        }
    }

    /**
     * Validates adding a product to cart
     */
    public function validateAddToCart()
    {
        $controller = $this->getController();

        $controller->setSubmitted('user_id', $controller->getCartUid());
        $controller->setSubmitted('store_id', $controller->getStoreId());
        $controller->setSubmitted('quantity', $controller->getSubmitted('quantity', 1));

        $controller->validateComponent('cart');
    }

    /**
     * Adds a product to the cart
     * @param \gplcart\core\models\Cart $cart_model
     */
    public function addToCart($cart_model)
    {
        $controller = $this->getController();
        $errors = $controller->error();

        if (empty($errors)) {
            $submitted = $controller->getSubmitted();
            $result = $cart_model->addProduct($submitted['product'], $submitted);
        } else {

            $result = array(
                'redirect' => '',
                'severity' => 'warning',
                'message' => $controller->format($errors)
            );
        }

        if ($controller->isAjax()) {
            $result['modal'] = $this->getCartPreview();
            $controller->outputJson($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted cart item
     * @param \gplcart\core\models\Cart $cart_model
     */
    public function deleteFromCart($cart_model)
    {
        $controller = $this->getController();
        $result = $cart_model->submitDelete($controller->getSubmitted('cart_id'));

        if (empty($result['quantity'])) {
            $result['message'] = '';
        }

        if ($controller->isAjax()) {
            $result['modal'] = $this->getCartPreview();
            $controller->outputJson($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
