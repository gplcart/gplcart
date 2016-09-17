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
     * Trigger model instance
     * @var \core\models\Trigger $trigger
     */
    protected $trigger;

    /**
     * An array of fired triggers for the current context
     * @var type 
     */
    protected $triggers = array();

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
     * Compare model instance
     * @var \core\models\Compare $compare
     */
    protected $compare;

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
        $this->setFrontendSubmits();

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

        /* @var $product \core\models\Product */
        $this->product = Container::instance('core\\models\\Product');
        
        /* @var $compare \core\models\Compare */
        $this->compare = Container::instance('core\\models\\Compare');

        /* @var $wishlist \core\models\Wishlist */
        $this->wishlist = Container::instance('core\\models\\Wishlist');

        /* @var $category \core\models\Category */
        $this->category = Container::instance('core\\models\\Category');

        /* @var $trigger \core\models\Trigger */
        $this->trigger = Container::instance('core\\models\\Trigger');
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
            $this->compare_content = $this->compare->get();
            $this->cart_content = $this->cart->getByUser($this->cart_uid, $this->store_id);
            $this->catalog_pricerules = $this->store->config('catalog_pricerule');
            $this->wishlist_content = $this->wishlist->getList(array('user_id' => $this->cart_uid));

            $this->triggers = $this->trigger->getFired(array('store_id' => $this->store_id, 'status' => 1));

            //d($this->triggers);
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

        $options += array(
            'id_key' => 'product_id',
            'ids' => array_keys($products),
            'template' => "product/item/{$options['view']}",
            'imagestyle' => $this->setting("image_style_product_{$options['view']}", 3)
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $options);
            $this->setItemUrl($product, $options);
            $this->setItemPrice($product, $options);
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
            $this->setItemCartThumb($item);
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
        if (isset($options['path'])) {
            $data['thumb'] = $this->image->url($options['imagestyle'], $options['path']);
            return $data;
        }

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
     * Sets product image thumbnail to the cart item
     * @param array $item
     */
    protected function setItemCartThumb(array &$item)
    {
        $options = array(
            'path' => '',
            'imagestyle' => $this->setting('image_style_cart', 3)
        );

        if (empty($item['product']['combination_id']) && !empty($item['product']['images'])) {
            $imagefile = reset($item['product']['images']);
            $options['path'] = $imagefile['path'];
        }

        if (!empty($item['product']['option_file_id']) && !empty($item['product']['images'][$item['product']['option_file_id']]['path'])) {
            $options['path'] = $item['product']['images'][$item['product']['option_file_id']]['path'];
        }

        $this->setItemThumb($item, $options);
    }

    /**
     * Sets flag if the product already added to comparison
     * @param array $product
     * @param array $options
     */
    protected function setItemProductCompared(array &$product, array $options)
    {
        $product['in_comparison'] = $this->compare->exists($product['product_id']);
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
        if ($this->catalog_pricerules) {
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
    protected function setItemProductRendered(array &$product, array $options)
    {
        $options += array('buttons' => array(
                'cart_add', 'wishlist_add', 'compare_add'));

        $data = array(
            'product' => $product,
            'token' => $this->token,
            'buttons' => $options['buttons']);

        $product['rendered'] = $this->render($options['template'], $data);
    }

    ###########################################################################
    ################################# Submits #################################
    ###########################################################################

    /**
     * Sets all submit listeners
     */
    protected function setFrontendSubmits()
    {
        $this->submitCart();
        $this->submitCompare();
        $this->submitWishlist();
    }
    
    protected function submitCompare(){
        if (!$this->isPosted('add_to_compare')) {
            return; // No "Add to compare" clicked
        }
        
        /*
        $this->setSubmitted('product');
        $this->validateAddToCompare();

        if ($this->hasErrors(null, false)) {
            return $this->completeSubmit();
        }

        $product_id = $this->getSubmitted();
        
        $added = $this->product->addToCompare($product_id);

        if (empty($added)) {
            return $this->completeSubmit();
        }
        
        $result = array(
            'severity' => 'success',
            'message' => $this->text('Product has been added to <a href="!href">comparison</a>', array(
                '!href' => $this->url('compare')))
        );

        $this->completeSubmit($result);
         * */
    }

    /**
     * Adds a product to the cart
     */
    protected function submitCart()
    {
        if (!$this->isPosted('add_to_cart')) {
            return; // No "Add to cart" clicked
        }

        $this->setSubmitted('product');
        $this->validateAddToCart();

        if ($this->hasErrors(null, false)) {
            return $this->completeSubmit();
        }

        $submitted = $this->getSubmitted();
        $product = $this->getSubmitted('product');

        $result = $this->cart->addProduct($product, $submitted);
        $this->completeSubmit($result);
    }

    /**
     * Adds a product to the wishlist
     */
    protected function submitWishlist()
    {
        if (!$this->isPosted('add_to_wishlist')) {
            return; // No "Add to wishlist" clicked
        }

        $this->setSubmitted('product');
        $this->validateAddToWishlist();

        if ($this->hasErrors(null, false)) {
            return $this->completeSubmit();
        }

        $submitted = $this->getSubmitted();
        $result = $this->wishlist->addProduct($submitted);

        return $this->completeSubmit($result);
    }

    /**
     * Finishes a submitted action.
     * For non-AJAX requests - redirects the user with a message
     * For AJAX requests - outputs JSON string with results such as message, redirect path...
     * @param array $data
     * @return mixed
     */
    protected function completeSubmit(array $data = array())
    {
        $errors = $this->getError();
        $message = empty($errors) ? $this->text('An error occurred') : end($errors);

        $data += array(
            'redirect' => '',
            'message' => $message,
            'severity' => 'danger'
        );

        if ($this->request->isAjax()) {
            $this->outputAjaxResponse($data);
        }

        $this->redirect($data['redirect'], $data['message'], $data['severity']);
    }

    /**
     * Outputs JSON with warious data
     */
    protected function outputAjaxResponse(array $data)
    {
        $response = $data;
        if ($this->isPosted('add_to_cart') && $data['severity'] == 'success') {
            $response = $this->getCartPreview();
        }

        $this->response->json($response);
    }

    /**
     * Returns an array containing rendered cart preview
     * @param array $options
     * @return array
     */
    protected function getCartPreview(array $options = array())
    {
        $cart = $this->cart->getByUser($this->cart_uid, $this->store_id);

        $options += array(
            'cart' => $this->prepareCart($cart),
            'limit' => $this->config('cart_preview_limit', 5)
        );

        $html = $this->render('cart/preview', $options);
        return array('quantity' => $cart['quantity'], 'rendered' => $html);
    }

    /**
     * Validates Add to cart action
     */
    protected function validateAddToCart()
    {
        $quantity = $this->getSubmitted('quantity');

        if (empty($quantity)) {
            $this->setSubmitted('quantity', 1);
        }

        $this->setSubmitted('user_id', $this->cart_uid);

        // Check if the product exists
        // and set its loaded array to the data array
        // see the argument for $this->setValidators()
        $this->addValidator('product_id', array(
            'product_exists' => array(
                'set_data' => true,
                'status' => true,
                'required' => true
            )
        ));

        $this->addValidator('quantity', array(
            'required' => array(),
            'numeric' => array(),
            'length' => array('max' => 2)
        ));

        // set_submitted => true - set validation results
        // to the submitted values for further usage
        $this->addValidator('options', array(
            'cart_options' => array('set_submitted' => true)
        ));

        $this->addValidator('limit', array(
            'cart_limits' => array('control_errors' => true)
        ));

        $this->setValidators();

        $product = $this->getValidatorResult('product_id');
        $this->setSubmitted('product', $product);
    }

    /**
     * Validates "Add to wishlist" action
     */
    protected function validateAddToWishlist()
    {
        $this->setSubmitted('user_id', $this->cart_uid);

        $this->addValidator('product_id', array(
            'product_exists' => array(
                'status' => true,
                'required' => true
            )
        ));

        $this->setValidators();
    }

}
