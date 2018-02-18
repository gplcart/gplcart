<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\ProductClass;
use gplcart\core\models\ProductField;

/**
 * Handles incoming requests and outputs data related to product comparison
 */
class Compare extends Controller
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
     * An array of products to compare
     * @var array
     */
    protected $data_products = array();

    /**
     * Compare constructor.
     * @param ProductClass $product_class
     * @param ProductField $product_field
     */
    public function __construct(ProductClass $product_class, ProductField $product_field)
    {
        parent::__construct();

        $this->product_class = $product_class;
        $this->product_field = $product_field;
    }

    /**
     * Page callback
     * Displays the select to compare page
     */
    public function selectCompare()
    {
        $this->controlProductsCompare();
        $this->setTitleSelectCompare();
        $this->setBreadcrumbSelectCompare();

        $this->setData('products', $this->getProductsSelectCompare());
        $this->outputSelectCompare();
    }

    /**
     * Make sure that products saved in cookie are all valid and available to the user
     * If some products were removed, disabled or moved to another store they will be removes from cookie
     */
    protected function controlProductsCompare()
    {
        $options = array(
            'status' => 1,
            'store_id' => $this->store_id,
            'product_id' => $this->product_compare->getList()
        );

        if (!empty($options['product_id'])) {
            $existing = array_keys($this->product->getList($options));
            if (array_diff($options['product_id'], $existing)) {
                $this->product_compare->set($existing);
            }
        }
    }

    /**
     * Returns an array of products re-indexed by a class ID
     * @return array
     */
    protected function getProductsSelectCompare()
    {
        $conditions = array(
            'product_id' => $this->getProductComparison());

        $options = array(
            'view' => $this->configTheme('compare_view', 'grid'),
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
     * Page callback
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
        $this->data_products = $this->getProductsCompare($ids);
        $this->prepareProductsCompare($this->data_products);
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

        return $this->getProducts(array('product_id' => $ids), $options);
    }

    /**
     * Prepare an array of products to be compared
     * @param array $products
     */
    protected function prepareProductsCompare(array &$products)
    {
        foreach ($products as $product_id => &$product) {
            $product['field'] = $this->product_field->getList($product_id);
            $this->setItemProductFields($product, $this->image, $this->product_class);
        }
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
            if (!empty($product['field_value_labels'])) {
                foreach ($product['field_value_labels'] as $type => $fields) {
                    foreach (array_keys($fields) as $field_id) {
                        $labels[$type][$field_id] = $product['fields'][$type][$field_id]['title'];
                    }
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
