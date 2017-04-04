<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\ProductClass as ProductClassModel;
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
     * The current product class
     * @var array
     */
    protected $data_product_class = array();

    /**
     * Constructor
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
     * Returns the product classes overview page
     */
    public function listProductClass()
    {
        $this->actionProductClass();

        $this->setTitleListProductClass();
        $this->setBreadcrumbListProductClass();

        $query = $this->getFilterQuery();

        $allowed = array('title', 'status', 'product_class_id');
        $this->setFilter($allowed, $query);

        $total = $this->getTotalProductClass($query);
        $limit = $this->setPager($total, $query);

        $this->setData('classes', $this->getListProductClass($limit, $query));
        $this->outputListProductClass();
    }

    /**
     * Applies an action to the selected product classes
     */
    protected function actionProductClass()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

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
     * Returns a number of total product classes
     * @param array $query
     * @return integer
     */
    public function getTotalProductClass(array $query)
    {
        $query['count'] = true;
        return (int) $this->product_class->getList($query);
    }

    /**
     * Returns an array of classes
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListProductClass(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return (array) $this->product_class->getList($query);
    }

    /**
     * Sets titles on the product classes overview page
     */
    protected function setTitleListProductClass()
    {
        $this->setTitle($this->text('Product classes'));
    }

    /**
     * Sets breadcrumbs on the product classes overview page
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
     * Renders the product class overview page
     */
    protected function outputListProductClass()
    {
        $this->output('content/product/class/list');
    }

    /**
     * Displays the product class edit page
     * @param null|integer $product_class_id
     */
    public function editProductClass($product_class_id = null)
    {
        $this->setProductClass($product_class_id);

        $this->setTitleEditProductClass();
        $this->setBreadcrumbEditProductClass();

        $this->setData('product_class', $this->data_product_class);

        $this->submitProductClass();
        $this->outputEditProductClass();
    }

    /**
     * Returns a product class
     * @param integer $product_class_id
     * @return array
     */
    protected function setProductClass($product_class_id)
    {
        if (!is_numeric($product_class_id)) {
            return array();
        }

        $product_class = $this->product_class->get($product_class_id);

        if (empty($product_class)) {
            $this->outputHttpStatus(404);
        }

        $this->data_product_class = $product_class;
        return $product_class;
    }

    /**
     * Saves a submitted product class
     * @return null
     */
    protected function submitProductClass()
    {
        if ($this->isPosted('delete')) {
            $this->deleteProductClass();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateProductClass()) {
            return null;
        }

        if (isset($this->data_product_class['product_class_id'])) {
            $this->updateProductClass();
        } else {
            $this->addProductClass();
        }
    }

    /**
     * Validates a products class
     * @return bool
     */
    protected function validateProductClass()
    {
        $this->setSubmitted('product_class');

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_product_class);

        $this->validateComponent('product_class');

        return !$this->hasErrors('product_class');
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
     * Updates a product class with submitted values
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
     * Adds a new product class using an array of submitted values
     */
    protected function addProductClass()
    {
        $this->controlAccess('product_class_add');

        $this->product_class->add($this->getSubmitted());

        $message = $this->text('Product class has been added');
        $this->redirect('admin/content/product-class', $message, 'success');
    }

    /**
     * Sets titles on the edit product class page
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
     * Renders the product class edit page
     */
    protected function outputEditProductClass()
    {
        $this->output('content/product/class/edit');
    }

    /**
     * Displays fields for a given product class
     * @param integer $product_class_id
     */
    public function fieldsProductClass($product_class_id)
    {
        $this->setProductClass($product_class_id);

        $this->setTitleFieldsProductClass();
        $this->setBreadcrumbFieldsProductClass();

        $fields = $this->getFieldsProductClass($product_class_id);

        $this->setData('fields', $fields);
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
     * Saves the product class fields
     */
    protected function submitFieldsProductClass()
    {
        if ($this->isPosted('save')) {
            $this->updateFieldsProductClass();
        }
    }

    /**
     * Updates fields for a product class
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
     * Sets titles on the product class fields page
     */
    protected function setTitleFieldsProductClass()
    {
        $vars = array('%name' => $this->data_product_class['title']);
        $text = $this->text('Fields of %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the product class fields page
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
     * Renders the product class fields page
     */
    protected function outputFieldsProductClass()
    {
        $this->output('content/product/class/fields');
    }

    /**
     * Displays the field add form
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
     * Adds fields to the given product class
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
     * Sets titles on the add product class field page
     */
    protected function setTitleEditFieldProductClass()
    {
        $vars = array('%name' => $this->data_product_class['title']);
        $text = $this->text('Add field to %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the add product class field page
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
     * Renders the add fields page
     */
    protected function outputEditFieldProductClass()
    {
        $this->output('content/product/class/field');
    }

}
