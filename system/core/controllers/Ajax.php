<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\models\File as ModelsFile;
use core\models\State as ModelsState;
use core\models\Search as ModelsSearch;
use core\models\Rating as ModelsRating;
use core\models\Country as ModelsCountry;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to AJAX operations
 */
class Ajax extends FrontendController
{

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Search model instance
     * @var \core\models\Search $search
     */
    protected $search;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Rating model instance
     * @var \core\models\Rating $rating
     */
    protected $rating;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsSearch $search
     * @param ModelsFile $file
     * @param ModelsRating $rating
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsSearch $search, ModelsFile $file, ModelsRating $rating)
    {
        parent::__construct();

        $this->file = $file;
        $this->state = $state;
        $this->rating = $rating;
        $this->search = $search;
        $this->country = $country;
    }

    /**
     * Main ajax callback
     */
    public function getResponseAjax()
    {
        if (!$this->request->isAjax()) {
            exit; // Reject non-ajax requests
        }

        $action = (string) $this->request->post('action');

        if (empty($action)) {
            $this->response->json(array('error' => $this->text('Missing handler')));
        }

        // action = method name. Check if the method exists
        if (!method_exists($this, $action)) {
            $this->response->json(array('error' => $this->text('Missing handler')));
        }

        try {
            $response = $this->{$action}();
        } catch (\BadMethodCallException $exc) {
            $response = array('error' => $this->text('An error occurred'));
        }

        $this->response->json($response);
    }

    /**
     * Returns an array of products
     * @return array
     */
    public function getProductsAjax()
    {
        if (!$this->access('product')) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $status = $this->request->post('status', null);
        $term = (string) $this->request->post('term', '');
        $store_id = $this->request->post('store_id', null);
        $max = $this->config('admin_autocomplete_limit', 10);

        $options = array(
            'title' => $term,
            'status' => $status,
            'store_id' => $store_id,
            'limit' => array(0, $max)
        );

        $products = $this->product->getList($options);

        if (!empty($products)) {
            $stores = $this->store->getList();
        }

        $list = array();
        foreach ($products as $product) {
            $product['url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                        . "/product/{$product['product_id']}";
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
            $list[$product['product_id']] = $product;
        }

        return $list;
    }

    /**
     * Returns an array of users
     * @return array
     */
    public function getUsersAjax()
    {
        if (!$this->access('user')) {
            return array(
                'error' => $this->text('You are not permitted to perform this operation'));
        }

        $term = (string) $this->request->post('term', '');
        $store_id = $this->request->post('store_id', null);
        $max = $this->config('admin_autocomplete_limit', 10);

        $options = array(
            'email' => $term,
            'store_id' => $store_id,
            'limit' => array(0, $max));

        return $this->user->getList($options);
    }

    /**
     * Toggles product options
     * @return array
     */
    public function switchProductOptionsAjax()
    {

        $product_id = (int) $this->request->post('product_id');
        $field_value_ids = (array) $this->request->post('values');

        $response = array(
            'message' => '',
            'error' => false,
            'subscribe' => false,
            'cart_access' => false,
            'combination' => array(),
            'message_modal' => false
        );

        $product = $this->product->get($product_id);

        if (empty($product['status'])) {

            $response['error'] = true;
            $response['message'] = $this->text('Unavailable product');

            $this->hook->fire('switch.product.options', $field_value_ids, $product, $response);
            return $response;
        }

        if (empty($field_value_ids)) {

            $response['error'] = true;
            $response['message'] = $this->text('No option selected');

            $this->hook->fire('switch.product.options', $field_value_ids, $product, $response);
            return $response;
        }

        $field_value_ids = array_values($field_value_ids);
        $combination_id = $this->product->getCombinationId($field_value_ids, $product_id);

        if (empty($product['combination'][$combination_id])) {

            $response['error'] = true;
            $response['message'] = $this->text('Invalid option combination');

            $this->hook->fire('switch.product.options', $field_value_ids, $product, $response);
            return $response;
        }

        $combination = $product['combination'][$combination_id];

        if (!empty($combination['price'])) {
            $combination['price'] = $this->price->format($combination['price'], $product['currency']);
        }

        if (!empty($combination['path'])) {
            $preset = $this->store->config('image_style_product');
            $combination['image'] = $this->image->url($preset, $combination['path']);
        }

        $response['combination'] = $combination;

        if (empty($combination['stock']) && $product['subtract']) {
            $response['subscribe'] = true;
            $response['message'] = $this->text('Out of stock');
        }

        $response['cart_access'] = true;

        $this->hook->fire('switch.product.options', $field_value_ids, $product, $response);
        return $response;
    }

    /**
     * Returns the cart preview for the current user
     * @return array
     */
    public function getCartPreviewAjax()
    {
        $cart = $this->cart->getByUser($this->cart_uid, $this->store_id);

        if (empty($cart['items'])) {
            return array();
        }

        $limit = $this->config('cart_preview_limit', 5);
        $content = $this->prepareCart($cart);

        $data = array('cart' => $content, 'limit' => $limit);
        return array('preview' => $this->render('cart/preview', $data));
    }

    /**
     * Returns an array of products based on certain conditions
     * @return array
     */
    public function searchProductsAjax()
    {
        $term = (string) $this->request->post('term');

        if (empty($term)) {
            return array();
        }

        $max = $this->config('autocomplete_limit', 10);

        $conditions = array(
            'status' => 1,
            'limit' => array(0, $max),
            'language' => $this->langcode,
            'store_id' => $this->store_id
        );

        $products = $this->search->search('product_id', $term, $conditions);

        if (empty($products)) {
            return array();
        }

        $options = array(
            'template' => 'search/suggestion',
            'imagestyle' => $this->setting('image_style_product_list', 3)
        );

        return $this->prepareProducts($products, $options);
    }

    /**
     * Returns an array of products for admin
     * @return array
     */
    public function adminSearchAjax()
    {
        $id = (string) $this->request->post('id');
        $term = (string) $this->request->post('term');

        if (empty($term) || empty($id)) {
            return array('error' => $this->text('An error occurred'));
        }

        $entityname = preg_replace('/_id$/', '', $id);

        if (!$this->access('admin') || !$this->access($entityname)) {
            return array('error' => $this->text('An error occurred'));
        }

        $preset = $this->config('admin_image_style', 2);
        $max = $this->config('admin_autocomplete_limit', 10);

        $options = array(
            'imagestyle' => $preset,
            'limit' => array(0, $max),
            'language' => $this->langcode
        );

        $results = $this->search->search($id, $term, $options);

        $response = array();
        foreach ($results as $result) {
            $template = "backend|search/suggestion/$entityname";
            $response[] = $this->render($template, array($entityname => $result), true);
        }

        return $response;
    }

    /**
     * Uploads an image
     * @return array
     */
    public function uploadImageAjax()
    {
        $path = 'image/upload';
        $type = $this->request->post('type');

        if (!empty($type)) {
            $type = (string) $type;
            $path .= '/' . $this->config("{$type}_image_dirname", $type);
        }

        $this->addValidator('file', array(
            'upload' => array(
                'path' => $path,
                'file' => $this->request->file('file')
        )));

        $errors = $this->setValidators();

        if (isset($errors['file'])) {
            return array('error' => (string) $errors['file']);
        }

        $response = array();
        $uploaded = $this->getValidatorResult('file');
        $preset = $this->config('admin_image_preset', 2);
        $thumb = $this->image->url($preset, $uploaded, true);

        $key = uniqid(); // Random array key to prevent merging items in the array
        $timestamp = filemtime(GC_FILE_DIR . "/$uploaded");
        $image = array(
            'weight' => 0,
            'thumb' => $thumb,
            'path' => $uploaded,
            'uploaded' => $timestamp
        );

        $data = array(
            'name_prefix' => $type,
            'languages' => $this->languages,
            'images' => array($key => $image));

        $attached = $this->render('backend|common/image/attache', $data, true);
        $response['files'][] = array('html' => $attached);
        return $response;
    }

    /**
     * Rates a product
     * @return array
     */
    public function rateAjax()
    {
        $stars = (int) $this->request->post('stars', 0);
        $product_id = (int) $this->request->post('product_id');

        if (empty($product_id) || empty($this->uid)) {
            return array(
                'error' => $this->text('You are not permitted to perform this operation'));
        }

        $options = array(
            'stars' => $stars,
            'product_id' => $product_id
        );

        $added = $this->rating->add($options);

        if ($added) {
            return array('success' => 1);
        }

        return array('error' => $this->text('An error occurred'));
    }

}
