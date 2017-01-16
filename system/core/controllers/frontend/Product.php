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
     * The current product
     * @var array
     */
    protected $data_product = array();

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
        $this->setProduct($product_id);

        $this->setMetaProduct();
        $this->setTitleIndexProduct();
        $this->setBreadcrumbIndexProduct();

        $this->setImagesProduct();
        $this->setRecentProduct();
        $this->setReviewsProduct();
        $this->setRelatedProduct();
        $this->setCartFormProduct();
        $this->setRatingWidgetProduct();

        $this->setHtmlFilter($this->data_product);

        $this->setData('product', $this->data_product);
        $this->setData('share', $this->renderShareWidget());

        $this->setJsIndexProduct();
        $this->outputIndexProduct();
    }

    /**
     * Set meta tags on the product page
     */
    protected function setMetaProduct()
    {
        $this->setMetaEntity($this->data_product);
    }

    /**
     * Sets list of reviews related to the product
     * @return null
     */
    protected function setReviewsProduct()
    {
        if (!$this->config('review_enabled', 1)) {
            return null;
        }

        $total = $this->getTotalReviewsProduct();

        if (empty($total)) {
            return null;
        }

        $query = $this->getFilterQuery();
        $per_page = (int) $this->config('review_limit', 5);
        $limit = $this->setPager($total, $query, $per_page);

        $options = array(
            'query' => $this->query,
            'pager' => $this->getPager(),
            'product' => $this->data_product,
            'reviews' => $this->getReviewsProduct($limit),
            'editable' => (bool) $this->config('review_editable', 1)
        );

        $html = $this->render('review/list', $options);
        $this->setData('reviews', $html);
    }

    /**
     * Modifies an array of reviews
     * @param array $reviews
     * @return array
     */
    protected function prepareReviewsProduct(array $reviews)
    {
        if (empty($reviews)) {
            return array();
        }

        $users = array();
        foreach ($reviews as $review) {
            $users[] = $review['user_id'];
        }

        if (!empty($users)) {
            $ratings = $this->rating->getByUser($this->data_product['product_id'], $users);
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
     * @return array
     */
    protected function getReviewsProduct(array $limit)
    {
        $options = array(
            'status' => 1,
            'limit' => $limit,
            'user_status' => 1,
            'product_id' => $this->data_product['product_id']
        );

        $options += $this->query;
        $reviews = (array) $this->review->getList($options);

        return $this->prepareReviewsProduct($reviews);
    }

    /**
     * Sets rendered rating widget
     */
    protected function setRatingWidgetProduct()
    {
        $rating = $this->rating->getByProduct($this->data_product['product_id'], true);

        $options = array(
            'product' => $this->data_product,
            'votes' => isset($rating['votes']) ? $rating['votes'] : 0,
            'rating' => isset($rating['rating']) ? $rating['rating'] : 0
        );

        $html = $this->render('common/rating/static', $options);
        $this->setData('rating', $html);
    }

    /**
     * Sets rendered "Add to cart form"
     */
    protected function setCartFormProduct()
    {
        $access = ($this->data_product['stock'] || empty($this->data_product['subtract']));

        $cart = array(
            'token' => $this->token,
            'cart_access' => $access,
            'product' => $this->data_product,
            'field_data' => $this->data_product['fields']
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
     */
    protected function setTitleIndexProduct()
    {
        $this->setTitle($this->data_product['title'], false);
    }

    /**
     * Sets breadcrumbs on the product page
     */
    protected function setBreadcrumbIndexProduct()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $categories = $this->geCategorytBreadcrumbsIndexProduct($this->data_product['category_id']);
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
     */
    protected function setJsIndexProduct()
    {
        $this->setJsSettings('product', $this->data_product);
    }

    /**
     * Sets block with recent products on the product page
     */
    protected function setRecentProduct()
    {
        $products = $this->getRecentProduct();

        $options = array('products' => $products);
        $html = $this->render('product/blocks/recent', $options);

        $this->setData('recent', $html);
    }

    /**
     * Sets block with related products on the product page
     */
    protected function setRelatedProduct()
    {
        $options = array('products' => $this->getRelatedProduct());
        $html = $this->render('product/blocks/related', $options);
        $this->setData('related', $html);
    }

    /**
     * Sets rendered product images
     */
    protected function setImagesProduct()
    {
        $imagestyle = $this->settings('image_style_product', 5);
        $this->setItemThumb($this->data_product, array('imagestyle' => $imagestyle));

        $options = array('product' => $this->data_product);
        $html = $this->render('product/images', $options);
        $this->setData('images', $html);
    }

    /**
     * Returns a total number of reviews for this product
     * @return integer
     */
    protected function getTotalReviewsProduct()
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'user_status' => 1,
            'product_id' => $this->data_product['product_id']
        );

        return (int) $this->review->getList($options);
    }

    /**
     * Loads a product from the database
     * @param integer $product_id
     * @return array
     */
    protected function setProduct($product_id)
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

        $this->data_product = $product;
        return $product;
    }

    /**
     * Returns an array of loaded related products
     * @return array
     */
    protected function getRelatedProduct()
    {
        $limit = $this->config('product_related_limit', 12);

        $options = array(
            'status' => 1,
            'limit' => array(0, $limit),
            'store_id' => $this->store_id
        );

        $products = (array) $this->product->getRelated($this->data_product['product_id'], true, $options);
        return $this->prepareProducts($products, $options);
    }

    /**
     * Returns an array of loaded recent products
     * @return array
     */
    protected function getRecentProduct()
    {
        $limit = $this->config('product_recent_limit', 12);
        $lifespan = $this->config('product_recent_cookie_lifespan', 31536000);
        $product_ids = $this->product->setViewed($this->data_product['product_id'], $limit, $lifespan);

        $current = array_search($this->data_product['product_id'], $product_ids);
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
