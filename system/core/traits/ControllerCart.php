<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods related to cart functionality
 */
trait ControllerCart
{

    /**
     * Handles "Add to cart" event
     * @param \gplcart\core\Controller $controller
     * @return null
     */
    protected function submitCartTrait($controller)
    {
        /* @var $cart \gplcart\core\models\Cart */
        $cart = $controller->getInstance('cart');

        /* @var $response \gplcart\core\helpers\Response */
        $response = $controller->getInstance('response');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        if ($controller->isPosted('add_to_cart')) {
            $this->validateAddToCartTrait($controller);
            $this->addToCartTrait($controller, $cart, $request, $response);
            return null;
        }

        if ($controller->isPosted('remove_from_cart')) {
            $controller->setSubmitted('cart');
            $this->deleteCartTrait($controller, $cart, $request, $response);
        }
    }

    /**
     * Performs "Add to cart" action
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Cart $cart
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     * @return null
     */
    protected function addToCartTrait($controller, $cart, $request, $response)
    {
        $errors = $controller->error();

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $controller->text('An error occurred')
        );

        if (empty($errors)) {
            $submitted = $controller->getSubmitted();
            $result = $cart->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($request->isAjax()) {
            if ($result['severity'] === 'success') {
                $result += array('modal' => $controller->renderCartPreview());
            }
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to cart" action
     * @param \gplcart\core\Controller $controller
     */
    protected function validateAddToCartTrait($controller)
    {
        $controller->setSubmitted('product');

        $controller->setSubmitted('user_id', $controller->cart('user_id'));
        $controller->setSubmitted('store_id', $controller->store('store_id'));
        $controller->setSubmitted('quantity', $controller->getSubmitted('quantity', 1));

        $controller->validate('cart');
    }

    /**
     * Deletes a cart item
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Cart $cart
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     * @todo More generic solution. Move to Cart model?
     */
    protected function deleteCartTrait($controller, $cart, $request, $response)
    {
        $conditions = array(
            'cart_id' => $controller->getSubmitted('cart_id'));

        if (!$cart->delete($conditions)) {
            return array('redirect' => '', 'severity' => 'success');
        }

        $result = array(
            'redirect' => '',
            'severity' => 'success',
            'quantity' => $controller->cart('count_total'),
            'message' => $controller->text('Cart item has been deleted')
        );

        $preview = $controller->renderCartPreview();

        if (empty($preview)) {
            $result['message'] = '';
            $result['quantity'] = 0;
        }

        if ($request->isAjax()) {
            $result['modal'] = $preview;
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
