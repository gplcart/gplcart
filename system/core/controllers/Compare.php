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
     */
    public function __construct(ModelsProductClass $product_class,
            ModelsField $field, ModelsFieldValue $field_value)
    {
        parent::__construct();

        $this->field = $field;
        $this->field_value = $field_value;
        $this->product_class = $product_class;
    }

    /**
     * Displays the select to compare page
     */
    public function select()
    {
        $product_ids = $this->getProductIds();
        $this->data['products'] = $this->getProducts($product_ids);

        $this->setBlockCategoryMenu();
        $this->setBlockRecentProducts();

        $this->setTitleSelect();
        $this->setBreadcrumbSelect();
        $this->outputSelect();
    }

    /**
     * Displays the product compare page
     * @param string $compared
     */
    public function compare($compared)
    {
        $product_ids = $this->getProductIds($compared);
        $products = $this->getProducts($product_ids);

        if (!empty($products)) {
            $product_class_id = key($products);
            $products = $products[$product_class_id];
            $this->setProductFields($products);
        }

        $this->data['products'] = $products;
        $this->data['share'] = $this->render('common/share', array(
            'url' => $this->url(false, array(), true),
            'title' => $this->text('Comparison')));

        $this->setTitleCompare();
        $this->setBreadcrumbCompare();
        $this->outputCompare();
    }

    /**
     * Sets titles on the select compared products page
     */
    protected function setTitleSelect()
    {
        $this->setTitle($this->text('Comparison'));
    }

    /**
     * Sets breadcrumbs on the select compared products page
     */
    protected function setBreadcrumbSelect()
    {
        $this->setBreadcrumb(array('url' => $this->url('/'), 'text' => $this->text('Home')));
    }

    /**
     * Renders the select compared products page
     */
    protected function outputSelect()
    {
        $this->output('compare/select');
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
        $this->setBreadcrumb(array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')));

        $this->setBreadcrumb(array(
            'url' => $this->url('compare'),
            'text' => $this->text('All compared products')));
    }

    /**
     * Renders product compare templates
     */
    protected function outputCompare()
    {
        $this->output('compare/compare');
    }

    /**
     * Returns an array of product IDs to be compared
     * @param null|array $product_ids
     * @return array
     */
    protected function getProductIds($product_ids = null)
    {
        if (!isset($product_ids)) {
            return $this->product->getCompared();
        }

        return array_filter(array_map('trim', explode(',', urldecode($product_ids))), 'is_numeric');
    }

    /**
     * Returns an array of prepared products to be compared
     * @param array $product_ids
     * @return array
     */
    protected function getProducts(array $product_ids)
    {
        if (empty($product_ids)) {
            return array();
        }

        $results = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        // Reindex by product class
        $products = array();
        foreach ($this->prepareProducts($results) as $product_id => $product) {
            $products[$product['product_class_id']][$product_id] = $product;
        }

        return $products;
    }

    /**
     * Modifies an array of product before rendering
     * @param array $products
     * @return array
     */
    protected function prepareProducts(array $products)
    {
        $user_id = $this->cart->uid();
        $product_ids = array_keys($products);
        $pricerules = $this->store->config('catalog_pricerule');
        $view = $this->config->module($this->theme, 'compare_view', 'grid');
        $imagestyle = $this->config->module($this->theme, 'image_style_product_grid', 3);

        foreach ($products as $product_id => &$product) {
            if (empty($product['status'])) {
                continue;
            }

            if ((int) $product['store_id'] !== (int) $this->store->id()) {
                continue;
            }

            $product['url'] = $product['alias'] ? $this->url($product['alias']) : $this->url("product/$product_id");
            $product['thumb'] = $this->image->getThumb($product_id, $imagestyle, 'product_id', $product_ids);
            $product['in_wishlist'] = $this->wishlist->exists($product_id, array('user_id' => $user_id));

            if (!empty($pricerules)) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);

            $buttons = array('cart_add', 'wishlist_add');

            if ($this->product->isCompared($product_id)) {
                $buttons[] = 'compare_remove';
            }

            $product['rendered'] = $this->render("product/item/$view", array(
                'product' => $product,
                'target' => $this->url('compare', array(), true),
                'buttons' => $buttons));
        }

        return $products;
    }

    /**
     * Sets products field data
     * @param array $products
     */
    protected function setProductFields(array &$products)
    {
        $this->data['attribute_fields'] = array();
        $this->data['option_fields'] = array();

        foreach ($products as $product_id => &$product) {
            $product_fields = $this->product->getFields($product_id);

            foreach ($product_fields as $type => $items) {
                $fields = $this->field->getList(array('field_id' => array_keys($items)));
                $values = $this->field_value->getList(array('field_id' => array_keys($items)));

                foreach ($fields as $field_id => $field) {
                    $this->data["{$type}_fields"][$field_id] = $field['title'];
                    foreach ($items[$field_id] as $field_value_id) {
                        $product["{$type}_values"][$field_id][] = $values[$field_value_id]['title'];
                    }
                }
            }
        }
    }

    /**
     * Sets sidebar menu
     */
    protected function setBlockCategoryMenu()
    {
        $this->addRegionItem('region_left', array('category/block/menu', array(
                'tree' => $this->getCategoryTree())));
    }

    /**
     * Returns an array of categories
     * @return array
     */
    protected function getCategoryTree()
    {
        $options = array(
            'status' => 1,
            'store_id' => $this->store_id,
            'type' => 'catalog',
        );

        $tree = $this->category->getTree($options);
        return $this->prepareCategoryTree($tree);
    }

    /**
     * Modifies an array of categories before rendering
     * @param array $tree
     * @return array
     */
    protected function prepareCategoryTree(array $tree)
    {
        foreach ($tree as &$item) {
            $item['url'] = $item['alias'] ? $item['alias'] : "category/{$item['category_id']}";
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }

        return $tree;
    }

    /**
     * Adds recently viewed products block
     */
    protected function setBlockRecentProducts()
    {
        $this->addRegionItem('region_bottom', array(
            'product/block/recent', array(
                'products' => $this->getRecentProducts())));
    }

    /**
     * Returns an array of recently viewed products
     * @return array
     */
    protected function getRecentProducts()
    {
        $limit = $this->config('product_recent_limit', 12);
        $product_ids = $this->product->getViewed($limit);

        if (empty($product_ids)) {
            return array();
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));
        return $this->prepareProducts($products, array('view' => 'grid'));
    }

}
