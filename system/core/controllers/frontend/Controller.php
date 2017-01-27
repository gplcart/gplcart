<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\Container;
use gplcart\core\Controller as BaseController;

/**
 * Contents specific to the frontend methods
 */
class Controller extends BaseController
{

    use \gplcart\core\traits\ControllerCart,
        \gplcart\core\traits\ControllerCompare,
        \gplcart\core\traits\ControllerWishlist,
        \gplcart\core\traits\ControllerProduct,
        \gplcart\core\traits\ControllerItem,
        \gplcart\core\traits\ControllerImage;

    /**
     * Trigger model instance
     * @var \gplcart\core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * An array of fired triggers for the current context
     * @var array
     */
    protected $triggers = array();

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Compare model instance
     * @var \gplcart\core\models\Compare $compare
     */
    protected $compare;

    /**
     * Wishlist model instance
     * @var \gplcart\core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * Catalog category tree for the current store
     * @var array
     */
    protected $data_categories = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->setFrontendInstancies();
        $this->setFrontendProperties();
        $this->setFrontendMenu();

        $this->submitCartTrait($this, $this->cart, $this->request, $this->response);
        $this->submitCompareTrait($this, $this->compare, $this->request, $this->response);
        $this->submitWishlistTrait($this, $this->wishlist, $this->request, $this->response);

        $this->hook->fire('init.frontend', $this);

