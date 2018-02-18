<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\CategoryGroup;
use gplcart\core\models\City;
use gplcart\core\models\Collection;
use gplcart\core\models\CollectionItem;
use gplcart\core\models\Sku;
use gplcart\core\traits\Category as CategoryTrait;

/**
 * Handles incoming requests and outputs data related to AJAX requests
 */
class Ajax extends Controller
{

    use CategoryTrait;

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
     * Category group class instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Collection item model instance
     * @var \gplcart\core\models\CollectionItem $collection_item
     */
    protected $collection_item;

    /**
     * Ajax constructor.
     * @param Sku $sku
     * @param City $city
     * @param Collection $collection
     * @param CollectionItem $collection_item
     * @param CategoryGroup $category_group
     */
    public function __construct(Sku $sku, City $city, Collection $collection,
                                CollectionItem $collection_item, CategoryGroup $category_group)
    {
        parent::__construct();

        $this->sku = $sku;
        $this->city = $city;
        $this->collection = $collection;
        $this->category_group = $category_group;
        $this->collection_item = $collection_item;
    }

    /**
     * Page callback
     * Entry point for all AJAX requests
     */
    public function responseAjax()
    {
        if (!$this->isAjax()) {
            $this->response->outputError403();
        }

        $action = $this->getPosted('action');

        if (empty($action)) {
            $this->outputJson(array('error' => $this->text('Unknown action')));
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

        $params = array(
            'status' => $this->getPosted('status'),
            'title_sku' => $this->getPosted('term'),
            'store_id' => $this->getPosted('store_id'),
            'limit' => array(0, $this->config('autocomplete_limit', 10))
        );

        $products = (array) $this->sku->getList($params);

        $product_ids = array();

        foreach ($products as $product) {
            $product_ids[] = $product['product_id'];
        }

        $options = array(
            'entity' => 'product',
            'entity_id' => $product_ids,
            'template_item' => 'backend|content/product/suggestion'
        );

        foreach ($products as &$product) {
            $this->setItemThumb($product, $this->image, $options);
            $this->setItemPriceFormatted($product, $this->price);
            $this->setItemRendered($product, array('item' => $product), $options);
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
            'email_like' => $this->getPosted('term'),
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

        $options = array(
            'store_id' => $this->getPosted('store_id', $this->store->getDefault())
        );

        return $this->getCategoryOptionsByStore($this->category, $this->category_group, $options);
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
        return array('preview' => $this->getCartPreview());
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

        $conditions = array(
            'status' => 1,
            'title' => $term,
            'store_id' => $collection['store_id'],
            'limit' => array(0, $this->config('autocomplete_limit', 10))
        );

        return $this->collection_item->getListEntities($collection['type'], $conditions);
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
