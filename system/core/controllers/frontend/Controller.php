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
            \gplcart\core\traits\ControllerWishlist;

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
     * Array of recently viewed products
     * @var array
     */
    protected $data_viewed = array();

    /**
     * Whether price rules enabled for the current store
     * @var boolean
     */
    protected $catalog_pricerules = false;

    /**
     * Comparison list content for the current user
     * @var array
     */
    protected $data_compare = array();

    /**
     * Array of wishlist items
     * @var array
     */
    protected $data_wishlist = array();

    /**
     * Catalog category tree for the current store
     * @var array
     */
    protected $data_category_tree = array();

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
        if ($this->url->isInstall()) {
            return null;
        }

        $this->data_viewed = $this->getViewed();
        $this->data_category_tree = $this->getCategories();
        $this->data_compare = $this->compare->get();
        $this->catalog_pricerules = $this->store->config('catalog_pricerule');
        $this->triggers = $this->trigger->getFired(array('store_id' => $this->store_id, 'status' => 1));

        $options = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        // Don't count, use the same arguments to avoid an extra query
        // see setItemProductWishlist method
        $this->data_wishlist = (array) $this->wishlist->getList($options);

        $this->data['wishlist_quantity'] = count($this->data_wishlist);
        $this->data['compare_content'] = $this->data_compare;
        return null;
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
        if (empty($this->data_category_tree)) {
            return '';
        }

        $data = array(
            'menu_class' => $class,
            'tree' => $this->data_category_tree,
            'menu_max_depth' => $max_depth
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
        
        if(empty($cart['items'])){
            return '';
        }
        
        $options = array(
            'cart' => $this->prepareCart($this->cart()),
            'limit' => $this->config('cart_preview_limit', 5)
        );

        return $this->render('cart/preview', $options);
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
            'imagestyle' => $this->settings('image_style_collection_banner', 7),
            'template_item' => 'collection/item/file'
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

            $this->setItemThumb($file, $options);
            $this->setItemRendered($file, array('file' => $file), $options);
        }

        return $this->render('collection/list/file', array('files' => $files));
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

        $options += array(
            'id_key' => 'product_id',
            'ids' => array_keys($products),
            'template_item' => "product/item/{$options['view']}",
            'imagestyle' => $this->settings("image_style_product_{$options['view']}", 3)
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $options);
            $this->setItemUrl($product, $options);
            $this->setItemPrice($product, $options);
            $this->setItemCompared($product, $options);
            $this->setItemWishlist($product, $options);
            $this->setItemRenderedProduct($product, $options);
        }

        return $products;
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

        $options += array(
            'id_key' => 'page_id',
            'ids' => array_keys($pages),
        );

        foreach ($pages as &$page) {
            $this->setItemThumb($page, $options);
            $this->setItemUrl($page, $options);
            $this->setItemRendered($page, array('page' => $page), $options);
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
            'type' => 'catalog',
            'prepare' => true,
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
        $conditions += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

        $options += array(
            'prepare' => true
        );
        
        if(isset($conditions['product_id']) && empty($conditions['product_id'])){
            return array(); // Prevent loading all available products
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
            $this->setItemIndentation($item);
        }

        return $tree;
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

            $this->setItemTotal($item);
            $this->setItemPrice($item);
            $this->setItemThumbCart($item);
        }

        $this->setItemTotal($cart);
        return $cart;
    }

    /**
     * Sets active flag to the item if its url mathes the current path
     * @param array $item
     * @param array $options
     */
    protected function setItemActive(array &$item, array $options)
    {
        $item['active'] = (isset($item['url']) && ($this->base . $this->isCurrentPath($item['url'])));
    }

    /**
     * Sets item indentation using hierarchy depth
     * @param array $item
     */
    protected function setItemIndentation(array &$item)
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
        if (empty($options['imagestyle'])) {
            return $data;
        }

        if (!empty($options['path'])) {
            $data['thumb'] = $this->image->url($options['imagestyle'], $options['path']);
            return $data;
        }

        if (empty($data['images'])) {
            $data['thumb'] = $this->image->getThumb($data, $options);
            return $data; // Processing single item, exit 
        }

        foreach ($data['images'] as &$image) {
            $image['thumb'] = $this->image->url($options['imagestyle'], $image['path']);
            $image['url'] = $this->image->urlFromPath($image['path']);
        }

        return $data;
    }

    /**
     * Sets product image thumbnail to the cart item
     * @param array $item
     */
    protected function setItemThumbCart(array &$item)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->settings('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['file_id'])//
                && !empty($item['product']['images'][$item['product']['file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['file_id']]['path'];
        }

        $this->setItemThumb($item, $options);
    }

    /**
     * Sets flag if the product already added to comparison
     * @param array $product
     * @param array $options
     */
    protected function setItemCompared(array &$product, array $options)
    {
        $product['in_comparison'] = $this->compare->exists($product['product_id']);
    }

    /**
     * Sets flag if the product already added to wishlist
     * @param array $product
     * @param array $options
     */
    protected function setItemWishlist(array &$product, array $options)
    {
        $arguments = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id,
            'product_id' => $product['product_id']
        );

        $product['in_wishlist'] = $this->wishlist->exists($arguments);
    }

    /**
     * Sets a URL to the item considering its possible alias
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
     * Sets the formatted product price considering price rules
     * @param array $product
     * @param array $options
     */
    protected function setItemPrice(array &$product, array $options = array())
    {
        $options += array('calculate' => true);

        if ($this->catalog_pricerules && !empty($options['calculate'])) {
            //$calculated = $this->product->calculate($product, $this->store_id);
            //$product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
    }

    /**
     * Sets formatted total to the item
     * @param array $item
     */
    protected function setItemTotal(array &$item)
    {
        $item['total_formatted'] = $this->price->format($item['total'], $item['currency']);
    }

    /**
     * Sets to the item its rendered HTML
     * @param array $product
     * @param array $options
     */
    protected function setItemRenderedProduct(array &$product, array $options)
    {
        $options += array(
            'buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'token' => $this->token,
            'buttons' => $options['buttons']
        );

        $this->setItemRendered($product, $data, $options);
    }

    /**
     * Adds to the item its rendered HTML
     * @param array $item
     * @param array $data
     * @param array $options
     */
    protected function setItemRendered(array &$item, array $data, array $options)
    {
        $item['rendered'] = $this->render($options['template_item'], $data);
    }

    protected function getCollectionItems(array $options)
    {
        $options += array(
            'status' => 1,
            'store_id' => $this->store_id
        );

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
     * @param string $type
     * @return null
     */
    public function controlSpam($type)
    {
        $honeypot = $this->request->request('url', '');

        if ($honeypot === '') {
            return null;
        }

        $ip = $this->request->ip();

        $message = array(
            'ip' => $ip,
            'message' => 'Spam submit from IP %address',
            'variables' => array('%address' => $ip)
        );

        $this->logger->log($type, $message, 'warning');
        $this->response->error403(false);
    }

}
