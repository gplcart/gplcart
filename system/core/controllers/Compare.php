<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Field as ModelsField;
use core\models\FieldValue as ModelsFieldValue;
use core\models\ProductClass as ModelsProductClass;
use core\models\ProductField as ModelsProductField;

use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product comparison
 */
class Compare extends FrontendController
{

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;
    
    /**
     * Product field model instance
     * @var \core\models\ProductField $product_field
     */
    protected $product_field;

    /**
     * Field class instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Field values instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;
    
    /**
     * Constructor
     * @param ModelsProductClass $product_class
     * @param ModelsField $field
     * @param ModelsFieldValue $field_value
     * @param ModelsProductField $product_field
     */
    public function __construct(ModelsProductClass $product_class,
            ModelsField $field, ModelsFieldValue $field_value, ModelsProductField $product_field)
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
            'buttons' => array('cart_add', 'wishlist_add'),
            'view' => $this->setting('compare_view', 'grid'),
            'imagestyle' => $this->setting('image_style_product_grid', 3)
        );

        $products = $this->getProducts($data, $options);
        $reindexed = $this->reindexProductsCompare($products);

        $this->setData('products', $reindexed);
    }

    /**
     * Returns an array of products keyed by their class
     * @param array $products
     * @return array
     */
    protected function reindexProductsCompare(array $products)
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
        $breadcrumbs = array();
        
        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumbs($breadcrumbs);
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
     * @param string $compared
     */
    public function compare($compared)
    {
        $this->setDataCompare();

        $this->setTitleCompare();
        $this->setBreadcrumbCompare();
        $this->outputCompare();
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
            return;
        }

        $reindexed = $this->reindexProductsCompare($products);
        $product_class_id = key($reindexed);
        $fields = array('option' => array(), 'attribute' => array());

        foreach ($reindexed[$product_class_id] as $product_id => &$product) {

            $product_fields = $this->product_field->getList($product_id);

            foreach ($product_fields as $type => $items) {

                $fields = $this->field->getList(array('field_id' => array_keys($items)));
                $values = $this->field_value->getList(array('field_id' => array_keys($items)));

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
