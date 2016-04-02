<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Controller;
use core\models\Bookmark;
use core\models\Product;
use core\models\Price;
use core\models\Image;
use core\models\Cart;
use core\models\Country;
use core\models\State;
use core\models\Search;
use core\models\File;
use core\models\Rating;

class Ajax extends Controller
{

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

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
     * @param Bookmark $bookmark
     * @param Product $product
     * @param Price $price
     * @param Image $image
     * @param Cart $cart
     * @param Country $country
     * @param Search $search
     * @param File $file
     * @param Rating $rating
     */
    public function __construct(Bookmark $bookmark, Product $product, Price $price, Image $image, Cart $cart, Country $country, State $state, Search $search, File $file, Rating $rating)
    {
        parent::__construct();

        $this->file = $file;
        $this->cart = $cart;
        $this->price = $price;
        $this->image = $image;
        $this->state = $state;
        $this->rating = $rating;
        $this->search = $search;
        $this->product = $product;
        $this->country = $country;
        $this->bookmark = $bookmark;
    }

    /**
     * Main ajax callback
     */
    public function ajax()
    {
        if (!$this->request->ajax()) {
            exit; // Reject non-ajax requests
        }

        $action = $this->request->post('action');

        if (empty($action)) {
            $this->response->json(array('error' => $this->text('Missing handler')));
        }

        // action = method name. Check if the method exists
        if (!method_exists($this, $action)) {
            $this->response->json(array('error' => $this->text('Missing handler')));
        }

        $this->response->json($this->{$action}());
    }

    /**
     * Adds a bookmark for an admin
     * @return array
     */
    public function adminAddBookmark()
    {
        if (!$this->access('bookmark_add')) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $url = $this->request->post('url');

        if (empty($url)) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $title = $this->request->post('title');

        if (empty($title) || mb_strlen($title) > 255) {
            return array('error' => $this->text('An error occurred'));
        }

        $bookmark_id = $this->bookmark->add(array('url' => $url, 'title' => $title, 'user_id' => $this->uid), true);

        if (!empty($bookmark_id)) {
            return array('success' => 1, 'bookmark_id' => $bookmark_id);
        }

        return array('error' => $this->text('An error occurred'));
    }

    /**
     * Deletes a bookmark
     * @return array
     */
    public function deleteBookmark()
    {
        $bookmark_id = $this->request->post('bookmark_id', 0);

        if ($this->access('bookmark_delete') && $this->bookmark->delete($bookmark_id)) {
            return array('success' => 1);
        }

        return array('error' => $this->text('An error occurred'));
    }

