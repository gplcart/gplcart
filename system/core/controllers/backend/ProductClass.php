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

        $query = $this->getFilterQuery();
        $total = $this->getTotalProductClass($query);
        $limit = $this->setPager($total, $query);
        $classes = $this->getListProductClass($limit, $query);

        $allowed = array('title', 'status', 'product_class_id');
        $this->setFilter($allowed, $query);

        $this->setData('classes', $classes);

        $this->setTitleListProductClass();
        $this->setBreadcrumbListProductClass();
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
            $message = $this->text('Updated %num product classes', array(
                '%num' => $updated
            ));
            $this->setMessage($message, 'success', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num product classes', array(
                '%num' => $deleted
            ));
            $this->setMessage($message, 'success', true);
        }

        return null;
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
        $product_class = $this->getProductClass($product_class_id);
        $this->setData('product_class', $product_class);

        $this->submitProductClass($product_class);

        $this->setTitleEditProductClass($product_class);
        $this->setBreadcrumbEditProductClass();
        $this->outputEditProductClass();
    }

    /**
     * Returns a product class
     * @param integer $product_class_id
     * @return array
     */
    protected function getProductClass($product_class_id)
    {
        if (!is_numeric($product_class_id)) {
            return array();
        }

        $product_class = $this->product_class->get($product_class_id);

        if (empty($product_class)) {
            $this->outputError(404);
        }

        return $product_class;
    }

    /**
     * Saves a submitted product class
     * @param array $product_class
     * @return null|void
     */
    protected function submitProductClass(array $product_class)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteProductClass($product_class);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('product_class');
        $this->validateProductClass($product_class);

        if ($this->hasErrors('product_class')) {
            return null;
        }

        if (isset($product_class['product_class_id'])) {
            return $this->updateProductClass($product_class);
        }

        return $this->addProductClass();
    }

    /**
     * Deletes a product class
     * @param array $product_class
     */
    protected function deleteProductClass(array $product_class)
    {
        $this->controlAccess('product_class_delete');

        $deleted = $this->product_class->delete($product_class['product_class_id']);

        if ($deleted) {
            $message = $this->text('Product class has been deleted');
            $this->redirect('admin/content/product-class', $message, 'success');
        }

        $message = $this->text('Unable to delete this product class');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a products class
     * @param array $product_class
     */
    protected function validateProductClass(array $product_class)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $product_class);
        $this->validate('product_class');
    }

    /**
     * Updates a product class with submitted values
     * @param array $product_class
     */
    protected function updateProductClass(array $product_class)
    {
        $this->controlAccess('product_class_edit');

        $values = $this->getSubmitted();
        $this->product_class->update($product_class['product_class_id'], $values);

        $message = $this->text('Product class has been updated');
        $this->redirect('admin/content/product-class', $message, 'success');
    }

    /**
     * Adds a new product class using an array of submitted values
     */
    protected function addProductClass()
    {
        $this->controlAccess('product_class_add');

        $values = $this->getSubmitted();
        $this->product_class->add($values);

        $message = $this->text('Product class has been added');
        $this->redirect('admin/content/product-class', $message, 'success');
    }

    /**
     * Sets titles on the edit product class page
     * @param array $product_class
     */
    protected function setTitleEditProductClass(array $product_class)
    {
        $title = $this->text('Add product class');

        if (isset($product_class['product_class_id'])) {
            $title = $this->text('Edit product class %name', array(
                '%name' => $product_class['title']
            ));
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
        $product_class = $this->getProductClass($product_class_id);
        $fields = $this->getFieldsProductClass($product_class_id);

        $this->setData('fields', $fields);
        $this->setData('product_class', $product_class);

        $this->submitFieldsProductClass($product_class);

        $this->setTitleFieldsProductClass($product_class);
        $this->setBreadcrumbFieldsProductClass();
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
     * @param array $product_class
     */
    protected function submitFieldsProductClass(array $product_class)
    {
        if ($this->isPosted('save')) {
            $this->updateFieldsProductClass($product_class);
        }
    }

    /**
     * Updates fields for a product class
     * @param array $product_class
     */
    protected function updateFieldsProductClass(array $product_class)
    {
        $this->controlAccess('product_class_edit');

        $fields = $this->setSubmitted('fields');
        $product_class_id = $product_class['product_class_id'];

        $this->product_class->deleteField($product_class_id);

        foreach ($fields as $field_id => $field) {

            if (!empty($field['remove'])) {
                continue;
            }

            $field['field_id'] = $field_id;
            $field['product_class_id'] = $product_class_id;
            $field['required'] = !empty($field['required']);
            $field['multiple'] = !empty($field['multiple']);

            $this->product_class->addField($field);
        }

        $options = array('%name' => $product_class['title']);
        $message = $this->text('Product class %name has been updated', $options);
        $this->redirect('', $message, 'success');
    }

    /**
     * Sets titles on the product class fields page
     * @param array $product_class
     */
    protected function setTitleFieldsProductClass(array $product_class)
    {
        $text = $this->text('Fields of %class', array(
            '%class' => $product_class['title']
        ));

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
        $product_class = $this->getProductClass($product_class_id);
        $fields = $this->getFieldsProductClass($product_class_id, true);

        $this->setData('fields', $fields);
        $this->setData('product_class', $product_class);

        $this->submitEditFieldProductClass($product_class);

        $this->setTitleEditFieldProductClass($product_class);
        $this->setBreadcrumbEditFieldProductClass($product_class);
        $this->outputEditFieldProductClass();
    }

    /**
     * Adds fields to the given product class
     * @param array $product_class
     */
    protected function submitEditFieldProductClass(array $product_class)
    {
        if ($this->isPosted('save')) {
            $this->addFieldProductClass($product_class);
        }
    }

    /**
     * Adds fields to the product class
     * @param array $product_class
     */
    protected function addFieldProductClass(array $product_class)
    {
        $this->controlAccess('product_class_edit');

        $fields = $this->setSubmitted('fields');

        foreach (array_values($fields) as $field_id) {

            $field = array(
                'field_id' => $field_id,
                'product_class_id' => $product_class['product_class_id']
            );

            $this->product_class->addField($field);
        }

        $message = $this->text('Product class has been updated');
        $path = "admin/content/product-class/field/{$product_class['product_class_id']}";
        $this->redirect($path, $message, 'success');
    }

    /**
     * Sets titles on the add product class field page
     * @param array $product_class
     */
    protected function setTitleEditFieldProductClass(array $product_class)
    {
        $text = $this->text('Add field to %class', array(
            '%class' => $product_class['title']
        ));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the add product class field page
     * @param array $product_class
     */
    protected function setBreadcrumbEditFieldProductClass(array $product_class)
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
            'text' => $this->text('Fields of %s', array('%s' => $product_class['title'])),
            'url' => $this->url("admin/content/product-class/field/{$product_class['product_class_id']}")
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
