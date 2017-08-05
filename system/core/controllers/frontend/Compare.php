<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\ProductClass as ProductClassModel,
    gplcart\core\models\ProductField as ProductFieldModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product comparison
 */
class Compare extends FrontendController
{

    use \gplcart\core\traits\Product;

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
     * An array of products to compare
     * @var array
     */
    protected $data_products = array();

    /**
     * @param ProductClassModel $product_class
     * @param ProductFieldModel $product_field
     */
    public function __construct(ProductClassModel $product_class,
            ProductFieldModel $product_field)
    {
        parent::__construct();

        $this->product_class = $product_class;
        $this->product_field = $product_field;
    }

    /**
     * Displays the select to compare page
     */
    public function selectCompare()
    {
        $this->setTitleSelectCompare();
        $this->setBreadcrumbSelectCompare();

        $this->setData('products', $this->getProductsSelectCompare());
        $this->outputSelectCompare();
    }

    /**
     * Returns an array of products reindexed by a class ID
     * @return array
     */
    protected function getProductsSelectCompare()
    {
        $conditions = array(
            'product_id' => $this->getComparison());

        $options = array(
            'view' => $this->settings('compare_view', 'grid'),
            'buttons' => array('cart_add', 'wishlist_add', 'compare_remove')
        );

        $products = $this->getProducts($conditions, $options);
        return $this->reindexProductsCompare($products);
    }

    /**
     * Returns an array of products indexed by a class ID
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
     * Sets titles on the select to compare page
     */
    protected function setTitleSelectCompare()
    {
        $this->setTitle($this->text('Comparison'));
    }

    /**
     * Sets breadcrumbs on the select to compare page
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
     * Render and output the select to compare page
     */
    protected function outputSelectCompare()
    {
        $this->output('compare/select');
    }

    /**
     * Displays the product comparison page
     * @param string $ids
     */
    public function compareCompare($ids)
    {
        $this->setProductCompare($ids);
        $this->controlAccessCompareCompare();

        $this->setTitleCompareCompare();
        $this->setBreadcrumbCompareCompare();

        $this->setData('products', $this->data_products);
        $this->setData('fields', $this->getFieldsCompare($this->data_products));

        $this->outputCompareCompare();
    }

    /**
     * Set an array of product IDs to compare
     * @param string $string
     */
    protected function setProductCompare($string)
    {
        $ids = array_filter(array_map('trim', explode(',', $string)), 'ctype_digit');
        $products = $this->getProductsCompare($ids);
        $this->data_products = $this->prepareProductsCompare($products);
    }

    /**
     * Controls access to the product comparison page
     */
    protected function controlAccessCompareCompare()
    {
        if (count($this->data_products) < 2) {
            $this->redirect('compare');
        }
    }

    /**
     * Returns an array of products to compare
     * @param array $ids
     * @return array
     */
    protected function getProductsCompare($ids)
    {
        $options = array(
            'buttons' => array(
                'cart_add',
                'wishlist_add',
                'compare_remove'
            )
        );

        $conditions = array('product_id' => $ids);
        return $this->getProducts($conditions, $options);
    }

    /**
     * Prepare an array of products to be compared
     * @param array $products
     * @return array
     */
    protected function prepareProductsCompare(array $products)
    {
        foreach ($products as $product_id => &$product) {
            $product['field'] = $this->product_field->getList($product_id);
            $this->setProductFieldsTrait($product, $this->product_class, $this);
        }
        return $products;
    }

    /**
     * Returns an array of all fields for the given products
     * @param array $products
     * @return array
     */
    protected function getFieldsCompare(array $products)
    {
        $labels = array();
        foreach ($products as $product) {
            if (empty($product['field_value_labels'])) {
                continue;
            }
            foreach ($product['field_value_labels'] as $type => $fields) {
                foreach (array_keys($fields) as $field_id) {
                    $labels[$type][$field_id] = $product['fields'][$type][$field_id]['title'];
                }
            }
        }

        return $labels;
    }

    /**
     * Sets titles on the product comparison page
     */
    protected function setTitleCompareCompare()
    {
        $this->setTitle($this->text('Comparison'));
    }

    /**
     * Sets breadcrumbs on the product comparison page
     */
    protected function setBreadcrumbCompareCompare()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home'));

        $breadcrumbs[] = array(
            'url' => $this->url('compare'),
            'text' => $this->text('Select products'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the product comparison page
     */
    protected function outputCompareCompare()
    {
        $this->output('compare/compare');
    }

}
