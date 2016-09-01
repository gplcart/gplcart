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

    /**
     * Array of recently viewed products
     * @var array
     */
    protected $viewed = array();

    /**
     * Current user cart ID
     * @var integer|string
     */
    protected $cart_uid;

    /**
     * Cart content for the current user
     * @var array
     */
    protected $cart_content = array();

    /**
     * Wishlist content for the current user
     * @var array
     */
    protected $wishlist_content = array();

    /**
     * Comparison list content for the current user
     * @var array
     */
    protected $compare_content = array();

    /**
     * Catalog category tree for the current store
     * @var array
     */
    protected $category_tree = array();

    /**
     * Whether price rules enabled for the current store
     * @var boolean
     */
    protected $catalog_pricerules = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFrontendInstancies();
        $this->setFrontendProperties();

        $this->hook->fire('init.frontend', $this);
    }

    /**
     * Sets model instancies
     */
    protected function setFrontendInstancies()
    {
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
    }

    /**
     * Sets controller's properties
     */
    protected function setFrontendProperties()
    {
        if (!$this->url->isInstall()) {
            $this->viewed = $this->getViewed();
            $this->cart_uid = $this->cart->uid();
            $this->category_tree = $this->getCategories();
            $this->compare_content = $this->product->getCompared();
            $this->cart_content = $this->cart->getByUser($this->cart_uid, false);
            $this->catalog_pricerules = $this->store->config('catalog_pricerule');
            $this->wishlist_content = $this->wishlist->getList(array('user_id' => $this->cart_uid));
        }
    }

    /**
     * 
     * @return type
     */
    public function getHoneypot()
    {
        return $this->render('common/honeypot');
    }

    /**
     * Returns Share this widget
     * @param array $options
     * @return string
     */
    public function getShare(array $options = array())
    {
        $options += array(
            'title' => $this->getPageTitle(),
            'url' => $this->url(false, array(), true)
        );

        return $this->render('common/share', $options);
    }

    /**
     * 
     * @return type
     */
    protected function getViewed()
    {
        $limit = $this->config('product_recent_limit', 12);
        return $this->product->getViewed($limit);
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

        if (!isset($options['view']) || !in_array($options['view'], array('list', 'grid'))) {
            $options['view'] = 'grid';
        }

        $options['id_key'] = 'product_id';
        $options['ids'] = array_keys($products);
        $options['imagestyle'] = $this->setting("image_style_product_{$options['view']}", 3);

        foreach ($products as &$product) {
            $this->setItemThumb($product, $options);
            $this->setItemUrl($product, $options);
            $this->setItemProductPrice($product, $options);
            $this->setItemProductCompared($product, $options);
            $this->setItemProductWishlist($product, $options);
            $this->setItemProductRendered($product, $options);
        }

        return $products;
    }

    /**
     * Returns prepared category tree
     * @param array $options
     * @return array
     */
    protected function getCategories(array $options = array())
    {
        $options += array(
            'status' => 1,
            'type' => 'catalog',
            'prepare' => true,
            'store_id' => $this->store_id,
            'imagestyle' => $this->setting('image_style_category_child', 3)
        );

        $tree = $this->category->getTree($options);

        if (empty($options['prepare'])) {
            return $tree;
        }

        return $this->prepareCategories($tree, $options);
    }

    /**
     * Loads an array of products from an array of product IDs
     * @param array $conditions
     * @param array $options
     * @return array
     */
    protected function getProducts(array $conditions = array(),
            array $options = array())
    {
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        $options += array(
            'prepare' => true
        );

        $products = $this->product->getList($conditions);

        if (empty($options['prepare'])) {
            return $products;
        }

        return $this->prepareProducts($products, $options);
    }

    /**
     * Modifies an array of category tree before rendering
     * @param array $tree
     * @return array
     */
    protected function prepareCategories(array $tree, array $options = array())
    {
        if (empty($tree)) {
            return array();
        }

        $options['id_key'] = 'category_id';
        $options['ids'] = array_keys($tree);

        foreach ($tree as &$item) {
            $this->setItemThumb($item, $options);
            $this->setItemUrl($item, $options);
            $this->setItemActive($item, $options);
            $this->setItemIndentation($item, $options);
        }

        return $tree;
    }

    /**
     * Sets active flag to the item if its url mathes the current path
     * @param array $item
     * @param array $options
     */
    protected function setItemActive(array &$item, array $options)
    {
        $item['active'] = (isset($item['url']) && ($this->base . $this->path === $item['url']));
    }

    /**
     * Sets item indentation using its hierarchy depth
     * @param array $item
     * @param array $options
     */
    protected function setItemIndentation(array &$item, array $options)
    {
        $depth = isset($item['depth']) ? $item['depth'] : 0;
        $item['indentation'] = str_repeat('<span class="indentation"></span>', $depth);
    }

    /**
     * Sets image thumbnail
     * @param array $data
     * @param array $options
     * @return array
     */
    protected function setItemThumb(array &$data, array $options = array())
    {
        if (empty($data['images']) && isset($options['ids'])) {
            $data['thumb'] = $this->image->getThumb($data, $options);
            return $data; // Processing single item, exit
        }

        if (empty($data['images'])) {
            return $data;
        }

        foreach ($data['images'] as &$image) {
            $image['thumb'] = $this->image->url($options['imagestyle'], $image['path']);
        }

        return $data;
    }

    /**
     * Sets flag if the product already added to comparison
     * @param array $product
     * @param array $options
     */
    protected function setItemProductCompared(array &$product, array $options)
    {
        $product['in_comparison'] = $this->product->isCompared($product['product_id']);
    }

    /**
     * Sets flag if the product already added to wishlist
     * @param array $product
     * @param array $options
     */
    protected function setItemProductWishlist(array &$product, array $options)
    {
        $product_id = $product['product_id'];
        $arguments = array('user_id' => $this->cart_uid);
        $product['in_wishlist'] = $this->wishlist->exists($product_id, $arguments);
    }

    /**
     * Sets item URL considering its possible alias
     * @param array $data
     * @param array $options
     */
    protected function setItemUrl(array &$data, array $options)
    {
        $id = $data[$options['id_key']];
        $entityname = preg_replace('/_id$/', '', $options['id_key']);
        $data['url'] = empty($data['alias']) ? $this->url("$entityname/$id") : $this->url($data['alias']);
    }

    /**
     * Sets formatted product price considering price rules
     * @param array $product
     * @param array $options
     */
    protected function setItemProductPrice(array &$product, array $options)
    {
        if ($this->catalog_pricerules) {
            $calculated = $this->product->calculate($product, $this->store_id);
            $product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
    }

    /**
     * Sets to the item its rendered HTML
     * @param array $product
     * @param array $options
     */
    protected function setItemProductRendered(array &$product, array $options)
    {
        $options += array('buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'buttons' => $options['buttons']);

        $product['rendered'] = $this->render("product/item/{$options['view']}", $data);
    }

}
