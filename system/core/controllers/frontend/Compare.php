<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\models\ProductField as ProductFieldModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product comparison
 */
class Compare extends FrontendController
{

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Product field model instance
     * @var \gplcart\core\models\ProductField $product_field
     */
    protected $product_field;

    /**
     * Field class instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Field values instance
     * @var \gplcart\core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param ProductClassModel $product_class
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param ProductFieldModel $product_field
     */
    public function __construct(ProductClassModel $product_class,
            FieldModel $field, FieldValueModel $field_value,
            ProductFieldModel $product_field)
    {
        parent::__construct();

        $this->field = $field;
        $this->field_value = $field_value;
        $this->product_class = $product_class;
        $this->product_field = $product_field;
    }

    /**
     * Displays the select to compare page
     */
    public function selectCompare()
    {
        $this->setDataSelectCompare();
        $this->setTitleSelectCompare();
        $this->setBreadcrumbSelectCompare();
        $this->outputSelectCompare();
    }

    /**
     * Sets products to be compared
     */
    protected function setDataSelectCompare()
    {
        $data = array('product_id' => $this->compare_content);

        $options = array(
            'view' => $this->settings('compare_view', 'grid'),
            'buttons' => array('cart_add', 'wishlist_add', 'compare_remove')
        );

        $products = $this->getProducts($data, $options);
        $reindexed = $this->prepareProductsCompare($products);

        $this->setData('products', $reindexed);
    }

    /**
     * Returns an array of products keyed by their class
     * @param array $products
     * @return array
     */
    protected function prepareProductsCompare(array $products)
    {
        $prepared = array();
        foreach ($products as $product_id => $product) {
            $prepared[$product['product_class_id']][$product_id] = $product;
        }

        return $prepared;
    }

    /**
     * Sets titles on the select compared products page
     */
    protected function setTitleSelectCompare()
    {
        $this->setTitle($this->text('Comparison'));
    }

    /**
     * Sets breadcrumbs on the select compared products page
     */
    protected function setBreadcrumbSelectCompare()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the select compared products page
     */
    protected function outputSelectCompare()
    {
        $this->output('compare/select');
    }

    /**
     * Displays the product compare page
     * @param string $ids
     */
    public function compare($ids)
    {
        $this->controlAccessCompare();

        $this->setDataCompare();

        $this->setTitleCompare();
        $this->setBreadcrumbCompare();
        $this->outputCompare();
    }

    /**
     * Controls access to the comparison page
     */
    protected function controlAccessCompare()
    {
        if (count($this->compare_content) < 2) {
            $this->redirect('compare');
        }
    }

    /**
     * Sets product field data on the product compare page
     */
    protected function setDataCompare()
    {
        $options = array(
            'buttons' => array(
                'cart_add',
                'wishlist_add',
                'compare_remove'
            )
        );

        $conditions = array('product_id' => $this->compare_content);
        $products = $this->getProducts($conditions, $options);

        if (empty($products)) {
            return null;
        }

        $reindexed = $this->prepareProductsCompare($products);
        $product_class_id = key($reindexed);
        $fields = array('option' => array(), 'attribute' => array());

        foreach ($reindexed[$product_class_id] as $product_id => &$product) {

            $product_fields = $this->product_field->getList($product_id);

            foreach ($product_fields as $type => $items) {

                $fields = (array) $this->field->getList(array('field_id' => array_keys($items)));
                $values = (array) $this->field_value->getList(array('field_id' => array_keys($items)));

                foreach ($fields as $field_id => $field) {
                    $fields[$type][$field_id] = $field['title'];
                    foreach ($items[$field_id] as $field_value_id) {
                        $product["{$type}_values"][$field_id][] = $values[$field_value_id]['title'];
                    }
                }
            }
        }

        $this->setData('products', $products);
        $this->setData('option_fields', $fields['option']);
        $this->setData('attribute_fields', $fields['attribute']);
        return null;
    }

    /**
     * Sets titles on the product compare page
     */
    protected function setTitleCompare()
    {
        $this->setTitle($this->text('Comparison'));
    }

    /**
     * Sets breadcrumbs on the product compare page
     */
    protected function setBreadcrumbCompare()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home'));

        $breadcrumbs[] = array(
            'url' => $this->url('compare'),
            'text' => $this->text('All compared products'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders product compare templates
     */
    protected function outputCompare()
    {
        $this->output('compare/compare');
    }

}
