<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\ProductClass as Pc;
use core\models\Field;

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
     * @param Pc $product_class
     * @param Field $field
     */
    public function __construct(Pc $product_class, Field $field)
    {
        parent::__construct();

        $this->product_class = $product_class;
        $this->field = $field;
    }

    /**
     * Returns the product classes overview page
     */
    public function classes()
    {

        $action = $this->request->post('action');
        $value = $this->request->post('value');
        $selected = $this->request->post('selected', array());

        if ($action) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalClasses($query), $query);
        $this->setFilter(array('title', 'status'), $query);

        $this->data['classes'] = $this->getClasses($total, $query);

        $this->setTitleClasses();
        $this->setBreadcrumbClasses();
        $this->outputClasses();
    }

    /**
     * Returns a number of total product classes
     * @param array $query
     * @return integer
     */
    public function getTotalClasses($query)
    {
        return $this->product_class->getList(array('count' => true) + $query);
    }

    /**
     * Returns an array of classes
     */
    protected function getClasses($limit, $query)
    {
        return $this->product_class->getList(array('limit' => $limit) + $query);
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
     * Displays the product class edit page
     * @param mixed $product_class_id
     */
    public function edit($product_class_id = null)
    {
        $product_class = $this->get($product_class_id);
        $this->data['product_class'] = $product_class;

        if ($this->request->post('delete')) {
            $this->delete($product_class);
        }

        if ($this->request->post('save')) {
            $this->submit($product_class);
        }

        $this->setTitleEdit($product_class);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
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

        if ($product_class) {
            return $product_class;
        }

        $this->outputError(404);
    }

    /**
     * Deletes a product class
     * @param array $product_class
     */
    protected function delete($product_class)
    {
        if (empty($product_class['product_class_id'])) {
            return;
        }

        $this->controlAccess('product_class_delete');

        if ($this->product_class->delete($product_class['product_class_id'])) {
            $this->redirect('admin/content/product/class', $this->text('Product class has been deleted'), 'success');
        }

        $this->redirect(false, $this->text('Unable to delete this product class. The most probable reason - it is used by one or more products'), 'danger');
    }
    
    /**
     * Applies an action to the selected product classes 
     * @param array $selected
     * @param string $action
     * @param string $value
     * @return boolean
     */
    protected function action($selected, $action, $value)
    {
        $updated = $deleted = 0;
        foreach ($selected as $id) {

            if ($action == 'status' && $this->access('product_class_edit')) {
                $updated += (int) $this->product_class->update($id, array('status' => (int) $value));
            }

            if ($action == 'delete' && $this->access('product_class_delete')) {
                $deleted += (int) $this->product_class->delete($id);
            }
        }

        if ($updated) {
            $this->session->setMessage($this->text('Updated %num product classes', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted) {
            $this->session->setMessage($this->text('Deleted %num product classes', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

    /**
     * Saves a product class
     * @param array $product_class
     */
    protected function submit($product_class)
    {
        $this->submitted = $this->request->post('product_class', array());
        $this->validate();

        if ($this->formErrors()) {
            $this->data['product_class'] = $this->submitted;
            return;
        }

        if (isset($product_class['product_class_id'])) {
            $this->controlAccess('product_class_edit');
            $this->product_class->update($product_class['product_class_id'], $this->submitted);
            $this->redirect('admin/content/product/class', $this->text('Product class has been updated'), 'success');
        }

        $this->controlAccess('product_class_add');
        $this->product_class->add($this->submitted);
        $this->redirect('admin/content/product/class', $this->text('Product class has been added'), 'success');
    }

    /**
     * Validates a products class
     * @param array $data
     */
    protected function validate()
    {
        if (empty($this->submitted['title']) || mb_strlen($this->submitted['title']) > 255) {
            $this->data['form_errors']['title'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Sets titles on the edit product class page
     * @param array $product_class
     */
    protected function setTitleEdit($product_class)
    {

        if (isset($product_class['product_class_id'])) {
            $title = $this->text('Edit product class %name', array('%name' => $product_class['title']));
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
     * Displays fields for a given product class
     * @param integer $product_class_id
     */
    public function fields($product_class_id)
    {
        $product_class = $this->get($product_class_id);

        $this->data['product_class'] = $product_class;
        $this->data['fields'] = $this->getFields($product_class_id);

        if ($this->request->post('save')) {
            $this->submitFields($product_class);
        }

        $this->setTitleFields($product_class);
        $this->setBreadcrumbFields();
        $this->outputFields();
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
    protected function submitFields($product_class)
    {
        $this->controlAccess('product_class_edit');
        $this->submitted = $this->request->post('fields', array());

        $this->product_class->deleteField(false, $product_class['product_class_id']);

        foreach ($this->submitted as $field_id => $field) {
            $field['product_class_id'] = $product_class['product_class_id'];
            $field['required'] = !empty($field['required']);
            $field['multiple'] = !empty($field['multiple']);
            $field['field_id'] = $field_id;
            $this->product_class->addField($field);
        }

        $this->redirect(false, $this->text('Product class %name has been updated', array(
                    '%name' => $product_class['title'])), 'success');
    }

    /**
     * Sets titles on the product class fields page
     * @param array $product_class
     */
    protected function setTitleFields($product_class)
    {
        $this->setTitle($this->text('Fields of %class', array('%class' => $product_class['title'])));
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
     * Displays the field add form
     * @param integer $product_class_id
     */
    public function addField($product_class_id)
    {
        $product_class = $this->get($product_class_id);

        $this->data['product_class'] = $product_class;
        $this->data['fields'] = $this->getFields($product_class_id, true);

        if ($this->request->post('save')) {
            $this->submitField($product_class);
        }

        $this->setTitleAddField($product_class);
        $this->setBreadcrumbAddField($product_class);
        $this->outputAddField();
    }

    /**
     * Adds fields to the given product class
     * @param array $product_class
     */
    protected function submitField($product_class)
    {
        $this->controlAccess('product_class_edit');

        foreach ($this->request->post('fields') as $field_id) {

            $field = array(
                'product_class_id' => $product_class['product_class_id'],
                'field_id' => $field_id
            );

            $this->product_class->addField($field);
        }

        $url = "admin/content/product/class/field/{$product_class['product_class_id']}";
        $this->redirect($url, $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the add product class field page
     * @param array $product_class
     */
    protected function setTitleAddField($product_class)
    {
        $this->setTitle($this->text('Fields of %class', array('%class' => $product_class['title'])));
    }

    /**
     * Sets breadcrumbs on the add product class field page
     * @param array $product_class
     */
    protected function setBreadcrumbAddField($product_class)
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
