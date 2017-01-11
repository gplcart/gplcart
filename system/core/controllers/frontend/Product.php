<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Review as ReviewModel;
use gplcart\core\models\Rating as RatingModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to products
 */
class Product extends FrontendController
{

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Orders model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Review model instance
     * @var \gplcart\core\models\Review $review
     */
    protected $review;

    /**
     * Rating model instance
     * @var \gplcart\core\models\Rating $rating
     */
    protected $rating;

    /**
     * Constructor
     * @param ProductClassModel $product_class
     * @param OrderModel $order
     * @param ReviewModel $review
     * @param RatingModel $rating
     */
    public function __construct(ProductClassModel $product_class,
            OrderModel $order, ReviewModel $review, RatingModel $rating)
    {
        parent::__construct();

        $this->order = $order;
        $this->review = $review;
        $this->rating = $rating;
        $this->product_class = $product_class;
    }

    /**
     * Displays a product page
     * @param integer $product_id
     */
    public function indexProduct($product_id)
    {
        $product = $this->getProduct($product_id);
        
        $this->setHtmlFilter($product);
        
        $share = $this->renderShareWidget();

        $this->setData('share', $share);
        $this->setData('product', $product);

        $this->setReviewsProduct($product);
        $this->setRatingWidgetProduct($product);
        $this->setCartFormProduct($product);
        $this->setImagesProduct($product);
        $this->setRelatedProduct($product);
        $this->setRecentProduct($product);

        $this->setJsIndexProduct($product);
        $this->setTitleIndexProduct($product);
        $this->setBreadcrumbIndexProduct($product);

        $this->setMetaEntity($product);
        $this->outputIndexProduct();
    }

    /**
     * Sets list of reviews related to the product
     * @param array $product
     * @return null
     */
    protected function setReviewsProduct(array $product)
    {
        $per_page = (int) $this->config('review_limit', 5);
        $enabled = (bool) $this->config('review_enabled', 1);
        $editable = (bool) $this->config('review_editable', 1);

        if (!$enabled) {
            return null;
        }

        $total = $this->getTotalReviewsProduct($product['product_id']);

        if (empty($total)) {
            return null;
        }

        $query = $this->getFilterQuery();
        $limit = $this->setPager($total, $query, $per_page);
        $pager = $this->getPager();

        $reviews = $this->getReviewsProduct($limit, $product);

        $options = array(
            'pager' => $pager,
            'product' => $product,
            'reviews' => $reviews,
            'query' => $this->query,
            'editable' => $editable
        );

        $html = $this->render('review/list', $options);
        $this->setData('reviews', $html);
        return null;
    }

    /**
     * Modifies an array of reviews
     * @param array $reviews
     * @param array $product
     * @return array
     */
    protected function prepareReviewsProduct(array $reviews, array $product)
    {
        if (empty($reviews)) {
            return array();
        }

        $users = array();
        foreach ($reviews as $review) {
            $users[] = $review['user_id'];
        }

        if (!empty($users)) {
            $ratings = $this->rating->getByUser($product['product_id'], $users);
        }

        foreach ($reviews as &$review) {

            $rating = 0;
            if (isset($ratings[$review['user_id']]['rating'])) {
                $rating = $ratings[$review['user_id']]['rating'];
            }

            $html = $this->render('common/rating/static', array('rating' => $rating));
            $review['rating_widget'] = $html;
        }

        return $reviews;
    }

    /**
     * Returns an array of reviews for the product
     * @param array $limit
     * @param array $product
     * @return array
     */
    protected function getReviewsProduct(array $limit, array $product)
    {
        $options = array(
            'status' => 1,
            'limit' => $limit,
            'user_status' => 1,
            'product_id' => $product['product_id']
        );

        $options += $this->query;
        $reviews = (array) $this->review->getList($options);

        return $this->prepareReviewsProduct($reviews, $product);
    }

    /**
     * Sets rendered rating widget
     * @param array $product
     */
    protected function setRatingWidgetProduct(array $product)
    {
        $rating = $this->rating->getByProduct($product['product_id'], true);

        $options = array(
            'product' => $product,
            'votes' => isset($rating['votes']) ? $rating['votes'] : 0,
            'rating' => isset($rating['rating']) ? $rating['rating'] : 0
        );

        $html = $this->render('common/rating/static', $options);
        $this->setData('rating', $html);
    }

    /**
     * Sets rendered "Add to cart form"
     * @param array $product
     */
    protected function setCartFormProduct(array $product)
    {
        $access = ($product['stock'] || empty($product['subtract']));

        $cart = array(
            'product' => $product,
            'token' => $this->token,
            'cart_access' => $access,
            'field_data' => $product['fields']
        );

        $html = $this->render('cart/add', $cart);
        $this->setData('cart_form', $html);
    }

    /**
     * Renders and displays product page
     */
    protected function outputIndexProduct()
    {
        $this->output('product/product');
    }

    /**
     * Sets title on the product page
     * @param array $product
     */
    protected function setTitleIndexProduct(array $product)
    {
        $this->setTitle($product['title'], false);
    }

