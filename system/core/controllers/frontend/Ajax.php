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
    gplcart\core\models\Collection as CollectionModel,
    gplcart\core\models\CollectionItem as CollectionItemModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to AJAX operations
 */
class Ajax extends FrontendController
{

    /**
     * SKU model instance
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
     * @param SkuModel $sku
     * @param CityModel $city
     * @param CollectionModel $collection
     * @param CollectionItemModel $collection_item
     */
    public function __construct(SkuModel $sku, CityModel $city, CollectionModel $collection,
            CollectionItemModel $collection_item)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->city = $city;
        $this->collection = $collection;
        $this->collection_item = $collection_item;
    }

    /**
     * Main AJAX callback
     */
    public function responseAjax()
    {
        if (!$this->isAjax()) {
            $this->response->outputError403();
        }

        $action = $this->getPosted('action');

        if (empty($action)) {
            $this->outputJson(array('error' => $this->text('Missing handler')));
        }

        $this->outputJson(call_user_func(array($this, $action)));
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
            'status' => $this->getPosted('status'),
            'title_sku' => $this->getPosted('term'),
            'store_id' => $this->getPosted('store_id'),
            'limit' => array(0, $this->config('autocomplete_limit', 10))
        );

        $products = $this->sku->getList($options);

        $product_ids = array();
        foreach ($products as $product) {
            $product_ids[] = $product['product_id'];
        }

        foreach ($products as &$product) {
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemThumb($product, $this->image, array('entity' => 'product', 'entity_id' => $product_ids));
            $this->setItemRendered($product, array('item' => $product), array('template_item' => 'backend|content/product/suggestion'));
        }

        return $products;
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
            'email' => $this->getPosted('term'),
            'store_id' => $this->getPosted('store_id'),
            'limit' => array(0, $this->config('autocomplete_limit', 10)));

        return $this->user->getList($options);
    }

    /**
     * Returns an array of store categories
     * @return array
     */
    public function getStoreCategoriesAjax()
    {
        if (!$this->access('category')) {
            return array('error' => $this->text('No access'));
        }

        $store_id = $this->getPosted('store_id', $this->store->getDefault());
        return $this->category->getOptionListByStore($store_id);
    }

    /**
     * Toggles product options
     * @return array
     */
    public function switchProductOptionsAjax()
    {
        $product_id = $this->getPosted('product_id');
        $field_value_ids = $this->getPosted('values', array(), true, 'array');

        if (empty($product_id)) {
            return array();
        }

        $product = $this->product->get($product_id);
        $response = $this->sku->selectCombination($product, $field_value_ids);
        $response += $product;

        $this->setItemPriceCalculated($response, $this->product);
        $this->setItemPriceFormatted($response, $this->price, $this->current_currency);

        return $response;
    }

    /**
     * Returns the cart preview for the current user
     * @return array
     */
    public function getCartPreviewAjax()
    {
        $cart = $this->getCart();

        if (empty($cart['items'])) {
            return array();
        }

        $content = $this->prepareCart($cart);
        $limit = $this->config('cart_preview_limit', 5);

        $data = array('cart' => $content, 'limit' => $limit);
        return array('preview' => $this->render('cart/preview', $data, true));
    }

    /**
     * Returns an array of suggested collection entities
     * @return array
     */
    public function getCollectionItemAjax()
    {
        $term = $this->getPosted('term');
        $collection_id = $this->getPosted('collection_id');

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

        $options = array(
            'status' => 1,
            'title' => $term,
            'store_id' => $collection['store_id'],
            'limit' => array(0, $this->config('autocomplete_limit', 10))
        );

        return $this->collection_item->getListEntities($collection['type'], $options);
    }

    /**
     * Returns an array of cities for the given country and state ID
     * @return array
     */
    public function searchCityAjax()
    {
        $country = $this->getPosted('country');
        $state_id = $this->getPosted('state_id');

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
