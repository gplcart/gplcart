<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Field as FieldModel,
    gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to product classes
 */
class ProductClass extends BackendController
{

    /**
     * Product model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * An array of product class data
     * @var array
     */
    protected $data_product_class = array();

    /**
     * @param ProductClassModel $product_class
     * @param FieldModel $field
     */
    public function __construct(ProductClassModel $product_class,
            FieldModel $field)
    {
        parent::__construct();

        $this->field = $field;
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
        $this->setTotalListProductClass();
        $this->setPagerLimit();

        $this->setData('classes', $this->getListProductClass());
        $this->outputListProductClass();
    }

    /**
     * Set a total number of product classes
     */
    public function setTotalListProductClass()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->product_class->getList($query);
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
        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->getPosted('value');
        $selected = (array) $this->getPosted('selected', array());

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('product_class_edit')) {
                $updated += (int) $this->product_class->update($id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('product_class_delete')) {
                $deleted += (int) $this->product_class->delete($id);
            }
        }

        if ($updated > 0) {
            $vars = array('%num' => $updated);
            $message = $this->text('Updated %num product classes', $vars);
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $vars = array('%num' => $deleted);
            $message = $this->text('Deleted %num product classes', $vars);
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns an array of product classes
     * @return array
     */
    protected function getListProductClass()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        return (array) $this->product_class->getList($query);
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
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
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
     * Displays the edit product class page
     * @param null|integer $product_class_id
     */
    public function editProductClass($product_class_id = null)
    {
        $this->setProductClass($product_class_id);

        $this->setTitleEditProductClass();
        $this->setBreadcrumbEditProductClass();

        $this->setData('product_class', $this->data_product_class);

        $this->submitEditProductClass();
        $this->outputEditProductClass();
    }

    /**
     * Sets the product class data
     * @param integer $product_class_id
     */
    protected function setProductClass($product_class_id)
    {
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
        if ($this->isPosted('delete')) {
            $this->deleteProductClass();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditProductClass()) {
            return null;
        }

        if (isset($this->data_product_class['product_class_id'])) {
            $this->updateProductClass();
        } else {
            $this->addProductClass();
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

        $deleted = $this->product_class->delete($this->data_product_class['product_class_id']);

        if ($deleted) {
            $message = $this->text('Product class has been deleted');
            $this->redirect('admin/content/product-class', $message, 'success');
        }

        $message = $this->text('Unable to delete this product class');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a product class
     */
    protected function updateProductClass()
    {
        $this->controlAccess('product_class_edit');

        $values = $this->getSubmitted();
        $this->product_class->update($this->data_product_class['product_class_id'], $values);

        $message = $this->text('Product class has been updated');
        $this->redirect('admin/content/product-class', $message, 'success');
    }

    /**
     * Adds a new product class
     */
    protected function addProductClass()
    {
        $this->controlAccess('product_class_add');

        $this->product_class->add($this->getSubmitted());

        $message = $this->text('Product class has been added');
        $this->redirect('admin/content/product-class', $message, 'success');
    }

    /**
     * Sets title on the edit product class page
     */
    protected function setTitleEditProductClass()
    {
        $title = $this->text('Add product class');

        if (isset($this->data_product_class['product_class_id'])) {
            $vars = array('%name' => $this->data_product_class['title']);
            $title = $this->text('Edit product class %name', $vars);
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
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
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

    /**
     * Displays the field overview page
     * @param integer $product_class_id
     */
    public function fieldsProductClass($product_class_id)
    {
        $this->setProductClass($product_class_id);

        $this->setTitleFieldsProductClass();
        $this->setBreadcrumbFieldsProductClass();

        $this->setData('fields', $this->getFieldsProductClass($product_class_id));
        $this->setData('product_class', $this->data_product_class);

        $this->submitFieldsProductClass();
        $this->outputFieldsProductClass();
    }

    /**
     * Returns an array of fields for the given product class
     * @param integer $product_class_id
     * @param boolean $unique
     * @return array
     */
    protected function getFieldsProductClass($product_class_id, $unique = false)
    {
        $class_fields = $this->product_class->getFields($product_class_id);

        if (!$unique) {
            return $class_fields;
        }

        $unique_fields = array();
        $fields = (array) $this->field->getList();

        foreach ($fields as $field) {
            if (empty($class_fields[$field['field_id']])) {
                $type = ($field['type'] === 'option') ? $this->text('Option') : $this->text('Attribute');
                $unique_fields[$field['field_id']] = "{$field['title']} ($type)";
            }
        }

        return $unique_fields;
    }

    /**
     * Handles the submitted product class fields
     */
    protected function submitFieldsProductClass()
    {
        if ($this->isPosted('save')) {
            $this->updateFieldsProductClass();
        }
    }

    /**
     * Updates fields
     */
    protected function updateFieldsProductClass()
    {
        $this->controlAccess('product_class_edit');

        $fields = $this->setSubmitted('fields');
        $id = $this->data_product_class['product_class_id'];

        $this->product_class->deleteField($id);

        foreach ($fields as $field_id => $field) {

            if (empty($field['remove'])) {

                $field['field_id'] = $field_id;
                $field['product_class_id'] = $id;
                $field['required'] = !empty($field['required']);
                $field['multiple'] = !empty($field['multiple']);

                $this->product_class->addField($field);
            }
        }

        $this->redirect('', $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the field overview page
     */
    protected function setTitleFieldsProductClass()
    {
        $vars = array('%name' => $this->data_product_class['title']);
        $text = $this->text('Fields of %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the field overview page
     */
    protected function setBreadcrumbFieldsProductClass()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product-class')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the field overview page
     */
    protected function outputFieldsProductClass()
    {
        $this->output('content/product/class/fields');
    }

    /**
     * Displays the add field page
     * @param integer $product_class_id
     */
    public function editFieldProductClass($product_class_id)
    {
        $this->setProductClass($product_class_id);

        $this->setTitleEditFieldProductClass();
        $this->setBreadcrumbEditFieldProductClass();

        $fields = $this->getFieldsProductClass($product_class_id, true);

        $this->setData('fields', $fields);
        $this->setData('product_class', $this->data_product_class);

        $this->submitEditFieldProductClass();
        $this->outputEditFieldProductClass();
    }

    /**
     * Adds fields to the product class
     */
    protected function submitEditFieldProductClass()
    {
        if ($this->isPosted('save')) {
            $this->addFieldProductClass();
        }
    }

    /**
     * Adds fields to the product class
     */
    protected function addFieldProductClass()
    {
        $this->controlAccess('product_class_edit');

        $fields = $this->setSubmitted('fields');
        $id = $this->data_product_class['product_class_id'];

        foreach (array_values($fields) as $field_id) {

            $field = array(
                'field_id' => $field_id,
                'product_class_id' => $id
            );

            $this->product_class->addField($field);
        }

        $message = $this->text('Product class has been updated');
        $path = "admin/content/product-class/field/$id";
        $this->redirect($path, $message, 'success');
    }

    /**
     * Sets titles on the add field page
     */
    protected function setTitleEditFieldProductClass()
    {
        $vars = array('%name' => $this->data_product_class['title']);
        $text = $this->text('Add field to %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the add field page
     */
    protected function setBreadcrumbEditFieldProductClass()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product-class')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Fields of %name', array('%name' => $this->data_product_class['title'])),
            'url' => $this->url("admin/content/product-class/field/{$this->data_product_class['product_class_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the add field page
     */
    protected function outputEditFieldProductClass()
    {
        $this->output('content/product/class/field');
    }

}