    /**
     * Sets breadcrumbs on the product page
     * @param array $product
     */
    protected function setBreadcrumbIndexProduct(array $product)
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $categories = $this->geCategorytBreadcrumbsIndexProduct($product['category_id']);
        $this->setBreadcrumbs(array_merge($breadcrumbs, $categories));
    }

    /**
     * Builds an array of breadcrumb items containing all parent categories
     * @param integer $category_id
     * @param array $breadcrumbs
     */
    protected function buildCategoryBreadcrumbsIndexProduct($category_id,
            array &$breadcrumbs)
    {
        if (!empty($this->category_tree[$category_id]['parents'])) {

            $parent = reset($this->category_tree[$category_id]['parents']);
            $category = $this->category_tree[$category_id];

            $url = empty($category['alias']) ? "category/$category_id" : $category['alias'];

            $breadcrumb = array(
                'url' => $this->url($url),
                'text' => $category['title']
            );

            array_unshift($breadcrumbs, $breadcrumb);
            $this->buildCategoryBreadcrumbsIndexProduct($parent, $breadcrumbs);
        }
    }

    /**
     * Returns an array of results from self::buildCategoryBreadcrumbsIndexProduct()
     * @param integer $category_id
     * @return array
     */
    protected function geCategorytBreadcrumbsIndexProduct($category_id)
    {
        $breadcrumbs = array();
        $this->buildCategoryBreadcrumbsIndexProduct($category_id, $breadcrumbs);
        return $breadcrumbs;
    }

    /**
     * Sets Javascripts on the product page
     * @param array $product
     */
    protected function setJsIndexProduct(array $product)
    {
        $this->setJsSettings('product', $product);
    }

    /**
     * Sets block with recent products on the product page
     * @param array $product
     */
    protected function setRecentProduct(array $product)
    {
        $products = $this->getRecentProduct($product['product_id']);

        $options = array('products' => $products);
        $html = $this->render('product/blocks/recent', $options);

        $this->setData('recent', $html);
    }

    /**
     * Sets block with related products on the product page
     * @param array $product
     */
    protected function setRelatedProduct(array $product)
    {
        $products = $this->getRelatedProduct($product['product_id']);

        $options = array('products' => $products);
        $html = $this->render('product/blocks/related', $options);
        $this->setData('related', $html);
    }

    /**
     * Sets rendered product images
     * @param array $product
     */
    protected function setImagesProduct(array $product)
    {
        $imagestyle = $this->settings('image_style_product', 5);
        $this->setItemThumb($product, array('imagestyle' => $imagestyle));

        $options = array('product' => $product);
        $html = $this->render('product/images', $options);
        $this->setData('images', $html);
    }

    /**
     * Returns a total number of reviews for this product
     * @param integer $product_id
     * @return integer
     */
    protected function getTotalReviewsProduct($product_id)
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'user_status' => 1,
            'product_id' => $product_id
        );

        return (int) $this->review->getList($options);
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function getProduct($product_id)
    {
        $product = $this->product->get($product_id, $this->langcode);

        if (empty($product)) {
            $this->outputHttpStatus(404);
        }

        if ($product['store_id'] != $this->store_id) {
            $this->outputHttpStatus(404);
        }

        if (empty($product['status']) && !$this->access('product')) {
            $this->outputHttpStatus(403);
        }

        $product['fields'] = $this->getFieldsProduct($product);
        $this->setItemPrice($product);
        return $product;
    }

    /**
     * Returns an array of loaded related products
     * @param integer $product_id
     * @return array
     */
    protected function getRelatedProduct($product_id)
    {
        $limit = $this->config('product_related_limit', 12);

        $options = array(
            'status' => 1,
            'limit' => array(0, $limit),
            'store_id' => $this->store_id
        );

        $products = (array) $this->product->getRelated($product_id, true, $options);
        return $this->prepareProducts($products, $options);
    }

    /**
     * Returns an array of loaded recent products
     * @param integer $product_id
     * @return array
     */
    protected function getRecentProduct($product_id)
    {
        $limit = $this->config('product_recent_limit', 12);
        $lifespan = $this->config('product_recent_cookie_lifespan', 31536000);
        $product_ids = $this->product->setViewed($product_id, $limit, $lifespan);

        $current = array_search($product_id, $product_ids);
        unset($product_ids[$current]); // Exclude the current product iD

        if (empty($product_ids)) {
            return array();
        }

        return $this->getProducts(array('product_id' => $product_ids));
    }

    /**
     * Returns an array of product fields
     * @param array $product
     * @return array
     */
    protected function getFieldsProduct(array $product)
    {
        $data = $this->product_class->getFieldData($product['product_class_id']);

        if (empty($product['field']['option'])) {
            return $data;
        }

        $imagestyle = $this->settings('image_style_option', 1);

        foreach ($product['field']['option'] as $field_id => $field_values) {

            if (empty($data['option'][$field_id])) {
                continue;
            }

            if (empty($data['option'][$field_id]['widget'] !== 'image')) {
                continue;
            }

            foreach ($field_values as $field_value_id) {
                $path = $data['option'][$field_id]['values'][$field_value_id]['path'];
                $options = array('path' => $path, 'imagestyle' => $imagestyle);
                $this->setItemThumb($data['option'][$field_id]['values'][$field_value_id], $options);
            }
        }

        return $data;
    }

}