    /**
     * Returns an array of products
     * @return array
     */
    public function getProducts()
    {
        if (!$this->access('product')) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $products = $this->product->getList(array(
            'title' => $this->request->post('term', ''),
            'store_id' => $this->request->post('store_id', null),
            'status' => $this->request->post('status', null),
            'limit' => array(0, $this->config->get('admin_autocomplete_limit', 10))));

        if ($products) {
            $stores = $this->store->getList();
        }

        $list = array();
        foreach ($products as $product) {
            $product['url'] = '';
            if (isset($stores[$product['store_id']])) {
                $store = $stores[$product['store_id']];
                $product['url'] = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", "/") . "/product/{$product['product_id']}";
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
    public function getUsers()
    {
        if (!$this->access('user')) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        $users = $this->user->getList(array(
            'email' => $this->request->post('term', ''),
            'store_id' => $this->request->post('store_id', null),
            'limit' => array(0, $this->config->get('admin_autocomplete_limit', 10))));

        return $users;
    }

    /**
     * Toggles product options
     * @return boolean
     */
    public function switchProductOptions()
    {
        $product_id = $this->request->post('product_id');
        $product = $this->product->get($product_id);

        if (empty($product['status'])) {
            return array('error' => $this->text('Invalid product'));
        }

        $response = array();

        $field_value_ids = $this->request->post('values');

        if (!empty($field_value_ids)) {
            $combination_id = $this->product->getCombinationId($field_value_ids, $product_id);

            $response = array(
                'message' => '',
                'combination' => array(),
                'message_modal' => false,
                'cart_access' => true,
                'subscribe' => false,
            );

            if (!empty($product['combination'][$combination_id])) {
                $combination = $product['combination'][$combination_id];

                if (!empty($combination['price'])) {
                    $combination['price'] = $this->price->format($combination['price'], $product['currency']);
                }

                if (!empty($combination['path'])) {
                    $combination['image'] = $this->image->url($this->store->config('image_style_product'), $combination['path']);
                }

                $response['combination'] = $combination;

                if (empty($combination['stock']) && $product['subtract']) {
                    $response['message'] = $this->text('Out of stock');
                    $response['cart_access'] = false;
                    $response['subscribe'] = true;
                }
            }
        }

        $this->hook->fire('switch.product.options', $field_value_ids, $product, $response);
        return $response;
    }

    /**
     * Returns the cart preview for the current user
     * @return type
     */
    public function getCartPreview()
    {
        $cart = $this->cart->getByUser();

        if (empty($cart['items'])) {
            return array();
        }

        $preview = array(
            'preview' => $this->render('cart/preview', array(
                'cart' => $this->prepareCartItems($cart),
                'limit' => $this->config->get('cart_preview_limit', 5)
        )));

        return $preview;
    }
    
    protected function prepareCartItems($cart)
    {
        $imagestyle = $this->config->module($this->theme, 'image_style_cart', 3);

        foreach ($cart['items'] as &$item) {
            $imagepath = '';

            if (empty($item['product']['combination_id'])
                    && !empty($item['product']['images'])) {
                $imagefile = reset($item['product']['images']);
                $imagepath = $imagefile['path'];
            }

            if (!empty($item['product']['option_file_id'])
                    && !empty($item['product']['images'][$item['product']['option_file_id']]['path'])) {
                $imagepath = $item['product']['images'][$item['product']['option_file_id']]['path'];
            }

            $item['total_formatted'] = $this->price->format($item['total'], $cart['currency']);
            $item['price_formatted'] = $this->price->format($item['price'], $cart['currency']);

            if (empty($imagepath)) {
                $item['thumb'] = $this->image->placeholder($imagestyle);
            } else {
                $item['thumb'] = $this->image->url($imagestyle, $imagepath);
            }
        }
        
        $cart['total_formatted'] = $this->price->format($cart['total'], $cart['currency']);
        return $cart;
    }

    /**
     * Returns an array of country data
     * @return array
     */
    public function getCountryData()
    {
        $country_code = $this->request->post('country');

        if (empty($country_code)) {
            return array();
        }

        $states = $this->state->getList(array('country' => $country_code, 'status' => 1));

        return array(
            'states' => $states,
            'format' => array_keys($this->country->getFormat($country_code, true))
        );
    }

    /**
     * Returns an array of products based on certain conditions
     * @return array
     */
    public function searchProducts()
    {
        $term = $this->request->post('term');

        if (empty($term)) {
            return array();
        }

        $products = $this->search->search('product_id', $term, array(
            'status' => 1,
            'store_id' => $this->store_id,
            'limit' => array(0, $this->config->get('autocomplete_limit', 10)),
            'language' => $this->langcode));

        if (empty($products)) {
            return array();
        }

        $product_ids = array_keys($products);
        $pricerules = $this->store->config('catalog_pricerule');
        $imestylestyle = $this->config->module($this->theme, 'image_style_product_list', 3);

        foreach ($products as $product_id => &$product) {
            unset($product['description']);

            $product['thumb'] = $this->image->getThumb($product_id, $imestylestyle, 'product_id', $product_ids);

            if ($pricerules) {
                $calculated = $this->product->calculate($product, $store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
            $product['rendered'] = $this->render('search/suggestion', array('product' => $product));
        }

        return $products;
    }

    /**
     * Returns an array of products for admin
     * @return array
     */
    public function adminSearch()
    {
        $term = $this->request->post('term');
        $id = $this->request->post('id');

        if (empty($term) || empty($id)) {
            return array('error' => $this->text('An error occurred'));
        }

        $entityname = preg_replace('/_id$/', '', $id);

        if (!$this->access('admin') || !$this->access($entityname)) {
            return array('error' => $this->text('An error occurred'));
        }

        $results = $this->search->search($id, $term, array(
            'language' => $this->langcode,
            'imagestyle' => $this->config->get('admin_image_style', 2),
            'limit' => array(0, $this->config->get('admin_autocomplete_limit', 10))));

        $response = array();
        foreach ($results as $result) {
            $response[] = $this->render("backend:search/suggestion/$entityname", array($entityname => $result), true);
        }

        return $response;
    }

    /**
     * Uploads an image
     * @return array
     */
    public function uploadImage()
    {
        $file = $this->request->file();

        if (empty($file['file']['name'])) {
            return array('error' => $this->text('Nothing to upload'));
        }

        $response = array();

        $upload_path = 'image/upload';
        $type = $this->request->post('type');

        if ($type) {
            $upload_path .= '/' . $this->config->get("{$type}_image_dirname", $type);
        }

        $this->file->setUploadPath($upload_path);

        $upload_result = $this->file->upload($file['file']);

        if ($upload_result !== true) {
            return array('error' => $upload_result);
        }

        $uploaded_path = $this->file->getUploadedFile();

        $path = $this->file->path($uploaded_path);
        $thumb = $this->image->url($this->config->get('admin_image_preset', 2), $path, true);
        $key = uniqid(); // Random array key to prevent merging items in the array

        $response['files'][] = array(
            'html' => $this->render('backend:common/image/attache', array(
                'name_prefix' => $type,
                'languages' => $this->languages,
                'images' => array($key => array(
                        'path' => $path,
                        'weight' => 0,
                        'thumb' => $thumb,
                        'uploaded' => filemtime($uploaded_path)))), true)
        );

        return $response;
    }

    public function rate()
    {
        $product_id = $this->request->post('product_id');
        $stars = $this->request->post('stars', 0);

        if (empty($product_id) || empty($this->uid)) {
            return array('error' => $this->text('You are not permitted to perform this operation'));
        }

        if ($this->rating->add(array('product_id' => $product_id, 'stars' => $stars))) {
            return array('success' => 1);
        }

        return array('error' => $this->text('An error occurred'));
    }
}
