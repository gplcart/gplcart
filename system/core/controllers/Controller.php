<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Container;
use core\Controller as BaseController;

/**
 * Contents specific to the frontend methods
 */
class Controller extends BaseController
{

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
     * Cart model instance
     * @var \core\models\Cart $cart
     */
    protected $cart;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Wishlist model instance
     * @var \core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Alias model instance instance
     * @var \core\models\Alias $alias
     */
    protected $alias;
    protected $cart_uid;
    protected $cart_content = array();
    protected $wishlist_content = array();
    protected $compare_content = array();
    protected $category_tree = array();
    protected $catalog_pricerules = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        /* @var $price \core\models\Price */
        $this->price = Container::instance('core\\models\\Price');

        /* @var $image \core\models\Image */
        $this->image = Container::instance('core\\models\\Image');

        /* @var $cart \core\models\Cart */
        $this->cart = Container::instance('core\\models\\Cart');

        /* @var $alias \core\models\Alias */
        $this->alias = Container::instance('core\\models\\Alias');

        /* @var $product \core\models\Product */
        $this->product = Container::instance('core\\models\\Product');

        /* @var $wishlist \core\models\Wishlist */
        $this->wishlist = Container::instance('core\\models\\Wishlist');

        /* @var $category \core\models\Category */
        $this->category = Container::instance('core\\models\\Category');

        if (!$this->url->isInstall()) {
            $this->cart_uid = $this->cart->uid();
            $this->cart_content = $this->cart->getByUser($this->cart_uid, false);
            $this->wishlist_content = $this->wishlist->getList(array('user_id' => $this->cart_uid));
            $this->compare_content = $this->product->getCompared();
            $this->category_tree = $this->getCategoryTree($this->current_store);


            $this->catalog_pricerules = $this->store->config('catalog_pricerule');
        }


        $this->hook->fire('init.frontend', $this);
    }

    protected function getCategoryTree($store)
    {
        $tree = $this->category->getTree(array('store_id' => $store['store_id'], 'type' => 'catalog', 'status' => 1));

        $category_aliases = $this->alias->getMultiple('category_id', array_keys($tree));

        foreach ($tree as &$item) {
            $path = "category/{$item['category_id']}";

            if (!empty($category_aliases[$item['category_id']])) {
                $path = $category_aliases[$item['category_id']];
            }

            $item['url'] = $this->url->get($path);

            if ($this->url->path() === $path) {
                $item['active'] = true;
            }
        }

        return $tree;
    }

    public function getHoneypot()
    {
        return $this->render('common/honeypot');
    }

    /**
     * Sets an additional data to an array of products
     * @param array $products
     * @param array $options
     * @return array
     */
    protected function prepareProducts(array $products, array $options = array())
    {
        if (empty($products)) {
            return array();
        }

        $options['product_ids'] = array_keys($products);
        if (!isset($options['view']) || !in_array($options['view'], array('list', 'grid'))) {
            $options['view'] = 'grid';
        }

        foreach ($products as &$product) {
            $this->setProductThumb($product, $options);
            $this->setProductAlias($product, $options);
            $this->setProductPrice($product, $options);
            $this->setProductCompare($product, $options);
            $this->setProductWishlist($product, $options);
            $this->setProductRendered($product, $options);
        }
        
        return $products;
    }

    /**
     * Sets product thumb
     * @param array $product
     * @param array $options
     */
    protected function setProductItemThumb(array &$product, array $options)
    {
        $preset = $this->setting("image_style_product_{$options['view']}", 3);
        $product['thumb'] = $this->image->getThumb($product['product_id'], $preset, 'product_id', $product['product_ids']);
    }

    /**
     * Sets flag if the product already in comparison
     * @param array $product
     * @param array $options
     */
    protected function setProductItemCompare(array &$product, array $options)
    {
        $product['in_comparison'] = $this->product->isCompared($product['product_id']);
    }

    /**
     * Sets flag if the product already in wishlist
     * @param array $product
     * @param array $options
     */
    protected function setProductItemWishlist(array &$product, array $options)
    {
        $product_id = $product['product_id'];
        $arguments = array('user_id' => $this->cart_uid);
        $product['in_wishlist'] = $this->wishlist->exists($product_id, $arguments);
    }

    protected function setProductItemAlias(array &$product, array $options)
    {
        $product_id = $product['product_id'];
        $product['url'] = empty($product['alias']) ? $this->url($product['alias']) : $this->url("product/$product_id");
    }

    /**
     * Sets product price
     * @param array $product
     * @param array $options
     */
    protected function setProductItemPrice(array &$product, array $options)
    {
        if ($this->catalog_pricerules) {
            $calculated = $this->product->calculate($product, $this->store_id);
            $product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
    }

    /**
     * Renders product item
     * @param array $product
     * @param array $options
     */
    protected function setProductItemRendered(array &$product, array $options)
    {
        $data = array(
            'product' => $product,
            'buttons' => array('cart_add', 'wishlist_add', 'compare_add'));

        $product['rendered'] = $this->render("product/item/{$options['view']}", $data);
    }

}
