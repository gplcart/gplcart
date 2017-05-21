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

    use \gplcart\core\traits\ControllerProduct;

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
     * An array of product ID to compare
     * @var array
     */
    protected $data_compare = array();

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

        $this->setRegionContentSelectCompare();
        $this->outputSelectCompare();
    }

    /**
     * Sets the content region on the select to compare page
     */
    protected function setRegionContentSelectCompare()
    {
        $products = $this->getProductsSelectCompare();
        $html = $this->render('compare/select', array('products' => $products));
        $this->setRegion('content', $html);
    }

    /**
     * Returns an array of products reindexed by a class ID
     * @return array
     */
    protected function getProductsSelectCompare()
    {
        $conditions = array(
            'product_id' => $this->compare());

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
        $this->output();
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

        $this->setRegionContentCompareCompare();
        $this->outputCompareCompare();
    }

    /**
     * Set an array of product IDs to compare
     * @param string $ids
     */
    protected function setProductCompare($ids)
    {
        $this->data_compare = array_filter(array_map('trim', explode(',', $ids)), 'ctype_digit');
    }

    /**
     * Controls access to the product comparison page
     */
    protected function controlAccessCompareCompare()
    {
        if (count($this->data_compare) < 2) {
            $this->redirect('compare');
        }
    }

    /**
     * Sets the content region on the product comparison page
     */
    protected function setRegionContentCompareCompare()
    {

        $products = $this->getProductsCompare();
        $prepared = $this->prepareProductsCompare($products);

        $data = array(
            'products' => $prepared,
            'field_labels' => $this->getFieldLabelsCompare($prepared)
        );

        $this->setRegion('content', $this->render('compare/compare', $data));
    }

    /**
     * Returns an array of products to compare
     * @return array
     */
    protected function getProductsCompare()
    {
        $options = array(
            'buttons' => array(
                'cart_add',
                'wishlist_add',
                'compare_remove'
            )
        );

        $conditions = array('product_id' => $this->data_compare);
        return $this->getProducts($conditions, $options);
    }

    /**
     * Prepare an array of products
     * @param array $products
     * @return array
     */
    protected function prepareProductsCompare(array $products)
    {
        foreach ($products as $product_id => &$product) {
            $product['field'] = $this->product_field->getList($product_id);
            $this->attachProductFieldsTrait($product, $this->product_class, $this);
        }
        return $products;
    }

    /**
     * Returns an array of all field labels for the given products
     * @param array $products
     * @return array
     */
    protected function getFieldLabelsCompare(array $products)
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
        $this->output();
    }

}
