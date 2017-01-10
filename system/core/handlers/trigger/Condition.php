<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\trigger;

use gplcart\core\Route;
use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\User as UserModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Condition as ConditionModel;

class Condition
{

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param ConditionModel $condition
     * @param UserModel $user
     * @param CurrencyModel $currency
     * @param ProductModel $product
     * @param AddressModel $address
     * @param ZoneModel $zone
     * @param Route $route
     */
    public function __construct(ConditionModel $condition, UserModel $user,
            CurrencyModel $currency, ProductModel $product,
            AddressModel $address, ZoneModel $zone, Route $route)
    {
        $this->zone = $zone;
        $this->user = $user;
        $this->route = $route;
        $this->address = $address;
        $this->product = $product;
        $this->currency = $currency;
        $this->condition = $condition;
    }

    /**
     * Returns true if shipping zone ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shippingZoneId(array $condition, array $data)
    {
        if (!isset($data['data']['shipping_address'])) {
            return false;
        }

        $address = $this->address->get($data['data']['shipping_address']);

        if (empty($address)) {
            return false;
        }

        // Filter out removed/disabled condition zones 
        $value = array_filter((array) $condition['value'], function ($id) {
            $zone = $this->zone->get($id);
            return !empty($zone['status']);
        });

        if (empty($value)) {
            return false;
        }

        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        $fields = array('country_zone_id', 'state_zone_id', 'city_zone_id');

        foreach ($fields as $field) {

            $matched = $this->condition->compareNumeric($address[$field], $value, $condition['operator']);

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if route condition is met
     * @param array $condition
     * @return boolean
     */
    public function route(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $route = $this->route->getCurrent();

        if (empty($route['pattern'])) {
            return false;
        }

        return $this->condition->compareString($route['pattern'], (array) $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if path condition is met
     * @param array $condition
     * @return boolean
     */
    public function path(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $path = $this->route->path();

        $found = false;
        foreach ((array) $condition['value'] as $pattern) {
            if (gplcart_parse_pattern($path, $pattern)) {
                $found = true;
            }
        }

        return ($condition['operator'] === '=') ? $found : !$found;
    }

    /**
     * Returns true if a date condition is met
     * @param array $condition
     * @return boolean
     */
    public function date(array $condition)
    {
        $value = reset($condition['value']);
        return $this->condition->compareNumeric(GC_TIME, (int) $value, $condition['operator']);
    }

    /**
     * Returns true if a number of usage condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function used(array $condition, array $data)
    {
        if (!isset($data['rule']['used'])) {
            return false;
        }

        $value = reset($condition['value']);
        return $this->condition->compareNumeric((int) $data['rule']['used'], (int) $value, $condition['operator']);
    }

    /**
     * Returns true if a cart total condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function cartTotal(array $condition, array $data)
    {
        if (!isset($data['cart']['total']) || empty($data['cart']['currency'])) {
            return false;
        }

        $condition_value = explode('|', reset($condition['value']));
        $cart_currency = $data['cart']['currency'];
        $condition_currency = $cart_currency;

        if (!empty($condition_value[1])) {
            $condition_currency = $condition_value[1];
        }

        $value = $this->currency->convert((int) $condition_value[0], $condition_currency, $cart_currency);
        return $this->condition->compareNumeric((int) $data['cart']['total'], $value, $condition['operator']);
    }

    /**
     * Returns true if a product ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function productId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ($data['cart']['items'] as $item) {
            if ($this->condition->compareNumeric((int) $item['product_id'], $value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product category ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        if (empty($product_ids)) {
            return false;
        }

        $args = array('product_id' => $product_ids, 'status' => 1);
        $products = $this->product->getList($args);

        if (empty($products)) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ((array) $products as $product) {

            $matched = $this->condition->compareNumeric((int) $product['category_id'], $value, $condition['operator']);

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product brand condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        if (empty($product_ids)) {
            return false;
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ((array) $products as $product) {

            $matched = $this->condition->compareNumeric((int) $product['brand_category_id'], $value, $condition['operator']);

            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a user ID condition is met
     * @param array $condition
     * @return boolean
     */
    public function userId(array $condition)
    {
        $user_id = (int) $this->user->getSession('user_id');

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareNumeric($user_id, $value, $condition['operator']);
    }

    /**
     * Returns true if a user role condition is met
     * @param array $condition
     * @return boolean
     */
    public function userRole(array $condition)
    {
        $role_id = $this->user->getSession('role_id');

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareNumeric($role_id, $value, $condition['operator']);
    }

    /**
     * Returns true if a shipping service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shipping(array $condition, array $data)
    {
        if (!isset($data['data']['order']['shipping'])) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareString($data['data']['order']['shipping'], $value, $condition['operator']);
    }

    /**
     * Returns true if a payment service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function payment(array $condition, array $data)
    {
        if (!isset($data['data']['order']['payment'])) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareString($data['data']['order']['payment'], $value, $condition['operator']);
    }

    /**
     * Returns true if a country condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function country(array $condition, array $data)
    {
        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        if (isset($data['data']['address']['country'])) {
            $country = $data['data']['address']['country'];
            return $this->condition->compareString($country, $value, $condition['operator']);
        }

        if (!isset($data['data']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['country'])) {
            return false;
        }

        return $this->condition->compareString($address['country'], $value, $condition['operator']);
    }

    /**
     * Returns true if a state condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function state(array $condition, array $data)
    {
        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        if (isset($data['data']['address']['state_id'])) {
            $country = $data['data']['address']['state_id'];
            return $this->condition->compareNumeric($country, $value, $condition['operator']);
        }

        if (!isset($data['data']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['state_id'])) {
            return false;
        }

        return $this->condition->compareNumeric($address['state_id'], $value, $condition['operator']);
    }

}
