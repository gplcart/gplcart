<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\models\Page;
use core\models\Price;
use core\models\Image;
use core\models\Product;

class Front extends Controller
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;
    
    /**
     * Page module instance
     * @var \core\models\Page $page
     */
    protected $page;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;
    
    /**
     * Constructor
     * @param Product $product
     * @param Page $page
     * @param Price $price
     * @param Image $image
     */
    public function __construct(Product $product, Page $page, Price $price, Image $image)
    {
        parent::__construct();

        $this->page = $page;
        $this->price = $price;
        $this->image = $image;
        $this->product = $product;
    }

    /**
     * Displays the store front page
     */
    public function front()
    {
        $this->setFrontPages();
        $this->setFrontProducts();
        
        //ddd($this->data['region_content']);

        $this->setTitleFront();
        $this->outputFront();
    }

    /**
     * Sets titles on the front page
     */
    protected function setTitleFront()
    {
        $this->setTitle($this->store->config('title'), false);
    }

    /**
     * Renders the fron page templates
     */
    protected function outputFront()
    {
        $this->output('front/front');
    }

    /**
     * Returns an array of products to be shown on the front page
     */
    protected function getFrontProducts()
    {
        $products = $this->product->getList(array(
            'front' => 1,
            'status' => 1,
            'store_id' => $this->store_id,
            'sort' => $this->config->module($this->theme, 'catalog_front_sort', 'price'),
            'order' => $this->config->module($this->theme, 'catalog_front_order', 'asc'),
            'limit' => array(0, $this->config->module($this->theme, 'catalog_front_limit', 12))
        ));

        return $products;
    }
    
    /**
     * Returns an array of pages to be shown on the front page
     */
    protected function getFrontPages()
    {
        $pages = $this->page->getList(array(
            'front' => 1,
            'status' => 1,
            'store_id' => $this->store_id,
            'limit' => array(0, $this->config->module($this->theme, 'page_front_limit', 12))
        ));

        return $pages;
    }

    /**
     * Modifies product array
     * @param array $products
     * @return array
     */
    protected function prepareProducts($products)
    {
        $product_ids = array_keys($products);
        $pricerules = $this->store->config('catalog_pricerule');
        $imagestyle = $this->config->module($this->theme, 'image_style_product_grid', 3);

        foreach ($products as $product_id => &$product) {
            $product['url'] = $product['alias'] ? $this->url($product['alias']) : $this->url("product/$product_id");
            $product['thumb'] = $this->image->getThumb($product_id, $imagestyle, 'product_id', $product_ids);

            if ($pricerules) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
        }

        return $products;
    }
    
    /**
     * Modifies pages array
     * @param array $pages
     * @return array
     */
    protected function preparePages($pages)
    {
        $page_ids = array_keys($pages);
        $imagestyle = $this->config->module($this->theme, 'image_style_page_banner', 7);

        foreach ($pages as $page_id => &$page) {
            $page['url'] = $page['alias'] ? $this->url($page['alias']) : $this->url("page/$page_id");
            $page['thumb'] = $this->image->getThumb($page_id, $imagestyle, 'page_id', $page_ids, false);
        }

        return $pages;
    }

    /**
     * Returns an array of rendered product items
     * @param array $products
     * @return array
     */
    protected function renderProducts($products)
    {
        $rendered = array();
        foreach ($this->prepareProducts($products) as $product) {
            $rendered[] = $this->render('category/item/grid', array('product' => $product));
        }

        return $rendered;
    }
    
    /**
     * Returns an array of rendered page items
     * @param array $pages
     * @return array
     */
    protected function renderPages($pages)
    {
        $rendered = array();
        foreach ($this->preparePages($pages) as $page) {
            $rendered[] = $this->render('page/block/page', array('page' => $page));
        }

        return $rendered;
    }

    /**
     * Sets rendered products on the front page
     */
    protected function setFrontProducts()
    {
        $products = $this->getFrontProducts();
        $this->addRegionItem('region_content', array('front/block/product', array(
            'products' => $this->renderProducts($products))));
    }
    
    /**
     * Sets rendered products on the front page
     */
    protected function setFrontPages()
    {
        $pages = $this->preparePages($this->getFrontPages());
        $this->addRegionItem('region_top', array('page/block/page', array(
            'pages' => $pages)));
    }
}
