<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Field as ModelsField;
use core\models\ProductClass as ModelsProductClass;

/**
 * Handles incoming requests and outputs data related to product classes
 */
class ProductClass extends Controller
{

    /**
     * Product model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param ModelsProductClass $product_class
     * @param ModelsField $field
     */
    public function __construct(ModelsProductClass $product_class,
            ModelsField $field)
    {
        parent::__construct();

        $this->field = $field;
        $this->product_class = $product_class;
    }

    /**
     * Returns the product classes overview page
     */
    public function classes()
    {
        if ($this->isSubmitted('action')) {
            $this->action();
        }

        $query = $this->getFilterQuery();
        $total = $this->getTotalClasses($query);
        $limit = $this->setPager($total, $query);
        $classes = $this->getClasses($limit, $query);

        $allowed = array('title', 'status');
        $this->setFilter($allowed, $query);

        $this->setData('classes', $classes);

        $this->setTitleClasses();
        $this->setBreadcrumbClasses();
        $this->outputClasses();
    }

    /**
     * Returns a number of total product classes
     * @param array $query
     * @return integer
     */
    public function getTotalClasses(array $query)
    {
        $query['count'] = true;
        return $this->product_class->getList($query);
    }

    /**
     * Displays the product class edit page
     * @param null|integer $product_class_id
     */
    public function edit($product_class_id = null)
    {
        $product_class = $this->get($product_class_id);
        $this->setData('product_class', $product_class);

        if ($this->isSubmitted('delete')) {
            $this->delete($product_class);
        }

        if ($this->isSubmitted('save')) {
            $this->submit($product_class);
        }

        $this->setTitleEdit($product_class);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Displays fields for a given product class
     * @param integer $product_class_id
     */
    public function fields($product_class_id)
    {
        $product_class = $this->get($product_class_id);
        $fields = $this->getFields($product_class_id);

        $this->setData('fields', $fields);
        $this->setData('product_class', $product_class);

        if ($this->isSubmitted('save')) {
            $this->submitFields($product_class);
        }

        $this->setTitleFields($product_class);
        $this->setBreadcrumbFields();
        $this->outputFields();
    }

    /**
     * Displays the field add form
     * @param integer $product_class_id
     */
    public function addField($product_class_id)
    {
        $product_class = $this->get($product_class_id);
        $fields = $this->getFields($product_class_id, true);

        $this->setData('fields', $fields);
        $this->setData('product_class', $product_class);

        if ($this->isSubmitted('save')) {
            $this->submitField($product_class);
        }

        $this->setTitleAddField($product_class);
        $this->setBreadcrumbAddField($product_class);
        $this->outputAddField();
    }

    /**
     * Returns an array of classes
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getClasses(array $limit, array $query)
    {
        $query['limit'] = $limit;
        return $this->product_class->getList($query);
    }

    /**
     * Sets titles on the product classes overview page
     */
    protected function setTitleClasses()
    {
        $this->setTitle($this->text('Product classes'));
    }

    /**
     * Sets breadcrumbs on the product classes overview page
     */
    protected function setBreadcrumbClasses()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the product class overview page
     */
    protected function outputClasses()
    {
        $this->output('content/product/class/list');
    }

    /**
     * Returns a product class
     * @param integer $product_class_id
     * @return array
     */
    protected function get($product_class_id)
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
     * Deletes a product class
     * @param array $product_class
     */
    protected function delete(array $product_class)
    {
        $this->controlAccess('product_class_delete');

        $deleted = $this->product_class->delete($product_class['product_class_id']);

        if ($deleted) {
            $this->redirect('admin/content/product/class', $this->text('Product class has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this product class. The most probable reason - it is used by one or more products'), 'danger');
    }

    /**
     * Applies an action to the selected product classes
     * @return boolean
     */
    protected function action()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('product_class_edit')) {
                $updated += (int) $this->product_class->update($id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('product_class_delete')) {
                $deleted += (int) $this->product_class->delete($id);
            }
        }

        if ($updated > 0) {
            $this->session->setMessage($this->text('Updated %num product classes', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num product classes', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a product class
     * @param array $product_class
     */
    protected function submit(array $product_class)
    {
        $this->setSubmitted('product_class');
        $this->validate($product_class);

        if ($this->hasErrors('product_class')) {
            return;
        }

        if (isset($product_class['product_class_id'])) {
            $this->controlAccess('product_class_edit');
            $this->product_class->update($product_class['product_class_id'], $this->getSubmitted());
            $this->redirect('admin/content/product/class', $this->text('Product class has been updated'), 'success');
        }

        $this->controlAccess('product_class_add');
        $this->product_class->add($this->getSubmitted());
        $this->redirect('admin/content/product/class', $this->text('Product class has been added'), 'success');
    }

    /**
     * Validates a products class
     * @param array $product_class
     */
    protected function validate(array $product_class)
    {
        $this->addValidator('title', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->setValidators($product_class);
    }

    /**
     * Sets titles on the edit product class page
     * @param array $product_class
     */
    protected function setTitleEdit(array $product_class)
    {
        if (isset($product_class['product_class_id'])) {
            $title = $this->text('Edit product class %name', array(
                '%name' => $product_class['title']));
        } else {
            $title = $this->text('Add product class');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit product class page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product/class')));
    }

    /**
     * Renders the product class edit page
     */
    protected function outputEdit()
    {
        $this->output('content/product/class/edit');
    }

    /**
     * Returns an array of fields for the given product class
     * @param integer $product_class_id
     * @param boolean $unique
     * @return array
     */
    protected function getFields($product_class_id, $unique = false)
    {
        $fields = $this->product_class->getFields($product_class_id);

        if (!$unique) {
            return $fields;
        }

        $unique_fields = array();
        foreach ($this->field->getList() as $field) {
            if (empty($fields[$field['field_id']])) {
                $unique_fields[$field['field_id']] = $field['title'];
            }
        }

        return $unique_fields;
    }

    /**
     * Saves the product class fields
     * @param array $product_class
     */
    protected function submitFields(array $product_class)
    {
        $this->controlAccess('product_class_edit');

        $fields = $this->setSubmitted('fields');

        $product_class_id = $product_class['product_class_id'];
        $this->product_class->deleteField(false, $product_class_id);

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

        $this->redirect('', $this->text('Product class %name has been updated', array(
                    '%name' => $product_class['title'])), 'success');
    }

    /**
     * Sets titles on the product class fields page
     * @param array $product_class
     */
    protected function setTitleFields(array $product_class)
    {
        $text = $this->text('Fields of %class', array(
            '%class' => $product_class['title']));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the product class fields page
     */
    protected function setBreadcrumbFields()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product/class')));
    }

    /**
     * Renders the product class fields page
     */
    protected function outputFields()
    {
        $this->output('content/product/class/fields');
    }

    /**
     * Adds fields to the given product class
     * @param array $product_class
     */
    protected function submitField(array $product_class)
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

        $path = "admin/content/product/class/field/{$product_class['product_class_id']}";
        $this->redirect($path, $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the add product class field page
     * @param array $product_class
     */
    protected function setTitleAddField(array $product_class)
    {
        $text = $this->text('Add field to %class', array(
            '%class' => $product_class['title']));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the add product class field page
     * @param array $product_class
     */
    protected function setBreadcrumbAddField(array $product_class)
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product/class')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Fields of %s', array('%s' => $product_class['title'])),
            'url' => $this->url("admin/content/product/class/field/{$product_class['product_class_id']}")));
    }

    /**
     * Renders the add fields page
     */
    protected function outputAddField()
    {
        $this->output('content/product/class/field');
    }

}
