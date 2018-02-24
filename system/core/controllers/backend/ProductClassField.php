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
use gplcart\core\models\ProductClassField as ProductClassFieldModel;

/**
 * Handles incoming requests and outputs data related to product class fields
 */
class ProductClassField extends Controller
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Product model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Product class field model instance
     * @var \gplcart\core\models\ProductClassField $product_class_field
     */
    protected $product_class_field;

    /**
     * An array of product class data
     * @var array
     */
    protected $data_product_class = array();

    /**
     * @param ProductClassModel $product_class
     * @param ProductClassFieldModel $product_class_field
     * @param FieldModel $field
     */
    public function __construct(ProductClassModel $product_class, ProductClassFieldModel $product_class_field, FieldModel $field)
    {
        parent::__construct();

        $this->field = $field;
        $this->product_class = $product_class;
        $this->product_class_field = $product_class_field;
    }

    /**
     * Sets the product class data
     * @param integer $product_class_id
     */
    protected function setProductClassProductClassField($product_class_id)
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
     * Route callback for the product class field overview page
     * @param integer $product_class_id
     */
    public function listProductClassField($product_class_id)
    {
        $this->setProductClassProductClassField($product_class_id);
        $this->setTitleListProductClassField();
        $this->setBreadcrumbListProductClassField();
        $this->setFilterListProductClassField();

        $this->setData('field_types', $this->field->getTypes());
        $this->setData('fields', $this->getListProductClassField());
        $this->setData('product_class', $this->data_product_class);

        $this->submitListProductClassField();
        $this->outputListProductClassField();
    }

    /**
     * Sets filter on the product class fields overview page
     */
    protected function setFilterListProductClassField()
    {
        $allowed = array('title', 'required', 'multiple', 'type',
            'weight', 'field_id', 'product_class_field_id');

        $this->setFilter($allowed);
    }

    /**
     * Returns an array of fields for the given product class
     * @param array $conditions
     * @return array
     */
    protected function getListProductClassField(array $conditions = array())
    {
        $conditions += $this->query_filter;
        $conditions['product_class_id'] = $this->data_product_class['product_class_id'];

        return (array) $this->product_class_field->getList($conditions);
    }

    /**
     * Returns an array of product class fields indexed by field ID which are unique for the product class
     * @return array
     */
    protected function getUniqueListProductClassField()
    {
        $types = $this->field->getTypes();
        $fields = $this->getListProductClassField(array('index' => 'field_id'));

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
    protected function submitListProductClassField()
    {
        if ($this->isPosted('save')) {
            $this->updateListProductClassField();
        }
    }

    /**
     * Updates product class fields
     */
    protected function updateListProductClassField()
    {
        $this->controlAccess('product_class_edit');

        foreach ((array) $this->setSubmitted('fields') as $id => $field) {

            if (!empty($field['remove'])) {
                $this->product_class_field->delete($id);
                continue;
            }

            $field['required'] = !empty($field['required']);
            $field['multiple'] = !empty($field['multiple']);
            $this->product_class_field->update($id, $field);
        }

        $this->redirect('', $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the product class field overview page
     */
    protected function setTitleListProductClassField()
    {
        $text = $this->text('Fields of %name', array('%name' => $this->data_product_class['title']));
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the product class field overview page
     */
    protected function setBreadcrumbListProductClassField()
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
     * Render and output the product class field overview page
     */
    protected function outputListProductClassField()
    {
        $this->output('content/product/class/field/list');
    }

    /**
     * Route callback
     * Displays the edit product class field page
     * @param integer $product_class_id
     */
    public function editProductClassField($product_class_id)
    {
        $this->setProductClassProductClassField($product_class_id);
        $this->setTitleEditProductClassField();
        $this->setBreadcrumbEditProductClassField();

        $this->setData('product_class', $this->data_product_class);
        $this->setData('fields', $this->getUniqueListProductClassField());

        $this->submitEditProductClassField();
        $this->outputEditProductClassField();
    }

    /**
     * Handles adding product class fields
     */
    protected function submitEditProductClassField()
    {
        if ($this->isPosted('save') && $this->validateEditProductClassField()) {
            $this->addProductClassField();
        }
    }

    /**
     * Validates an array of submitted product class fields
     * @return bool
     */
    protected function validateEditProductClassField()
    {
        $this->setSubmitted('product_class');
        $this->setSubmitted('product_class_id', $this->data_product_class['product_class_id']);

        $this->validateComponent('product_class_field');

        return !$this->hasErrors(false);
    }

    /**
     * Adds product class fields
     */
    protected function addProductClassField()
    {
        $this->controlAccess('product_class_edit');

        foreach ((array) $this->getSubmitted('field_id', array()) as $field_id) {

            $field = array(
                'field_id' => $field_id,
                'product_class_id' => $this->data_product_class['product_class_id']
            );

            $this->product_class_field->add($field);
        }

        $url = "admin/content/product-class/field/{$this->data_product_class['product_class_id']}";
        $this->redirect($url, $this->text('Product class has been updated'), 'success');
    }

    /**
     * Sets titles on the add product class field page
     */
    protected function setTitleEditProductClassField()
    {
        $text = $this->text('Add field to %name', array('%name' => $this->data_product_class['title']));
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the edit product class field page
     */
    protected function setBreadcrumbEditProductClassField()
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

        $breadcrumbs[] = array(
            'text' => $this->text('Fields of %name', array('%name' => $this->data_product_class['title'])),
            'url' => $this->url("admin/content/product-class/field/{$this->data_product_class['product_class_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the edit product class field page
     */
    protected function outputEditProductClassField()
    {
        $this->output('content/product/class/field/edit');
    }

}
