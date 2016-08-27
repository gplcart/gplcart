<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\Search as ModelsSearch;
use core\models\CategoryGroup as ModelsCategoryGroup;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to product catalog
 */
class Category extends FrontendController
{

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * Constructor
     * @param ModelsSearch $search
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(ModelsSearch $search,
            ModelsCategoryGroup $category_group)
    {
        parent::__construct();

        $this->search = $search;
        $this->category_group = $category_group;
    }

    /**
     * Displays the category page
     * @param integer $category_id
     */
    public function category($category_id)
    {
        $category = $this->get($category_id);

        $view = $this->config->module($this->theme, 'catalog_view', 'grid');
        $sort = $this->config->module($this->theme, 'catalog_sort', 'price');
        $order = $this->config->module($this->theme, 'catalog_order', 'asc');

        $default = array('sort' => $sort, 'order' => $order, 'view' => $view);

        $query = $this->getFilterQuery($default);
        $total = $this->getTotalProducts($category_id, $query);
        $limit = $this->setPager($total, $query, $this->config->module($this->theme, 'catalog_limit', 20));
        $tree = $this->getTree($category['category_group_id']);

        $products = $this->getProducts($limit, $query, $category_id);

        $this->data['category'] = $category;
        $this->data['products'] = $this->getRenderedProducts($products);
        $this->data['images'] = $this->getRenderedImages($category);
        $this->data['children'] = $this->getRenderedChildren($category_id, $tree);
        $this->data['navbar'] = $this->getRenderedNavbar(count($products), $total, $query);

        $this->setCategoryMenu($tree);

        $this->setMetaCategory($category);
        $this->setTitleCategory($category);
        $this->setBreadcrumbCategory($category);
        $this->outputCategory();
    }

    /**
     * Modifies a product array before rendering
     * @param array $products
     * @param array $query
     * @return array
     */
    public function prepareProducts(array $products, array $query)
    {
        if (empty($products)) {
            return array();
        }

        $user_id = $this->cart->uid();
        $product_ids = array_keys($products);
        $pricerules = $this->store->config('catalog_pricerule');

        $view = in_array($query['view'], array('list', 'grid')) ? $query['view'] : 'grid';
        $imestylestyle = $this->config->module($this->theme, "image_style_product_$view", 3);

        foreach ($products as $product_id => &$product) {
            $product['in_comparison'] = $this->product->isCompared($product_id);
            $product['thumb'] = $this->image->getThumb($product_id, $imestylestyle, 'product_id', $product_ids);
            $product['url'] = $product['alias'] ? $this->url($product['alias']) : $this->url("product/$product_id");
            $product['in_wishlist'] = $this->wishlist->exists($product_id, array('user_id' => $user_id));

            if (!empty($pricerules)) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
            $product['rendered'] = $this->render("product/item/$view", array(
                'product' => $product,
                'buttons' => array('cart_add', 'wishlist_add', 'compare_add')));
        }

        return $products;
    }

    /**
     * Sets sidebar menu
     * @param array $tree
     */
    protected function setCategoryMenu(array $tree)
    {
        $this->addRegionItem('region_left', array('category/block/menu', array('tree' => $tree)));
    }

    /**
     * Sets titles on the category page
     * @param array $category
     */
    protected function setTitleCategory(array $category)
    {
        $metatitle = $category['meta_title'];

        if (empty($metatitle)) {
            $metatitle = $category['title'];
        }

        $this->setTitle($metatitle, false);
        $this->setPageTitle($category['title']);
    }

