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
        $this->setPagerListProductClass();

        $this->setData('classes', $this->getListProductClass());
        $this->outputListProductClass();
    }

    /**
     * Set pager
     * @return array
     */
    public function setPagerListProductClass()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->product_class->getList($options)
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
        $options = $this->query_filter;
        $options['limit'] = $this->data_limit;
        return (array) $this->product_class->getList($options);
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
        $this->setBreadcrumbHome();
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

        $this->setData('can_delete', $this->canDelete());
        $this->setData('product_class', $this->data_product_class);

        $this->submitEditProductClass();
        $this->outputEditProductClass();
    }

    /**
     * Whether a product class can be deleted
     * @return bool
     */
    protected function canDelete()
    {
        return isset($this->data_product_class['product_class_id'])//
                && $this->access('product_class_delete')//
                && $this->product_class->canDelete($this->data_product_class['product_class_id']);
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
        if ($this->isPosted('delete') && $this->canDelete()) {
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
        $this->redirect('', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Updates a product class
     */
    protected function updateProductClass()
    {
        $this->controlAccess('product_class_edit');
        $this->product_class->update($this->data_product_class['product_class_id'], $this->getSubmitted());
        $this->redirect('admin/content/product-class', $this->text('Product class has been updated'), 'success');
    }

    /**
     * Adds a new product class
     */
    protected function addProductClass()
    {
        $this->controlAccess('product_class_add');
        $this->product_class->add($this->getSubmitted());
        $this->redirect('admin/content/product-class', $this->text('Product class has been added'), 'success');
    }

    /**
     * Sets title on the edit product class page
     */
    protected function setTitleEditProductClass()
    {
        if (isset($this->data_product_class['product_class_id'])) {
            $vars = array('%name' => $this->data_product_class['title']);
            $title = $this->text('Edit %name', $vars);
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
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product-class')
        );

        $this->setBreadcrumb($breadcrumb);
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
        $this->setFilterFieldsProductClass();

        $this->setData('field_types', $this->field->getTypes());
        $this->setData('fields', $this->getFieldsProductClass());
        $this->setData('product_class', $this->data_product_class);

        $this->submitFieldsProductClass();
        $this->outputFieldsProductClass();
    }

    /**
     * Sets filter on the product class fields overview page
     */
    protected function setFilterFieldsProductClass()
    {
        $allowed = array('title', 'required', 'multiple', 'type', 'weight');
        $this->setFilter($allowed);
    }

    /**
     * Returns an array of fields for the given product class
     * @param boolean $unique
     * @return array
     */
    protected function getFieldsProductClass($unique = false)
    {
        $options = array(
            'product_class_id' => $this->data_product_class['product_class_id']) + $this->query_filter;

        $fields = $this->product_class->getFields($options);
        return $unique ? $this->prepareFieldsProductClass($fields) : $fields;
    }

    /**
     * Prepares an array of product class fields
     * @param array $fields
     * @return array
     */
    protected function prepareFieldsProductClass(array $fields)
    {
        $types = $this->field->getTypes();

        $unique = array();
        foreach ((array) $this->field->getList() as $field) {
            if (empty($fields[$field['field_id']])) {
                $type = empty($types[$field['type']]) ? $this->text('Unknown') : $types[$field['type']];
                $unique[$field['field_id']] = "{$field['title']} ($type)";
            }
        }

        return $unique;
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
     * Updates product class fields
     */
    protected function updateFieldsProductClass()
    {
        $this->controlAccess('product_class_edit');
        $id = $this->data_product_class['product_class_id'];
        $this->product_class->deleteField($id);

        foreach ($this->setSubmitted('fields') as $field_id => $field) {

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
        $this->setTitle($this->text('Fields of %name', $vars));
    }

    /**
     * Sets breadcrumbs on the field overview page
     */
    protected function setBreadcrumbFieldsProductClass()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Product classes'),
            'url' => $this->url('admin/content/product-class')
        );

        $this->setBreadcrumb($breadcrumb);
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

        $this->setData('product_class', $this->data_product_class);
        $this->setData('fields', $this->getFieldsProductClass(true));

        $this->submitEditFieldProductClass();
        $this->outputEditFieldProductClass();
    }

    /**
     * Adds fields to the product class
     */
    protected function submitEditFieldProductClass()
    {
        if ($this->isPosted('save') && $this->validateEditFieldProductClass()) {
            $this->addFieldProductClass();
        }
    }

    /**
     * Validates an array of submitted fields
     * @return bool
     */
    protected function validateEditFieldProductClass()
    {
        $this->setSubmitted('field');
        $this->validateElement('values', 'required');

        return !$this->hasErrors(false);
    }

    /**
     * Adds fields to the product class
     */
    protected function addFieldProductClass()
    {
        $this->controlAccess('product_class_edit');
        $id = $this->data_product_class['product_class_id'];
        foreach (array_values($this->getSubmitted('values')) as $field_id) {
            $field = array('field_id' => $field_id, 'product_class_id' => $id);
            $this->product_class->addField($field);
        }

        $this->redirect("admin/content/product-class/field/$id", $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the add field page
     */
    protected function setTitleEditFieldProductClass()
    {
        $vars = array('%name' => $this->data_product_class['title']);
        $this->setTitle($this->text('Add field to %name', $vars));
    }

    /**
     * Sets breadcrumbs on the add field page
     */
    protected function setBreadcrumbEditFieldProductClass()
    {
        $this->setBreadcrumbHome();

        $breadcrumbs = array();

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
        $this->output('content/product/class/edit_field');
    }

}
