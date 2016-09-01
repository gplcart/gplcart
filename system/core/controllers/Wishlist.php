<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\controllers\Controller as FrontendController;

class Wishlist extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the wishlist page
     */
    public function wishlist()
    {
        $this->data['products'] = $this->getRenderedProducts();

        $this->setBlockRecentProducts();
        $this->setBlockCategoryMenu();

        $this->setTitleWishlist();
        $this->setBreadcrumbWishlist();
        $this->outputWishlist();
    }

    /**
     * Sets titles on the wishlist page
     */
    protected function setTitleWishlist()
    {
        $this->setTitle($this->text('My wishlist'));
    }

    /**
     * Sets breadcrumbs on the wishlist page
     */
    protected function setBreadcrumbWishlist()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Renders the wishlist page templates
     */
    protected function outputWishlist()
    {
        $this->output('wishlist');
    }

    /**
     * Returns an array of wishlist items for the current user
     * @return array
     */
    protected function getWishlist()
    {
        $user_id = $this->cart->uid();
        $results = $this->wishlist->getList(array('user_id' => $user_id));

        // Reindex array
        $products = array();
        foreach ($results as $result) {
            $products[$result['product_id']] = $result;
        }

        return $products;
    }

    /**
     * Prepares an array of wishlist products before rendering
     * @param array $items
     * @return array
     */
    protected function prepareProducts(array $items)
    {
        $product_ids = array_keys($items);
        $pricerules = $this->store->config('catalog_pricerule');
        $view = $this->config->module($this->theme, 'wishlist_view', 'grid');
        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));
        $imagestyle = $this->config->module($this->theme, 'image_style_product_grid', 3);

        foreach ($products as $product_id => &$product) {
            if (empty($product['status'])) {
                continue;
            }

            if ($product['store_id'] != $this->store_id) {
                continue;
            }

            $product['url'] = $product['alias'] ? $this->url($product['alias']) : $this->url("product/$product_id");
            $product['thumb'] = $this->image->getThumb($product_id, $imagestyle, 'product_id', $product_ids);

            if (!empty($pricerules)) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
            $product['rendered'] = $this->render("product/item/$view", array(
                'product' => $product, 'buttons' => array(
                    'cart_add', 'wishlist_remove', 'compare_add')));
        }

        return $products;
    }

    /**
     * Returns ready-to-display wishlist items
     * @return string
     */
    protected function getRenderedProducts()
    {
        $products = $this->prepareProducts($this->getWishlist());
        return $this->render("product/list", array('products' => $products));
    }

    /**
     * Sets recently viewed products block
     */
    protected function setBlockRecentProducts()
    {
        $this->setRegion('region_bottom', array('product/block/recent', array(
                'products' => $this->getRecentProducts())));
    }

    /**
     * Sets sidebar menu block
     */
    protected function setBlockCategoryMenu()
    {
        $this->setRegion('region_left', array('category/block/menu', array(
                'tree' => $this->getTree())));
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

    /**
     * Returns prepared category tree
     * @return array
     */
    protected function getTree()
    {
        $options = array(
            'status' => 1,
            'type' => 'catalog',
            'store_id' => $this->store_id
        );

        $tree = $this->category->getTree($options);
        return $this->prepareTree($tree);
    }

    /**
     * Modifies a category tree before rendering
     * @param array $tree
     * @return array
     */
    protected function prepareTree(array $tree)
    {
        foreach ($tree as &$item) {
            $item['url'] = $item['alias'] ? $item['alias'] : "category/{$item['category_id']}";
            $item['indentation'] = str_repeat('<span class="indentation"></span>', $item['depth']);
        }

        return $tree;
    }

}
