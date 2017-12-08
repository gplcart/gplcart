<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Sku as SkuModel,
    gplcart\core\models\Review as ReviewModel,
    gplcart\core\models\Rating as RatingModel,
    gplcart\core\models\ProductView as ProductViewModel,
    gplcart\core\models\ProductClass as ProductClassModel;
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
     * Product view model instance
     * @var \gplcart\core\models\ProductView $product_view
     */
    protected $product_view;

    /**
     * SKU model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

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
     * An array of product data
     * @var array
     */
    protected $data_product = array();

    /**
     * @param ProductClassModel $product_class
     * @param ProductViewModel $product_view
     * @param SkuModel $sku
     * @param ReviewModel $review
     * @param RatingModel $rating
     */
    public function __construct(ProductClassModel $product_class, ProductViewModel $product_view,
            SkuModel $sku, ReviewModel $review, RatingModel $rating)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->review = $review;
        $this->rating = $rating;
        $this->product_view = $product_view;
        $this->product_class = $product_class;
    }

    /**
     * Displays the product page
     * @param integer $product_id
     */
    public function indexProduct($product_id)
    {
        $this->setProduct($product_id);

        $this->setMetaIndexProduct();
        $this->setTitleIndexProduct();
        $this->setBreadcrumbIndexProduct();

        $this->setHtmlFilterIndexProduct();

        $this->setData('product', $this->data_product);

        $this->setDataSummaryIndexProduct();
        $this->setDataImagesIndexProduct();
        $this->setDataCartFormIndexProduct();
        $this->setDataRatingWidgetIndexProduct();

        $this->setDataDescriptionIndexProduct();
        $this->setDataAttributesIndexProduct();
        $this->setDataReviewsIndexProduct();
        $this->setDataRecentIndexProduct();
        $this->setDataRelatedIndexProduct();
        $this->setDataBundledIndexProduct();

        $this->setJsIndexProduct();
        $this->outputIndexProduct();
    }

    /**
     * Sets the description summary on the product page
     */
    protected function setDataSummaryIndexProduct()
    {
        $summary = '';
        if (!empty($this->data_product['description'])) {
            $exploded = $this->explodeText($this->data_product['description']);
            $summary = strip_tags($exploded[0]);
        }

        $this->setData('summary', $summary);
    }

    /**
     * Sets the "Add to cart" form
     */
    protected function setDataCartFormIndexProduct()
    {
        $data = array(
            'product' => $this->data_product,
            'share' => $this->getWidgetShare()
        );

        $this->setData('cart_form', $this->render('cart/add', $data, true));
    }

    /**
     * Sets attributes the product attributes data
     */
    protected function setDataAttributesIndexProduct()
    {
        $data = array('product' => $this->data_product);
        $this->setData('attributes', $this->render('product/attributes', $data));
    }

    /**
     * Sets the product images on the product page
     */
    protected function setDataImagesIndexProduct()
    {
        $html = $this->render('product/images', array('product' => $this->data_product));
        $this->setData('images', $html);
    }

    /**
     * Sets the product rating widget
     */
    protected function setDataRatingWidgetIndexProduct()
    {
        $data = array(
            'product' => $this->data_product,
            'rating' => $this->rating->getByProduct($this->data_product['product_id'])
        );

        $this->setData('rating', $this->render('common/rating/static', $data));
    }

    /**
     * Sets the product description
     */
    protected function setDataDescriptionIndexProduct()
    {
        $description = $this->data_product['description'];

        if (!empty($description)) {
            $exploded = $this->explodeText($description);
            if (!empty($exploded[1])) {
                $description = $exploded[1];
            }
        }

        $rendered = $this->render('product/description', array('description' => $description));
        $this->setData('description', $rendered);
    }

    /**
     * Sets the product reviews
     */
    protected function setDataReviewsIndexProduct()
    {
        if ($this->config('review_enabled', 1) && !empty($this->data_product['total_reviews'])) {

            $pager_options = array(
                'key' => 'rep',
                'total' => $this->data_product['total_reviews'],
                'limit' => (int) $this->config('review_limit', 5)
            );

            $pager = $this->getPager($pager_options);

            $data = array(
                'product' => $this->data_product,
                'pager' => $pager['rendered'],
                'reviews' => $this->getReviewsProduct($pager['limit'])
            );

            $this->setData('reviews', $this->render('review/list', $data, true));
        }
    }

    /**
     * Sets the recent products block
     */
    protected function setDataRecentIndexProduct()
    {
        $products = $this->getRecentProduct();

        if (!empty($products)) {

            $pager_options = array(
                'key' => 'pwp',
                'total' => count($products),
                'limit' => $this->config('product_view_pager_limit', 4)
            );

            $pager = $this->getPager($pager_options);

            if (!empty($pager['limit'])) {
                list($from, $to) = $pager['limit'];
                $products = array_slice($products, $from, $to, true);
            }

            $data = array('pager' => $pager['rendered'], 'products' => $products);
            $this->setData('recent', $this->render('product/recent', $data));
        }
    }

    /**
     * Sets the related products
     */
    protected function setDataRelatedIndexProduct()
    {
        $products = $this->getRelatedProduct();

        if (!empty($products)) {

            $pager_options = array(
                'key' => 'rlp',
                'total' => count($products),
                'limit' => $this->config('related_pager_limit', 4)
            );

            $pager = $this->getPager($pager_options);

            if (!empty($pager['limit'])) {
                list($from, $to) = $pager['limit'];
                $products = array_slice($products, $from, $to);
            }

            $data = array(
                'products' => $products,
                'pager' => $pager['rendered']
            );

            $this->setData('related', $this->render('product/related', $data));
        }
    }

    /**
     * Sets bundled products
     */
    protected function setDataBundledIndexProduct()
    {
        $this->setData('bundle', $this->render('product/bundle', array('product' => $this->data_product)));
    }

    /**
     * Set HTML filter on the product page
     */
    protected function setHtmlFilterIndexProduct()
    {
        $this->setHtmlFilter($this->data_product);
    }

    /**
     * Set meta tags on the product page
     */
    protected function setMetaIndexProduct()
    {
        $this->setMetaEntity($this->data_product);
    }

    /**
     * Prepare an array of reviews
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

        $ratings = null;
        if (!empty($users)) {
            $ratings = $this->rating->getByUser($this->data_product['product_id'], $users);
        }

        foreach ($reviews as &$review) {

            $rating = array('rating' => 0);
            if (isset($ratings[$review['user_id']]['rating'])) {
                $rating['rating'] = $ratings[$review['user_id']]['rating'];
            }

            $review['rating'] = $rating['rating'];
            $review['rating_formatted'] = $this->render('common/rating/static', array('rating' => $rating));
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
            'sort' => 'created',
            'order' => 'desc',
            'product_id' => $this->data_product['product_id']
        );

        $options += $this->query;
        $reviews = (array) $this->review->getList($options);
        return $this->prepareReviewsProduct($reviews);
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

        $categories = $this->getCategorytBreadcrumbsProduct($this->data_product['category_id']);
        $this->setBreadcrumbs(array_merge($breadcrumbs, $categories));
    }

    /**
     * Builds an array of breadcrumbs containing all parent categories
     * @param integer $category_id
     * @param array $breadcrumbs
     */
    protected function buildCategoryBreadcrumbsProduct($category_id, array &$breadcrumbs)
    {
        if (!empty($this->data_categories[$category_id]['parents'])) {

            $category = $this->data_categories[$category_id];
            $parent = reset($category['parents']);
            $url = empty($category['alias']) ? "category/$category_id" : $category['alias'];

            $breadcrumb = array(
                'url' => $this->url($url),
                'text' => $category['title']
            );

            array_unshift($breadcrumbs, $breadcrumb);
            $this->buildCategoryBreadcrumbsProduct($parent, $breadcrumbs);
        }
    }

    /**
     * Returns an array of results from self::buildCategoryBreadcrumbsIndexProduct()
     * @param integer $category_id
     * @return array
     */
    protected function getCategorytBreadcrumbsProduct($category_id)
    {
        $breadcrumbs = array();
        $this->buildCategoryBreadcrumbsProduct($category_id, $breadcrumbs);
        return $breadcrumbs;
    }

    /**
     * Sets JavaScripts on the product page
     */
    protected function setJsIndexProduct()
    {
        $this->setJsSettings('product', $this->data_product);
    }

    /**
     * Returns a total number of reviews for the product
     * @param array $product
     * @return integer
     */
    protected function getTotalReviewsProduct(array $product)
    {
        $options = array(
            'status' => 1,
            'count' => true,
            'user_status' => 1,
            'product_id' => $product['product_id']
        );

        return (int) $this->review->getList($options);
    }

    /**
     * Set a product data
     * @param integer $product_id
     */
    protected function setProduct($product_id)
    {
        $product = $this->product->get($product_id, array('language' => $this->langcode));

        if (empty($product)) {
            $this->outputHttpStatus(404);
        }

        if ($product['store_id'] != $this->store_id) {
            $this->outputHttpStatus(404);
        }

        if (empty($product['status']) && !$this->access('product')) {
            $this->outputHttpStatus(404);
        }

        $this->data_product = $this->prepareProduct($product);
    }

    /**
     * Prepare an array of product data
     * @param array $product
     * @return array
     */
    protected function prepareProduct(array $product)
    {
        $selected = $this->getSelectedCombinationProduct($product);
        $this->unshiftSelectedImageProduct($selected, $product);

        $this->setItemThumbProduct($product, $this->image);
        $this->setItemProductInComparison($product, $this->product_compare);
        $this->setItemProductBundle($product, $this->product, $this->image);
        $this->setItemProductInWishlist($product, $this->wishlist);

        $this->setItemPriceCalculated($selected, $this->product);
        $this->setItemPriceFormatted($selected, $this->price, $this->current_currency);

        $product['selected_combination'] = $selected;
        $product['total_reviews'] = $this->getTotalReviewsProduct($product);

        $this->setItemProductFields($product, $this->image, $this->product_class);
        return $product;
    }

    /**
     * Returns selected product combination
     * @param array $product
     * @return array
     */
    protected function getSelectedCombinationProduct(array $product)
    {
        $field_value_ids = array();
        if (!empty($product['default_field_values'])) {
            $field_value_ids = $product['default_field_values'];
        }

        $selected = $this->sku->selectCombination($product, $field_value_ids);
        $selected += $product;

        return $selected;
    }

    /**
     * Put default selected image on the first position
     * @param array $selected
     * @param array $product
     * @todo Replace with a trait
     */
    protected function unshiftSelectedImageProduct($selected, &$product)
    {
        if (isset($selected['combination']['file_id']) && isset($product['images'][$selected['combination']['file_id']])) {
            $image = $product['images'][$selected['combination']['file_id']];
            unset($product['images'][$selected['combination']['file_id']]);
            $product['images'] = array($image['file_id'] => $image) + $product['images'];
        }
    }

    /**
     * Render and output the product page
     */
    protected function outputIndexProduct()
    {
        $this->output('product/product');
    }

    /**
     * Returns an array of related products
     * @return array
     */
    protected function getRelatedProduct()
    {
        $options = array(
            'status' => 1,
            'store_id' => $this->store_id,
            'product_id' => $this->data_product['product_id'],
            'limit' => array(0, $this->config('related_limit', 12))
        );

        $product_ids = (array) $this->product->getRelated($options);

        $products = array();
        if (!empty($product_ids)) {
            $products = (array) $this->product->getList(array('product_id' => $product_ids));
        }

        return $this->prepareEntityItems($products, array('entity' => 'product'));
    }

    /**
     * Returns an array of recent products
     * @return array
     */
    protected function getRecentProduct()
    {
        $items = $this->product_view->set($this->data_product['product_id'], $this->cart_uid);

        $product_ids = array();
        foreach ((array) $items as $id => $item) {
            if ($item['product_id'] != $this->data_product['product_id']) {
                $product_ids[$id] = $item['product_id'];
            }
        }

        if (empty($product_ids)) {
            return array();
        }

        $products = $this->getProducts(array('product_id' => $product_ids));

        // Retain original order
        uksort($products, function($key1, $key2) use ($product_ids) {
            return (array_search($key1, $product_ids) < array_search($key2, $product_ids));
        });

        return $products;
    }

}
