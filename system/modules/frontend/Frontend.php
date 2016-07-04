<?php

namespace modules\frontend;

use core\Config;
use core\models\Cart as modelsCart;
use core\models\Product as modelsProduct;
use core\models\Bookmark as modelsBookmark;
use core\models\Category as modelsCategory;
use core\models\Alias as modelsAlias;
use core\classes\Url as classesUrl;
use core\classes\Document as classesDocument;

class Frontend {

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

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
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param classesUrl $url
     * @param classesDocument $document
     * @param modelsCart $cart
     * @param modelsProduct $product
     * @param modelsBookmark $bookmark
     * @param modelsCategory $category
     * @param modelsAlias $alias
     * @param Config $config
     */
    public function __construct(classesUrl $url, classesDocument $document,
            modelsCart $cart, modelsProduct $product, modelsBookmark $bookmark,
            modelsCategory $category, modelsAlias $alias, Config $config) {

        $this->url = $url;
        $this->cart = $cart;
        $this->product = $product;
        $this->bookmark = $bookmark;
        $this->category = $category;
        $this->alias = $alias;
        $this->document = $document;
        $this->config = $config;

        if ($this->url->isFrontend()) {

            $this->addMeta();

            if (!$this->url->isInstall()) {
                $this->addJs();
                $this->addCss();
            }
        }
    }

    /**
     * Module info
     * @return array
     */
    public function info() {
        return array(
            'name' => 'Frontend theme',
            'description' => 'Frontend theme',
            'author' => 'IURII MAKUKH',
            'core' => '1.0',
            'type' => 'theme',
            'configure' => 'admin/module/frontend/settings',
            'settings' => $this->getDefaultSettings()
        );
    }

    /**
     * Injects a data to templates
     * @param array $data
     */
    public function hookData(&$data) {
        if ($this->url->isFrontend() && !$this->url->isInstall()) {
            $uid = $this->cart->uid();
            $data['cart'] = $this->cart->getByUser($uid);
            $data['wishlist'] = $this->bookmark->getList(array('user_id' => $uid, 'type' => 'product'));
            $data['compare'] = $this->product->getCompared();
            $data['megamenu'] = $this->getCatalogTree($data['current_store']);
        }
    }

    /**
     * Adds a new route for settings page
     * @param array $routes
     */
    public function hookRoute(&$routes) {
        $routes['admin/module/frontend/settings'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('modules\\frontend\\controllers\\Frontend', 'settings')
            )
        );
    }

    protected function getCatalogTree($store) {
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

    /**
     * Adds theme's javascripts
     */
    protected function addJs() {
        $this->document->js('system/modules/frontend/js/script.js', 'top');
        $this->document->js('files/assets/jquery/ui/jquery-ui.min.js', 'top');
        $this->document->js('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');
        $this->document->js('files/assets/jquery/match-height/dist/jquery.matchHeight-min.js', 'top');
        $this->document->js('files/assets/jquery/lightslider/dist/js/lightslider.min.js', 'top');
    }

    /**
     * Adds theme's styles
     */
    protected function addCss() {
        $this->document->css('files/assets/bootstrap/bootstrap/css/bootstrap.min.css');
        $this->document->css('files/assets/font-awesome/css/font-awesome.min.css');
        $this->document->css('files/assets/jquery/ui/jquery-ui.min.css');
        $this->document->css('files/assets/jquery/lightslider/dist/css/lightslider.min.css');
        $this->document->css('system/modules/frontend/css/style.css');
    }

    /**
     * Adds theme's meta tags
     */
    protected function addMeta() {
        $this->document->meta(array('charset' => 'utf-8'));
        $this->document->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $this->document->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $this->document->meta(array('name' => 'author', 'content' => 'GPL Cart'));
    }

    /**
     * Returns an array of default module settings
     * @return array
     */
    protected function getDefaultSettings() {
        return array(
            'catalog_limit' => 20,
            'catalog_front_limit' => 12,
            'catalog_front_sort' => 'price',
            'catalog_front_order' => 'asc',
            'catalog_sort' => 'price',
            'catalog_order' => 'asc',
            'catalog_view' => 'grid',
            'image_style_category' => 3,
            'image_style_category_child' => 3,
            'image_style_product' => 5,
            'image_style_product_extra' => 3,
            'image_style_product_grid' => 3,
            'image_style_product_list' => 3,
            'image_style_cart' => 3,
            'image_style_page_banner' => 7,
        );
    }

}
