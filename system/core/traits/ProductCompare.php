<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains controller methods for product comparison
 */
trait ProductCompare
{

    /**
     * @return \gplcart\core\Controller
     */
    protected abstract function getController();

    /**
     * Handles adding/removing a submitted product from comparison
     * @param \gplcart\core\models\ProductCompare $compare_model
     */
    public function submitProductCompare($compare_model)
    {
        $controller = $this->getController();
        $controller->setSubmitted('product');
        $controller->filterSubmitted(array('product_id'));

        if ($controller->isPosted('remove_from_compare')) {
            $this->deleteFromProductCompare($compare_model);
        } else if ($controller->isPosted('add_to_compare')) {
            $this->validateAddProductCompare();
            $this->addToProductCompare($compare_model);
        }
    }

    /**
     * Validate adding a product to comparison
     */
    public function validateAddProductCompare()
    {
        $this->getController()->validateComponent('compare');
    }

    /**
     * Adds a submitted product to comparison
     * @param \gplcart\core\models\ProductCompare $compare_model
     */
    public function addToProductCompare($compare_model)
    {
        $controller = $this->getController();
        $errors = $controller->error();

        if (empty($errors)) {
            $submitted = $controller->getSubmitted();
            $result = $compare_model->addProduct($submitted['product'], $submitted);
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
     * Deletes a submitted product from comparison
     * @param \gplcart\core\models\ProductCompare $compare_model
     */
    public function deleteFromProductCompare($compare_model)
    {
        $controller = $this->getController();
        $product_id = $controller->getSubmitted('product_id');
        $result = $compare_model->deleteProduct($product_id);

        if ($controller->isAjax()) {
            $controller->outputJson($result);
        }

        $this->controlDeleteProductCompare($result, $product_id);
        $controller->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Controls result after a product has been deleted from comparison
     * @param array $result
     * @param integer $product_id
     */
    protected function controlDeleteProductCompare(array &$result, $product_id)
    {
        if (empty($result['redirect'])) {
            $segments = explode(',', $this->getController()->path());
            if (isset($segments[0]) && $segments[0] === 'compare' && !empty($segments[1])) {
                $ids = array_filter(array_map('trim', explode(',', $segments[1])), 'ctype_digit');
                unset($ids[array_search($product_id, $ids)]);
                $result['redirect'] = $segments[0] . '/' . implode(',', $ids);
            }
        }
    }

}
