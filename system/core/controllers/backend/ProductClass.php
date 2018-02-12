<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\ProductClass as ProductClassModel;

/**
 * Handles incoming requests and outputs data related to product classes
 */
class ProductClass extends Controller
{

    /**
     * Product model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of product class data
     * @var array
     */
    protected $data_product_class = array();

    /**
     * @param ProductClassModel $product_class
     */
    public function __construct(ProductClassModel $product_class)
    {
        parent::__construct();

        $this->product_class = $product_class;
    }

    /**
     * Returns the product class overview page
     */
    public function listProductClass()
    {
        $this->actionListProductClass();
        $this->setTitleListProductClass();
        $this->setBreadcrumbListProductClass();
        $this->setFilterListProductClass();
        $this->setPagerListProductClass();

        $this->setData('product_classes', $this->getListProductClass());
        $this->outputListProductClass();
    }

    /**
     * Set pager on the product class overview page
     * @return array
     */
    public function setPagerListProductClass()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->product_class->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Set filter on the product class overview page
     */
    protected function setFilterListProductClass()
    {
        $this->setFilter(array('title', 'status', 'product_class_id'));
    }

    /**
     * Applies an action to the selected product classes
     */
    protected function actionListProductClass()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        $updated = $deleted = 0;

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('product_class_edit')) {
                $updated += (int) $this->product_class->update($id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('product_class_delete')) {
                $deleted += (int) $this->product_class->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($message, 'success');
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of product classes
     * @return array
     */
    protected function getListProductClass()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        return (array) $this->product_class->getList($conditions);
    }

    /**
     * Sets titles on the product class overview page
     */
    protected function setTitleListProductClass()
    {
        $this->setTitle($this->text('Product classes'));
    }

    /**
     * Sets breadcrumbs on the product class overview page
     */
    protected function setBreadcrumbListProductClass()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the product class overview page
     */
    protected function outputListProductClass()
    {
        $this->output('content/product/class/list');
    }

    /**
     * Route callback for the edit product class page
     * @param null|integer $product_class_id
     */
    public function editProductClass($product_class_id = null)
    {
        $this->setProductClass($product_class_id);
        $this->setTitleEditProductClass();
        $this->setBreadcrumbEditProductClass();

        $this->setData('can_delete', $this->canDeleteProductClass());
        $this->setData('product_class', $this->data_product_class);

        $this->submitEditProductClass();
        $this->outputEditProductClass();
    }

    /**
     * Whether a product class can be deleted
     * @return bool
     */
    protected function canDeleteProductClass()
    {
        return isset($this->data_product_class['product_class_id'])
            && $this->access('product_class_delete')
            && $this->product_class->canDelete($this->data_product_class['product_class_id']);
    }

    /**
     * Sets the product class data
     * @param integer $product_class_id
     */
    protected function setProductClass($product_class_id)
    {
        $this->data_product_class = array();

        if (is_numeric($product_class_id)) {

            $this->data_product_class = $this->product_class->get($product_class_id);

            if (empty($this->data_product_class)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted product class
     */
    protected function submitEditProductClass()
    {
        if ($this->isPosted('delete') && $this->canDeleteProductClass()) {
            $this->deleteProductClass();
        } else if ($this->isPosted('save') && $this->validateEditProductClass()) {
            if (isset($this->data_product_class['product_class_id'])) {
                $this->updateProductClass();
            } else {
                $this->addProductClass();
            }
        }
    }

    /**
     * Validates a products class data
     * @return bool
     */
    protected function validateEditProductClass()
    {
        $this->setSubmitted('product_class');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_product_class);

        $this->validateComponent('product_class');

        return !$this->hasErrors();
    }

    /**
     * Deletes a product class
     */
    protected function deleteProductClass()
    {
        $this->controlAccess('product_class_delete');

        if ($this->product_class->delete($this->data_product_class['product_class_id'])) {
            $this->redirect('admin/content/product-class', $this->text('Product class has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Product class has not been deleted'), 'warning');
    }

    /**
     * Updates a product class
     */
    protected function updateProductClass()
    {
        $this->controlAccess('product_class_edit');

        if ($this->product_class->update($this->data_product_class['product_class_id'], $this->getSubmitted())) {
            $this->redirect('admin/content/product-class', $this->text('Product class has been updated'), 'success');
        }

        $this->redirect('', $this->text('Product class has not been updated'), 'warning');
    }

    /**
     * Adds a new product class
     */
    protected function addProductClass()
    {
        $this->controlAccess('product_class_add');

        if ($this->product_class->add($this->getSubmitted())) {
            $this->redirect('admin/content/product-class', $this->text('Product class has been added'), 'success');
        }

        $this->redirect('', $this->text('Product class has not been added'), 'warning');
    }

    /**
     * Sets title on the edit product class page
     */
    protected function setTitleEditProductClass()
    {
        if (isset($this->data_product_class['product_class_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_product_class['title']));
        } else {
            $title = $this->text('Add product class');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit product class page
     */
    protected function setBreadcrumbEditProductClass()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product-class')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit product class page
     */
    protected function outputEditProductClass()
    {
        $this->output('content/product/class/edit');
    }

}
