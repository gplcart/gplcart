<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\classes\Cache;
use core\models\Cart as ModelsCart;
use core\models\Price as ModelsPrice;
use core\models\Image as ModelsImage;
use core\models\Order as ModelsOrder;
use core\models\Alias as ModelsAlias;
use core\models\Review as ModelsReview;
use core\models\Rating as ModelsRating;
use core\models\Product as ModelsProduct;
use core\models\Wishlist as ModelsWishlist;
use core\models\ProductClass as ModelsProductClass;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends Controller
{

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Product class model instance
     * @var \core\models\ProductClass $product_class
     */
    protected $product_class;

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
     * Orders model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Wishlist model instance
     * @var \core\models\Wishlist $wishlist
     */
    protected $wishlist;

    /**
     * Review model instance
     * @var \core\models\Review $review
     */
    protected $review;

    /**
     * Rating model instance
     * @var \core\models\Rating $rating
     */
    protected $rating;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsProductClass $product_class
     * @param ModelsPrice $price
     * @param ModelsImage $image
     * @param ModelsCart $cart
     * @param ModelsOrder $order
     * @param ModelsWishlist $wishlist
     * @param ModelsReview $review
     * @param ModelsRating $rating
     * @param ModelsAlias $alias
     */
    public function __construct(ModelsProduct $product,
            ModelsProductClass $product_class, ModelsPrice $price,
            ModelsImage $image, ModelsCart $cart, ModelsOrder $order,
            ModelsWishlist $wishlist, ModelsReview $review,
            ModelsRating $rating, ModelsAlias $alias)
    {
        parent::__construct();

        $this->cart = $cart;
        $this->alias = $alias;
        $this->price = $price;
        $this->image = $image;
        $this->order = $order;
        $this->review = $review;
        $this->rating = $rating;
        $this->product = $product;
        $this->wishlist = $wishlist;
        $this->product_class = $product_class;
    }

    /**
     * Displays a product page
     * @param integer $product_id
     */
    public function product($product_id)
    {
        $product = $this->get($product_id);

        $this->data['product'] = $product;
        $this->data['price'] = $product['price_formatted'];

        $this->submitted = $this->request->post('product', array());

        if (!empty($this->submitted)) {
            $this->addToCart();
        }

        $this->setReviews($product);
        $this->setRatingWidget($product);
        $this->setShareWidget($product);
        $this->setCartForm($product);
        $this->setImages($product);
        $this->setRelated($product);
        $this->setRecent($product);

        $this->setCssProduct();
        $this->setJsProduct($product);
        $this->setTitleProduct($product);
        $this->outputProduct();
    }

    /**
     * Sets list of reviews related to the product
     * @param array $product
     */
    protected function setReviews(array $product)
    {
        $enabled = $this->config->get('review_enabled', 1);

        if (empty($enabled)) {
            return;
        }

        $total = $this->getTotalReviews($product['product_id']);

        if (empty($total)) {
            return;
        }

        $query = $this->getFilterQuery();
        $per_page = $this->config->get('review_limit', 5);
        $limit = $this->setPager($total, $query, $per_page);
        $reviews = $this->getReviews($limit, $product);

        $this->data['reviews'] = $this->render('review/list', array(
            'product' => $product,
            'query' => $this->query,
            'pager' => $this->getPager(),
            'reviews' => $this->prepareReviews($reviews, $product),
            'editable' => (bool) $this->config->get('review_editable', 1)
        ));
    }

    /**
     * Modifies an array of reviews
     * @param array $reviews
     * @param array $product
     * @return array
     */
    protected function prepareReviews(array $reviews, array $product)
    {
        $users = array();
        foreach ($reviews as $review) {
            $users[] = $review['user_id'];
        }

        if (!empty($users)) {
            $ratings = $this->rating->getByUser($product['product_id'], $users);
        }

        foreach ($reviews as &$review) {
            $rating = isset($ratings[$review['user_id']]['rating']) ? $ratings[$review['user_id']]['rating'] : 0;
            $review['rating_widget'] = $this->render('common/rating/static', array('rating' => $rating));
        }

        return $reviews;
    }

    /**
     * Returns an array of reviews for the product
     * @param array $limit
     * @param array $product
     * @return array
     */
    protected function getReviews(array $limit, array $product)
    {
        $options = array(
            'status' => 1,
            'limit' => $limit,
            'user_status' => 1,
            'product_id' => $product['product_id']
        );

        $options += $this->query;
        return $this->review->getList($options);
    }

    /**
     * Sets rendered rating widget
     * @param array $product
     */
    protected function setRatingWidget(array $product)
    {
        $rating = $this->rating->getByProduct($product['product_id'], true);

        $options = array(
            'product' => $product,
            'votes' => isset($rating['votes']) ? $rating['votes'] : 0,
            'rating' => isset($rating['rating']) ? $rating['rating'] : 0
        );

        $this->data['rating'] = $this->render('common/rating/static', $options);
    }

    /**
     * Sets rendered share widget
     * @param array $product
     */
    protected function setShareWidget(array $product)
    {
        $this->data['share'] = $this->render('common/share', array(
            'url' => $this->url(false, array(), true),
            'title' => $product['title']));
    }

    /**
     * Sets rendered "Add to cart form"
     * @param array $product
     */
    protected function setCartForm(array $product)
    {
        $access = ($product['stock'] || !$product['subtract']);

        $cart = array(
            'product' => $product,
            'token' => $this->token,
            'cart_access' => $access,
            'field_data' => $product['fields']
        );

        $this->data['cart_form'] = $this->render('cart/add', $cart);
    }

    /**
     * Renders and displays product page
     */
    protected function outputProduct()
    {
        $this->output('product/product');
    }

    /**
     * Sets title on the product page
     * @param array $product
     */
    protected function setTitleProduct(array $product)
    {
        $this->setTitle($product['title'], false);
    }

    /**
     * Sets CSS on the product page
     */
    protected function setCssProduct()
    {
        $this->setCss('files/assets/jquery/lightgallery/dist/css/lightgallery.min.css');
    }

    /**
     * Sets Javascripts on the product page
     * @param array $product
     */
    protected function setJsProduct(array $product)
    {
        $this->setJs('files/assets/jquery/lightgallery/dist/js/lightgallery-all.min.js', 'top');
        $this->setJs('files/assets/jquery/lightslider/dist/js/lightslider.min.js', 'top');
        $this->setJsSettings('product', $product);
    }

    /**
     * Sets block with recent products on the product page
     * @param array $product
     */
    protected function setRecent(array $product)
    {
        $products = $this->getRecent($product['product_id']);

        $this->data['recent'] = $this->render('product/block/recent', array(
            'products' => $this->renderBlockItems($products)
        ));
    }

    /**
     * Sets block with related products on the product page
     * @param array $product
     */
    protected function setRelated(array $product)
    {
        $products = $this->getRelated($product['product_id']);

        $this->data['related'] = $this->render('product/block/related', array(
            'products' => $this->renderBlockItems($products)
        ));
    }

    /**
     * Sets rendered product images
     * @param array $product
     */
    protected function setImages(array $product)
    {
        $this->data['images'] = $this->render('product/images', array(
            'product' => $product,
            'images' => $this->getImages($product),
        ));
    }

    /**
     * Returns an array of rendered, ready-to-display product items
     * @param array $products
     * @return array
     */
    protected function renderBlockItems(array $products)
    {
        $rendered = array();
        foreach ($products as $product) {
            $rendered[] = $this->render('product/item/grid', array('product' => $product));
        }

        return $rendered;
    }

    /**
     * Returns a total number of reviews for this product
     * @param integer $product_id
     * @return integer
     */
    protected function getTotalReviews($product_id)
    {
        return $this->review->getList(array(
                    'status' => 1,
                    'count' => true,
                    'user_status' => 1,
                    'product_id' => $product_id
        ));
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function get($product_id)
    {
        $product = Cache::get("product.$product_id.{$this->langcode}");

        if (!isset($product)) {

            $product = $this->product->get($product_id, $this->langcode);

            if (empty($product)) {
                $this->outputError(404);
            }

            $alias = $this->alias->get('product_id', $product_id);

            if (empty($alias)) {
                $this->url("product/$product_id");
            } else {
                $product['url'] = $this->url($alias);
            }

            $product['fields'] = $this->getFields($product);

            Cache::set("product.$product_id.{$this->langcode}", $product);
        }

        if ($product['store_id'] != $this->store_id) {
            $this->outputError(404);
        }

        if (empty($product['status']) && !$this->access('product')) {
            $this->outputError(404);
        }

        $enabled = $this->store->config('catalog_pricerule');

        if (!empty($enabled)) {
            $calculated = $this->product->calculate($product, $this->store_id);
            $product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
        return $product;
    }

    /**
     * Returns an array of loaded related products
     * @param integer $product_id
     * @return array
     */
    protected function getRelated($product_id)
    {
        $limit = $this->config->get('product_related_limit', 12);

        $options = array(
            'status' => 1,
            'limit' => array(0, $limit),
            'store_id' => $this->store_id
        );

        $product_ids = $this->product->getRelated($product_id, false, $options);
        return $this->loadMultiple($product_ids);
    }

    /**
     * Returns an array of loaded recent products
     * @param integer $product_id
     * @return array
     */
    protected function getRecent($product_id)
    {
        $limit = $this->config->get('product_recent_limit', 12);
        $lifespan = $this->config->get('product_recent_cookie_lifespan', 31536000);

        $product_ids = $this->product->setViewed($product_id, $limit, $lifespan);
        return $this->loadMultiple($product_ids);
    }

    /**
     * Returns an array of loaded products
     * @param array $product_ids
     * @return array
     */
    protected function loadMultiple($product_ids)
    {
        if (empty($product_ids)) {
            return array();
        }

        $pricerules = $this->store->config('catalog_pricerule');
        $products = $this->product->getList(array('product_id' => $product_ids));
        $imagestyle = $this->getSettings('image_style_product_grid', 3);

        foreach ($products as $product_id => &$product) {

            if (empty($product['alias'])) {
                $product['url'] = $this->url("product/$product_id");
            } else {
                $product['url'] = $this->url($product['alias']);
            }

            $product['thumb'] = $this->image->getThumb($product_id, $imagestyle, 'product_id', $product_ids);

            if (!empty($pricerules)) {
                $calculated = $this->product->calculate($product, $this->store_id);
                $product['price'] = $calculated['total'];
            }

            $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
        }

        return $products;
    }

    /**
     * Validates and adds a product to the cart
     */
    protected function addToCart()
    {
        $is_ajax = $this->request->isAjax();

        $this->validateAddToCart();
        $errors = $this->getError();

        if (!empty($errors)) {
            if ($is_ajax) {
                $this->response->json(array('errors' => $errors));
            }
            $this->redirect('', $errors, 'danger');
        }

        $add_result = $this->cart->addProduct($this->submitted);

        if ($add_result === true) {
            if (!$is_ajax) {
                $this->redirect('', $this->text('Product has been added to your cart. <a href="!href">Checkout</a>', array('!href' => $this->url('checkout'))), 'success');
            }

            $cart = $this->cart->getByUser();

            $response = array(
                'quantity' => $cart['quantity'],
                'preview' => $this->render('cart/preview', array(
                    'cart' => $this->cart->prepareCartItems($cart, $this->getSettings()),
                    'limit' => $this->config->get('cart_preview_limit', 5)
            )));

            $this->response->json($response);
        }

        foreach ($add_result as &$error) {
            $error = $this->text($error);
            if (!$is_ajax) {
                $this->session->setMessage($error, 'danger');
            }
        }

        if ($is_ajax) {
            $this->response->json(array('errors' => $add_result));
        }

        $this->redirect();
    }

    /**
     * Returns an array of product's fields
     * @param array $product
     * @return array
     */
    protected function getFields(array $product)
    {
        $field_data = $this->product_class->getFieldData($product['product_class_id']);

        if (empty($product['field']['option'])) {
            return $field_data;
        }

        foreach ($product['field']['option'] as $field_id => $field_values) {
            if (empty($field_data['option'][$field_id]) || $field_data['option'][$field_id]['widget'] != 'image') {
                continue;
            }

            foreach ($field_values as $field_value_id) {
                $path = $field_data['option'][$field_id]['values'][$field_value_id]['path'];
                if ($path) {
                    $thumb = $this->image->url($this->store->config('image_style_option'), $path);
                } else {
                    $thumb = $this->image->placeholder($this->store->config('image_style_option'));
                }

                $field_data['option'][$field_id]['values'][$field_value_id]['thumb'] = $thumb;
            }
        }

        return $field_data;
    }

    /**
     * Returns an array of product images
     * @param array $product
     * @return array
     */
    protected function getImages($product)
    {
        if (empty($product['images'])) {
            return array();
        }

        $imagestyle = $this->getSettings('image_style_product', 5);
        $imagestyle_extra = $this->getSettings('image_style_product_extra', 3);

        $images = array();
        foreach ($product['images'] as $image) {
            $images[] = $image + array(
                'url_original' => $this->image->urlFromPath($image['path']),
                'url_big' => $this->image->url($imagestyle, $image['path']),
                'url_extra' => $this->image->url($imagestyle_extra, $image['path']),
            );
        }

        $main = array_shift($images);
        return array('main' => $main, 'extra' => $images);
    }

    /**
     * Validates adding a product to the cart
     * @return null
     */
    protected function validateAddToCart()
    {
        if (empty($this->submitted['quantity'])) {
            $this->submitted['quantity'] = 1;
            return;
        }

        if (!is_numeric($this->submitted['quantity']) || strlen($this->submitted['quantity']) > 2) {
            $this->errors['quantity'] = $this->text('Invalid quantity');
        }
    }

}
