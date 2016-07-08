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
use core\models\Bookmark as ModelsBookmark;
use core\models\Shipping as ModelsShipping;
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
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

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
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Constructor
     * @param ModelsProduct $product
     * @param ModelsProductClass $product_class
     * @param ModelsPrice $price
     * @param ModelsImage $image
     * @param ModelsCart $cart
     * @param ModelsOrder $order
     * @param ModelsBookmark $bookmark
     * @param ModelsReview $review
     * @param ModelsRating $rating
     * @param ModelsAlias $alias
     * @param ModelsShipping $shipping
     */
    public function __construct(ModelsProduct $product,
            ModelsProductClass $product_class, ModelsPrice $price,
            ModelsImage $image, ModelsCart $cart, ModelsOrder $order,
            ModelsBookmark $bookmark, ModelsReview $review,
            ModelsRating $rating, ModelsAlias $alias, ModelsShipping $shipping)
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
        $this->bookmark = $bookmark;
        $this->shipping = $shipping;
        $this->product_class = $product_class;
    }

    /**
     * Displays a product page
     * @param integer $product_id
     */
    public function product($product_id)
    {
        $product = $this->get($product_id, $this->langcode);

        if (!$product) {
            $this->outputError(404);
        }

        $submitted_product = $this->request->post('product');

        if ($submitted_product) {
            $this->addToCart($submitted_product);
        }

        $this->data['product'] = $product;

        $cart = array(
            'product' => $product,
            'token' => $this->token,
            'field_data' => $product['fields'],
            'cart_access' => ($product['stock'] || !$product['subtract'])
        );

        $this->data['cart_form'] = $this->render('common/cart/add', $cart);

        $rating = $this->rating->getByProduct($product_id, true);
        $this->data['rating'] = $this->render('common/rating/static', array(
            'product' => $product,
            'rating' => isset($rating['rating']) ? $rating['rating'] : 0,
            'votes' => isset($rating['votes']) ? $rating['votes'] : 0));

        $this->data['share'] = $this->render('common/share', array(
            'url' => $this->url(false, array(), true),
            'title' => $product['title']));

        $total_reviews = $this->getTotalReviews($product_id);

        $limit = $this->setPager($total_reviews, $this->query, $this->config->get('review_limit', 5));

        if ($total_reviews) {
            $reviews = $this->review->getList(array(
                'limit' => $limit,
                'product_id' => $product_id,
                'status' => 1,
                'user_status' => 1) + $this->query);

            $users = array();
            foreach ($reviews as $review) {
                $users[] = $review['user_id'];
            }

            if ($users) {
                $ratings = $this->rating->getByUser($product_id, $users);
            }

            foreach ($reviews as &$review) {
                $rating = isset($ratings[$review['user_id']]['rating']) ? $ratings[$review['user_id']]['rating'] : 0;
                $review['rating_widget'] = $this->render('common/rating/static', array('rating' => $rating));
            }

            if ($this->config->get('review_enabled', 1)) {
                $this->data['reviews'] = $this->render('review/list', array(
                    'product' => $product,
                    'reviews' => $reviews,
                    'query' => $query,
                    'editable' => $this->config->get('review_editable', 1),
                    'pager' => $this->data['pager'],
                ));
            }
        }

        $this->data['images'] = $this->render('product/images', array(
            'product' => $product,
            'images' => $this->getImages($product),
        ));

        $related = $this->getRelated($product_id, $this->langcode);
        $this->data['related'] = $this->render('product/block/related', array(
            'product' => $product,
            'products' => array_chunk($related, 6)
        ));

        $recent = $this->getRecent($product_id, $this->langcode);
        $this->data['recent'] = $this->render('product/block/recent', array(
            'product' => $product,
            'products' => array_chunk($recent, 6)
        ));

        $quotes = $this->getShippingQuotes($product);
        $this->data['shipping_quotes'] = $this->render('product/shipping', array(
            'product' => $product,
            'quotes' => $quotes
        ));

        //d($this->user->getShippingAddress($this->uid));

        $this->data['price'] = $product['price_formatted'];
        $this->data['footer_content'][] = array('content' => $this->render('product/toolbar', array('product' => $product)));

        $this->setJs('files/assets/photoswipe/photoswipe.min.js', 'top');
        $this->setJs('files/assets/photoswipe/photoswipe-ui-default.min.js', 'top');

        $this->setCss('files/assets/photoswipe/photoswipe.css');
        $this->setCss('files/assets/photoswipe/default-skin/default-skin.css');

        $this->setTitle($product['title'], false);
        $this->output('product/product');
    }

    /**
     * Returns a total number of reviews for this product
     * @param integer $product_id
     * @return integer
     */
    protected function getTotalReviews($product_id)
    {
        $total = $this->review->getList(array(
            'count' => true,
            'product_id' => $product_id,
            'status' => 1,
            'user_status' => 1
        ));

        return $total;
    }

    protected function getShippingQuotes($product)
    {

        //$cart = $this->cart->getByUser();
        //$order =array();

        $services = $this->shipping->getServices();

        foreach ($services as &$service) {
            $service['price'] = $this->price->convert((int) $service['price'], $service['currency'], $product['currency']);
            $service['price_formatted'] = $this->price->format($service['price'], $product['currency']);
        }

        return $services;
    }

    protected function get($product_id, $langcode)
    {
        $product = Cache::get("product.$product_id.$langcode");

        if (!isset($product)) {
            $product = $this->product->get($product_id, $langcode);

            if (!$product) {
                return array();
            }

            $alias = $this->alias->get('product_id', $product_id);
            $product['url'] = $alias ? $this->url($alias) : $this->url("product/$product_id");
            $product['fields'] = $this->getFields($product);

            Cache::set("product.$product_id.$langcode", $product);
        }

        if ($product['store_id'] != $this->store_id) {
            return array();
        }

        if (empty($product['status']) && !$this->access('product')) {
            return array();
        }

        if ($this->store->config('catalog_pricerule')) {
            $calculated = $this->product->calculate($product, $this->store_id);
            $product['price'] = $calculated['total'];
        }

        $product['price_formatted'] = $this->price->format($product['price'], $product['currency']);
        return $product;
    }

    protected function getRelated($product_id, $langcode)
    {
        $related = $this->product->getRelated($product_id, false, array(
            'store_id' => $this->store_id,
            'limit' => array(0, $this->config->get('product_related_limit', 12)),
            'status' => 1));

        return $this->getMultiple($related, $langcode);
    }

    protected function getMultiple($product_ids, $langcode)
    {
        if (!$product_ids) {
            return array();
        }

        $style = $this->store->config('image_style_product_grid');

        $list = array();
        foreach ($product_ids as $product_id) {
            $product = $this->get($product_id, $langcode);

            if (!$product) {
                continue;
            }

            if (!empty($product['images'])) {
                $image = reset($product['images']);
                $product['thumb'] = $this->image->url($style, $image['path']);
            } else {
                $product['thumb'] = $this->image->placeholder($style);
            }

            $list[$product_id] = $product;
        }

        return $list;
    }

    protected function getRecent($product_id, $langcode)
    {
        $limit = $this->config->get('product_recent_limit', 12);
        $lifespan = $this->config->get('product_recent_cookie_lifespan', 31536000);

        $products = $this->product->setViewed($product_id, $limit, $lifespan);
        return $this->getMultiple($products, $langcode);
    }

    /**
     * Validates and adds a product to the cart
     * @param array $submitted
     */
    protected function addToCart($submitted)
    {
        $ajax = $this->request->ajax();

        $this->validateAddToCart($submitted);
        $errors = $this->formErrors(false);

        if ($errors) {
            if ($ajax) {
                $this->response->json(array('errors' => $errors));
            }
            $this->redirect('', $errors, 'danger');
        }

        $add_result = $this->cart->addProduct($submitted);

        if ($add_result === true) {
            if (!$ajax) {
                $this->redirect('', $this->text('Product has been added to your cart. <a href="!href">Checkout</a>', array('!href' => $this->url('checkout'))), 'success');
            }

            $cart = $this->cart->getByUser();

            $this->response->json(array(
                'quantity' => $cart['quantity'],
                'preview' => $this->render('common/cart/preview', array(
                    'cart' => $cart,
                    'limit' => $this->config->get('cart_preview_limit', 5)
            ))));
        }

        foreach ($add_result as &$error) {
            $error = $this->text($error);
            if (!$ajax) {
                $this->session->setMessage($error, 'danger');
            }
        }

        if ($ajax) {
            $this->response->json(array('errors' => $add_result));
        }

        $this->redirect();
    }

    protected function getFields($product)
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

    protected function getImages($product)
    {
        if (empty($product['images'])) {
            return array();
        }

        $imagestyle = $this->store->config('image_style_product');
        $imagestyle_extra = $this->store->config('image_style_product_extra');

        $images = array();
        foreach ($product['images'] as $image) {
            $images[] = $image + array(
                'url_original' => $this->image->urlFromPath($image['path']),
                'url_big' => $this->image->url($imagestyle, $image['path']),
                'url_extra' => $this->image->url($imagestyle_extra, $image['path']),
                'size' => getimagesize(GC_FILE_DIR . '/' . $image['path']),
            );
        }

        $main = array_shift($images);
        return array('main' => $main, 'extra' => $images);
    }

    protected function validateAddToCart(&$submitted)
    {
        if (empty($submitted['quantity'])) {
            $submitted['quantity'] = 1;
            return;
        }

        if (!is_numeric($submitted['quantity']) || strlen($submitted['quantity']) > 2) {
            $this->data['form_errors']['quantity'] = $this->text('Invalid quantity');
        }
    }

}
