<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods related to product comparison
 */
trait ControllerCompare
{

    /**
     * Adds/removes a product from comparison
     * @param \gplcart\core\Controller $controller
     * @return null
     */
    protected function submitCompareTrait($controller)
    {
        /* @var $compare \gplcart\core\models\Compare */
        $compare = $controller->getInstance('compare');

        /* @var $response \gplcart\core\helpers\Response */
        $response = $controller->getInstance('response');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        // Goes before deleteCompareTrait()
        $controller->setSubmitted('product');

        if ($controller->isPosted('remove_from_compare')) {
            $this->deleteCompareTrait($controller, $compare, $request, $response);
            return null;
        }

        if ($controller->isPosted('add_to_compare')) {
            $this->validateAddToCompareTrait($controller);
            $this->addToCompareTrait($controller, $compare, $request, $response);
        }
    }

    /**
     * Adds a product to comparison
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Compare $compare
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     */
    protected function addToCompareTrait($controller, $compare, $request,
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
            $result = $compare->addProduct($submitted['product'], $submitted);
        } else {
            $result['message'] = implode('<br>', gplcart_array_flatten($errors));
        }

        if ($request->isAjax()) {
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Deletes a submitted product from the comparison
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Compare $compare
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     */
    protected function deleteCompareTrait($controller, $compare, $request,
            $response)
    {
        $result = $compare->deleteProduct($controller->getSubmitted('product_id'));

        if ($request->isAjax()) {
            $response->json($result);
        }

        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Validates "Add to compare" action
     * @param \gplcart\core\Controller $controller
     */
    protected function validateAddToCompareTrait($controller)
    {
        $controller->validate('compare');
    }

    /**
     * Set "in comparison" boolean flag
     * @param \gplcart\core\Controller $controller
     * @param array $product
     */
    protected function setInComparisonTrait($controller, array &$product)
    {
        /* @var $compare \gplcart\core\models\Compare */
        $compare = $controller->getInstance('compare');

        $product['in_comparison'] = $compare->exists($product['product_id']);
    }

}
