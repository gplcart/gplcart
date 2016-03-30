<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\pricerule;

use core\models\User;
use core\models\Product;
use core\models\Currency;
use core\models\PriceRule;

class Condition
{

    /**
     * Price rule model instance
     * @var \core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Constructor
     * @param PriceRule $pricerule
     * @param User $user
     * @param Currency $currency
     * @param Product $product
     */
    public function __construct(PriceRule $pricerule, User $user, Currency $currency, Product $product)
    {
        $this->user = $user;
        $this->product = $product;
        $this->currency = $currency;
        $this->pricerule = $pricerule;
    }

    /**
     * Returns true if a date condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function date(array $rule, array $condition, array $data)
    {
        $condition_value = reset($condition['value']);
        return $this->pricerule->compareNumeric(GC_TIME, (int) $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a number of usage condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function used(array $rule, array $condition, array $data)
    {
        $condition_value = reset($condition['value']);
        return $this->pricerule->compareNumeric((int) $rule['used'], (int) $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a cart total condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function cartTotal(array $rule, array $condition, array $data)
    {
        if (!isset($data['cart']['total']) || empty($data['cart']['currency'])) {
            return false;
        }

        $condition_value = explode('|', reset($condition['value']));
        $cart_currency = $data['cart']['currency'];
        $cart_subtotal = (int) $data['cart']['total'];
        $condition_currency = $cart_currency;
        $condition_operator = $condition['operator'];

        if (!empty($condition_value[1])) {
            $condition_currency = $condition_value[1];
        }

        $condition_price = $this->currency->convert((int) $condition_value[0], $condition_currency, $cart_currency);
        return $this->pricerule->compareNumeric($cart_subtotal, $condition_price, $condition_operator);
    }

    /**
     * Returns true if a product ID condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function productId(array $rule, array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($data['cart']['items'] as $item) {
            if ($this->pricerule->compareNumeric((int) $item['product_id'], $condition_value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product category ID condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $rule, array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($products as $product) {
            if ($this->pricerule->compareNumeric((int) $product['category_id'], $condition_value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product brand condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $rule, array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        foreach ($products as $product) {
            if ($this->pricerule->compareNumeric((int) $product['brand_category_id'], $condition_value, $condition['operator'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a user ID condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function userId(array $rule, array $condition, array $data)
    {
        $user_id = $this->user->id();

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->pricerule->compareNumeric($user_id, $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a user role condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function userRole(array $rule, array $condition, array $data)
    {
        $role_id = $this->user->roleId();

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->pricerule->compareNumeric($role_id, $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a shipping service condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shipping(array $rule, array $condition, array $data)
    {
        if (!isset($data['data']['order']['shipping'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->pricerule->compareString($data['data']['order']['shipping'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a payment service condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function payment(array $rule, array $condition, array $data)
    {
        if (!isset($data['data']['order']['payment'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->pricerule->compareString($data['data']['order']['payment'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a shipping address condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shippingAddressId(array $rule, array $condition, array $data)
    {
        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        return $this->pricerule->compareNumeric($data['data']['order']['shipping_address'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a country condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function country(array $rule, array $condition, array $data)
    {
        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        if (isset($data['data']['address']['country'])) {
            $country = $data['data']['address']['country'];
            return $this->pricerule->compareString($country, $condition_value, $condition['operator']);
        }

        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['order']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['country'])) {
            return false;
        }

        return $this->pricerule->compareString($address['country'], $condition_value, $condition['operator']);
    }

    /**
     * Returns true if a state condition is met
     * @param array $rule
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function state(array $rule, array $condition, array $data)
    {
        $condition_value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $condition_value = (int) reset($condition_value);
        }

        if (isset($data['data']['address']['state_id'])) {
            $country = $data['data']['address']['state_id'];
            return $this->pricerule->compareNumeric($country, $condition_value, $condition['operator']);
        }

        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address_id = $data['data']['order']['shipping_address'];
        $address = $this->address->get($address_id);

        if (empty($address['state_id'])) {
            return false;
        }

        return $this->pricerule->compareNumeric($address['state_id'], $condition_value, $condition['operator']);
    }

}