    /**
     * Sets breadcrumbs on the category page
     * @param array $category
     */
    protected function setBreadcrumbCategory(array $category)
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
        $this->setBreadcrumb(array('text' => $category['title']));
    }

    /**
     * Sets metatags on the category page
     * @param array $category
     */
    protected function setMetaCategory(array $category)
    {
        $meta_description = $category['meta_description'];

        if (empty($meta_description)) {
            $meta_description = $this->truncate($category['description_1'], 160, '');
        }

        $this->setMeta(array('name' => 'description', 'content' => $meta_description));
        $this->setMeta(array('rel' => 'canonical', 'href' => $this->path));
    }

    /**
     * Renders the category page
     */
    protected function outputCategory()
    {
        $this->output('category/category');
    }

    /**
     * Returns a category
     * @param integer $category_id
     * @return array
     */
    protected function get($category_id)
    {
        $category = $this->category->get($category_id, $this->langcode);

        if (empty($category['status'])) {
            $this->outputError(404);
        }

        if ($category['store_id'] != $this->store_id) {
            $this->outputError(404);
        }

        return $category;
    }

    /**
     * Returns ready-to-display category images
     * @param array $category
     * @return string
     */
    protected function getRenderedImages(array $category)
    {
        if (empty($category['images'])) {
            return '';
        }

        $imagestyle = $this->config->module($this->theme, 'image_style_category', 3);

        foreach ($category['images'] as &$image) {
            $image['thumb'] = $this->image->url($imagestyle, $image['path']);
        }

        return $this->render('category/images', array('category' => $category));
    }

    /**
     * Returns ready-to-display category children
     * @param integer $category_id
     * @param array $tree
     * @return string
     */
    protected function getRenderedChildren($category_id, array $tree)
    {
        $children = $this->category->getChildren($category_id, $tree);
        return $this->render('category/children', array('children' => $children));
    }

    /**
     * Returns ready-to-display category navbar
     * @param integer $quantity
     * @param integer $total
     * @param array $query
     * @return string
     */
    protected function getRenderedNavbar($quantity, $total, array $query)
    {
        $options = array(
            'total' => $total,
            'quantity' => $quantity,
            'view' => $query['view'],
            'sort' => "{$query['sort']}-{$query['order']}"
        );

        return $this->render('category/navbar', $options);
    }

    /**
     * Returns ready-to-display products
     * @param array $products
     * @return string
     */
    protected function getRenderedProducts(array $products)
    {
        return $this->render('product/list', array('products' => $products));
    }

    /**
     * Returns prepared category tree
     * @param integer $category_group_id
     * @return array
     */
    protected function getTree($category_group_id)
    {
        $options = array(
            'status' => 1,
            'category_group_id' => $category_group_id
        );

        return $this->prepareTree($this->category->getTree($options));
    }

    /**
     * Modifies a category tree before rendering
     * @param array $tree
     * @return array
     */
    protected function prepareTree(array $tree)
    {
        $category_ids = array_keys($tree);
        $imagestyle = $this->config->module($this->theme, 'image_style_category_child', 3);

        $prepared = array();
        foreach ($tree as $category_id => $item) {
            $url = $item['alias'] ? $item['alias'] : "category/{$item['category_id']}";
            $item['url'] = $url;
            $item['active'] = ($this->path === $url);
            $item['thumb'] = $this->image->getThumb($category_id, $imagestyle, 'category_id', $category_ids, false);
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
            $prepared[$category_id] = $item;
        }

        return $prepared;
    }

    /**
     * Returns an array of prepared products
     * @param integer $limit
     * @param array $query
     * @param integer $category_id
     * @return array
     */
    protected function getProducts($limit, array $query, $category_id)
    {
        $options = array(
            'status' => 1,
            'limit' => $limit,
            'store_id' => $this->store_id,
            'category_id' => $category_id,
                //'language' => $this->langcode
                ) + $query;

        $products = $this->product->getList($options);
        return $this->prepareProducts($products, $query);
    }

    /**
     * Returns total number of products for the category ID
     * @param integer $category_id
     * @param array $query
     * @return integer
     */
    protected function getTotalProducts($category_id, array $query)
    {
        $options = array(
            'count' => true,
            'category_id' => $category_id,
                //'language' => $this->langcode
        );

        return $this->product->getList($options + $query);
    }

}
