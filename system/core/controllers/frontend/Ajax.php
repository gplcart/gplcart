<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Sku as SkuModel,
    gplcart\core\models\City as CityModel,
    gplcart\core\models\Rating as RatingModel,
    gplcart\core\models\Collection as CollectionModel,
    gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to AJAX operations
 */
class Ajax extends FrontendController
{

    /**
     * Rating model instance
     * @var \gplcart\core\models\Rating $rating
     */
    protected $rating;

    /**
     * Sku model instance
     * @var \gplcart\core\models\Sku $sku
     */
    protected $sku;

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * @param RatingModel $rating
     * @param SkuModel $sku
     * @param CityModel $city
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(RatingModel $rating, SkuModel $sku,
            CityModel $city, CollectionModel $collection,
            CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->city = $city;
        $this->rating = $rating;
        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Main ajax callback
     */
    public function responseAjax()
    {
        if (!$this->request->isAjax()) {
            $this->response->error403();
        }

        $action = (string) $this->getPosted('action');

        if (empty($action)) {
            $this->response->json(array('error' => $this->text('Missing handler')));
        }

        $response = $this->{$action}();
        $this->response->json($response);
    }

    /**
     * Lookup callback methods
     * @param string $action
     * @param array $args
     * @return array
     */
    public function __call($action, $args)
    {
        if (is_callable(array($this, $action))) {
            return call_user_func_array(array($this, $action), $args);
        }

        return array('error' => $this->text('Missing handler'));
    }

    /**
     * Returns an array of products
     * @return array
     */
    public function getProductsAjax()
    {
        if (!$this->access('product')) {
            return array('error' => $this->text('No access'));
        }

        $options = array(
            'status' => $this->getPosted('status', null),
            'store_id' => $this->getPosted('store_id', null),
            'title' => (string) $this->getPosted('term', ''),
            'limit' => array(0, $this->config('autocomplete_limit', 10))
        );

        return $this->getProducts($options);
    }

    /**
     * Returns an array of users
     * @return array
     */
    public function getUsersAjax()
    {
        if (!$this->access('user')) {
            return array('error' => $this->text('No access'));
        }

        $options = array(
            'email' => (string) $this->getPosted('term', ''),
            'store_id' => $this->getPosted('store_id', null),
            'limit' => array(0, $this->config('autocomplete_limit', 10)));

        return $this->user->getList($options);
    }

    /**
     * Toggles product options
     * @return array
     */
    public function switchProductOptionsAjax()
    {
        $product_id = (int) $this->getPosted('product_id');
        $field_value_ids = (array) $this->getPosted('values');

        $product = $this->product->get($product_id);
        $response = $this->sku->selectCombination($product, $field_value_ids);
        $response += $product;

        $this->attachItemPriceCalculated($response);
        $this->attachItemPriceFormatted($response);

        return $response;
    }

    /**
     * Returns the cart preview for the current user
     * @return array
     */
    public function getCartPreviewAjax()
    {
        $cart = $this->cart();

        if (empty($cart['items'])) {
            return array();
        }

        $content = $this->prepareCart($cart);
        $limit = $this->config('cart_preview_limit', 5);

        $data = array('cart' => $content, 'limit' => $limit);
        return array('preview' => $this->render('cart/preview', $data));
    }

    /**
     * Returns an array of suggested collection entities
     * @return array
     */
    public function getCollectionItemAjax()
    {
        $term = (string) $this->getPosted('term');
        $collection_id = (int) $this->getPosted('collection_id');

        if (empty($term) || empty($collection_id)) {
            return array('error' => $this->text('An error occurred'));
        }

        $collection = $this->collection->get($collection_id);

        if (empty($collection)) {
            return array('error' => $this->text('An error occurred'));
        }

        if (!$this->access($collection['type'])) {
            return array('error' => $this->text('No access'));
        }

        $max = $this->config('autocomplete_limit', 10);
        $options = array('title' => $term, 'limit' => array(0, $max));

        return $this->collection_item->getSuggestions($collection, $options);
    }

    /**
     * Rates a product
     * @return array
     */
    public function rateAjax()
    {
        $stars = (int) $this->getPosted('stars', 0);
        $product_id = (int) $this->getPosted('product_id');

        if (empty($product_id) || empty($this->uid)) {
            return array('error' => $this->text('No access'));
        }

        $options = array(
            'rating' => $stars,
            'user_id' => $this->uid,
            'product_id' => $product_id
        );

        $added = $this->rating->set($options);

        if ($added) {
            return array('success' => 1);
        }

        return array('error' => $this->text('An error occurred'));
    }

    /**
     * Returns an array of cities for the given country and state ID
     * @return array
     */
    public function searchCityAjax()
    {
        $country = (string) $this->getPosted('country', '');
        $state_id = (string) $this->getPosted('state_id', '');

        if (empty($country) || empty($state_id)) {
            return array();
        }

        $conditions = array(
            'status' => 1,
            'state_status' => 1,
            'country_status' => 1,
            'country' => $country,
            'state_id' => $state_id,
        );

        return (array) $this->city->getList($conditions);
    }

}
