<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\frontend;

use core\models\Sku as ModelsSku;
use core\models\File as ModelsFile;
use core\models\State as ModelsState;
use core\models\Search as ModelsSearch;
use core\models\Rating as ModelsRating;
use core\models\Country as ModelsCountry;
use core\models\Collection as ModelsCollection;
use core\models\CollectionItem as ModelsCollectionItem;
use core\controllers\frontend\Controller as FrontendController;

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
     * Sku model instance
     * @var \core\models\Sku $sku
     */
    protected $sku;

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsSearch $search
     * @param ModelsFile $file
     * @param ModelsRating $rating
     * @param ModelsSku $sku
     * @param ModelsCollection $collection
     * @param ModelsCollectionItem $collection_item
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsSearch $search, ModelsFile $file, ModelsRating $rating,
            ModelsSku $sku, ModelsCollection $collection,
            ModelsCollectionItem $collection_item)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->file = $file;
        $this->state = $state;
        $this->rating = $rating;
        $this->search = $search;
        $this->country = $country;
        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Main ajax callback
     */
    public function getResponseAjax()
    {
        if (!$this->request->isAjax()) {
            exit(1); // Reject non-ajax requests
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
            $response = array('error' => $exc->getMessage());
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

        if (empty($products)) {
            return array();
        }

        $stores = $this->store->getList();

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

        $product = $this->product->get($product_id);
        $response = $this->sku->selectCombination($product, $field_value_ids);

        $options = array(
            'calculate' => false,
            'imagestyle' => $this->setting('image_style_product', 5));

        $this->setItemThumb($response['combination'], $options);
        $this->setItemPrice($response['combination'], $options);

        return $response;
    }

    /**
     * Returns the cart preview for the current user
     * @return array
     */
    public function getCartPreviewAjax()
    {
        $options = array(
            'user_id' => $this->cart_uid,
            'store_id' => $this->store_id
        );

        $cart = $this->cart->getContent($options);

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
            'template_item' => 'search/suggestion',
            'imagestyle' => $this->setting('image_style_product_list', 3)
        );

        return $this->prepareProducts($products, $options);
    }

    /**
     * Returns an array of suggested collection entities
     * @return array
     */
    public function getCollectionItemAjax()
    {
        $term = (string) $this->request->post('term');
        $collection_id = (int) $this->request->post('collection_id');

        if (empty($term) || empty($collection_id)) {
            return array('error' => $this->text('An error occurred'));
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            return array('error' => $this->text('An error occurred'));
        }

        if (!$this->access($collection['type'])) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $max = $this->config('admin_autocomplete_limit', 10);
        $options = array('title' => $term, 'limit' => array(0, $max));

        return $this->collection_item->getSuggestions($collection, $options);
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

        if (!$this->access($entityname)) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
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

        $result = $this->file->setUploadPath($path)
                ->upload($this->request->file('file'));

        if ($result !== true) {
            return array('error' => (string) $result);
        }

        $response = array();

        $uploaded = $this->file->getUploadedFile(true);
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