        $this->controlHttpStatus();
    }

    /**
     * Sets model instancies
     */
    protected function setFrontendInstancies()
    {
        $this->price = Container::get('gplcart\\core\\models\\Price');
        $this->image = Container::get('gplcart\\core\\models\\Image');
        $this->trigger = Container::get('gplcart\\core\\models\\Trigger');
        $this->product = Container::get('gplcart\\core\\models\\Product');
        $this->compare = Container::get('gplcart\\core\\models\\Compare');
        $this->wishlist = Container::get('gplcart\\core\\models\\Wishlist');
        $this->category = Container::get('gplcart\\core\\models\\Category');
        $this->collection_item = Container::get('gplcart\\core\\models\\CollectionItem');
    }

    /**
     * Sets controller's properties
     */
    protected function setFrontendProperties()
    {
        if (!$this->url->isInstall()) {
            $this->triggers = $this->getTriggers();
            $this->data_categories = $this->getCategories();
        }
    }

    /**
     * Returns an array of recently viewed products
     * @return array
     */
    public function viewed()
    {
        $limit = $this->config('product_recent_limit', 12);
        return $this->product->getViewed($limit);
    }

    /**
     * Returns an array of categories
     * @return array
     */
    public function categories()
    {
        return $this->data_categories;
    }

    /**
     * Returns an array of product IDs to compare
     * @return array|integer
     */
    public function compare($key = null)
    {
        $items = $this->compare->getList();

        if ($key == 'count') {
            return count($items);
        }
        return $items;
    }

    /**
     * Returns user wishlist
     * @return array|integer
     */
    public function wishlist($key = null)
    {
        $options = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        // Don't count in query, use the same arguments to avoid an extra query
        $result = (array) $this->wishlist->getList($options);

        if ($key == 'count') {
            return count($result);
        }

        return $result;
    }

    /**
     * 
     * @return type
     */
    protected function getTriggers()
    {
        $conditions = array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        return $this->trigger->getFired($conditions);
    }

    protected function setFrontendMenu()
    {
        $menu = $this->renderMenu(0, 'nav navbar-nav menu-top');
        $this->setRegion('region_top', $menu);
    }

    /**
     * Returns rendered honeypot input
     * @return string
     */
    public function renderHoneyPotField()
    {
        return $this->render('common/honeypot');
    }

    /**
     * Returns Share this widget
     * @param array $options
     * @return string
     */
    public function renderShareWidget(array $options = array())
    {
        $options += array(
            'title' => $this->ptitle(),
            'url' => $this->url('', array(), true)
        );

        return $this->render('common/share', $options);
    }

    /**
     * Returns a rendered menu
     * @return string
     */
    protected function renderMenu($max_depth = null,
            $class = 'list-unstyled menu')
    {
        if (empty($this->data_categories)) {
            return '';
        }

        $data = array(
            'menu_class' => $class,
            'menu_max_depth' => $max_depth,
            'tree' => $this->data_categories
        );

        return $this->render('category/blocks/menu', $data);
    }

    /**
     * Returns rendered cart preview
     * @return string
     */
    protected function renderCartPreview()
    {
        $cart = $this->cart();

        if (empty($cart['items'])) {
            return '';
        }

        $options = array(
            'cart' => $this->prepareCart($this->cart()),
            'limit' => $this->config('cart_preview_limit', 5)
        );

        return $this->render('cart/preview', $options);
    }

    /**
     * Prepares an array of cart items
     * @param array $cart
     * @return array
     */
    public function prepareCart(array $cart)
    {
        foreach ($cart['items'] as &$item) {

            $item['currency'] = $cart['currency'];
            $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);

            $this->setThumbCartTrait($this, $this->image, $item);
            $this->setProductPriceTrait($this, $this->price, $this->product, $item);
        }

        $cart['total_formatted'] = $this->price->format($cart['total'], $cart['currency']);
        return $cart;
    }

    /**
     * Returns rendered product collection
     * @param array $options
     * @return string
     */
    protected function renderCollectionProduct(array $options)
    {
        $options += array(
            'template_list' => 'collection/list/product'
        );

        $products = $this->getCollectionItems($options);

        if (empty($products)) {
            return '';
        }

        $item = reset($products);
        $title = $item['collection_item']['collection_title'];

        $prepared = $this->prepareProducts($products, $options);
        $data = array('products' => $prepared, 'title' => $title);

        return $this->render($options['template_list'], $data);
    }

    /**
     * Returns rendered page collection
     * @param array $options
     * @return string
     */
    protected function renderCollectionPage(array $options)
    {
        $options += array(
            'template_list' => 'collection/list/page',
            'template_item' => 'collection/item/page'
        );

        $pages = $this->getCollectionItems($options);

        if (empty($pages)) {
            return '';
        }

        $item = reset($pages);
        $title = $item['collection_item']['collection_title'];

        $prepared = $this->preparePages($pages, $options);
        $data = array('pages' => $prepared, 'title' => $title);

        return $this->render($options['template_list'], $data);
    }

    /**
     * Returns rendered file collection
     * @param array $options
     * @return string
     */
    protected function renderCollectionFile(array $options)
    {
        $options += array(
            'template_item' => 'collection/item/file',
            'imagestyle' => $this->settings('image_style_collection_banner', 7)
        );

        $files = $this->getCollectionItems($options);

        if (empty($files)) {
            return '';
        }

        foreach ($files as &$file) {

            $options['path'] = $file['path'];
            if (!empty($file['collection_item']['data']['url'])) {
                $url = $file['collection_item']['data']['url'];
                $file['url'] = $this->url($url, array(), $this->url->isAbsolute($url));
            }

            $this->setThumbTrait($this->image, $file, $options);
            $this->setItemRenderedTrait($this, $file, array('file' => $file), $options);
        }

        return $this->render('collection/list/file', array('files' => $files));
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

        $options += array(
            'id_key' => 'product_id',
            'ids' => array_keys($products),
            'template_item' => "product/item/{$options['view']}",
            'imagestyle' => $this->settings("image_style_product_{$options['view']}", 3)
        );

        foreach ($products as &$product) {

            $this->setItemUrlTrait($this, $product, $options);
            $this->setThumbTrait($this->image, $product, $options);
            $this->setItemRenderedProductTrait($this, $product, $options);
            $this->setProductPriceTrait($this, $this->price, $this->product, $product, $options);

            $product['in_wishlist'] = $this->isInWishlist($product['product_id']);
            $product['in_comparison'] = $this->isInComparison($product['product_id']);
        }

        return $products;
    }

    /**
     * 
     * @param type $product_id
     * @return type
     */
    public function isInWishlist($product_id)
    {
        $arguments = array(
            'product_id' => $product_id,
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        return (bool) $this->wishlist->exists($arguments);
    }

    /**
     * 
     * @param type $product_id
     * @return type
     */
    public function isInComparison($product_id)
    {
        return (bool) $this->compare->exists($product_id);
    }

    /**
     * Sets an additional data to an array of pages
     * @param array $pages
     * @param array $options
     * @return array
     */
    protected function preparePages(array $pages, array $options = array())
    {
        if (empty($pages)) {
            return array();
        }

        $options += array('id_key' => 'page_id', 'ids' => array_keys($pages));

        foreach ($pages as &$page) {
            $this->setItemUrlTrait($this, $page, $options);
            $this->setThumbTrait($this->image, $page, $options);
            $this->setItemRenderedTrait($this, $page, array('page' => $page), $options);
        }

        return $pages;
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
            'prepare' => true,
            'type' => 'catalog',
            'store_id' => $this->store_id,
            'imagestyle' => $this->settings('image_style_category_child', 3)
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
    protected function getProducts($conditions = array(), $options = array())
    {
        $options += array('prepare' => true);
        $conditions += array('status' => 1, 'store_id' => $this->store_id);

        if (isset($conditions['product_id']) && empty($conditions['product_id'])) {
            return array();
        }

        $products = (array) $this->product->getList($conditions);

        if (empty($products)) {
            return array();
        }

        if (empty($options['prepare'])) {
            return $products;
        }

        return $this->prepareProducts($products, $options);
    }

    /**
     * Modifies an array of category tree before rendering
     * @param array $categories
     * @param array $options
     * @return array
     */
    protected function prepareCategories($categories, $options = array())
    {
        if (empty($categories)) {
            return array();
        }

        $options['id_key'] = 'category_id';
        $options['ids'] = array_keys($categories);

        foreach ($categories as &$category) {

            $this->setItemUrlTrait($this, $category, $options);
            $this->setThumbTrait($this->image, $category, $options);

            $category['active'] = ($this->base . (string) $this->isCurrentPath($category['url'])) !== '';
            $category['indentation'] = str_repeat('<span class="indentation"></span>', $category['depth']);
        }

        return $categories;
    }

    /**
     * 
     * @param array $options
     * @return type
     */
    protected function getCollectionItems(array $options)
    {
        $options += array('status' => 1, 'store_id' => $this->store_id);
        return $this->collection_item->getItems($options);
    }

    /**
     * Sets meta tags on the entity page
     * @param array $data
     */
    protected function setMetaEntity(array $data)
    {
        if ($data['meta_title'] !== '') {
            $this->setTitle($data['meta_title'], false);
        }

        if ($data['meta_description'] !== '') {
            $this->setMeta(array('name' => 'description', 'content' => $data['meta_description']));
        }

        $this->setMeta(array('rel' => 'canonical', 'href' => $this->path));
    }

    /**
     * "Honey pot" submission protection
     */
    public function controlSpam()
    {
        if ($this->request->request('url', '') !== '') {
            $this->response->error403(false);
        }
    }

}
