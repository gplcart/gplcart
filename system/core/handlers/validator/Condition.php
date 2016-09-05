<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Price as ModelsPrice;
use core\models\State as ModelsState;
use core\models\Payment as ModelsPayment;
use core\models\Product as ModelsProduct;
use core\models\Address as ModelsAddress;
use core\models\Country as ModelsCountry;
use core\models\Category as ModelsCategory;
use core\models\Currency as ModelsCurrency;
use core\models\Shipping as ModelsShipping;
use core\models\UserRole as ModelsUserRole;
use core\models\Language as ModelsLanguage;

class Condition
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Payment model instance
     * @var \core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
     * @param ModelsCurrency $currency
     * @param ModelsPrice $price
     * @param ModelsPayment $payment
     * @param ModelsShipping $shipping
     * @param ModelsProduct $product
     * @param ModelsCategory $category
     * @param ModelsUserRole $role
     * @param ModelsAddress $address
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsCurrency $currency, ModelsPrice $price,
            ModelsPayment $payment, ModelsShipping $shipping,
            ModelsProduct $product, ModelsCategory $category,
            ModelsUserRole $role, ModelsAddress $address,
            ModelsCountry $country, ModelsState $state, ModelsLanguage $language)
    {
        $this->role = $role;
        $this->price = $price;
        $this->state = $state;
        $this->address = $address;
        $this->product = $product;
        $this->payment = $payment;
        $this->country = $country;
        $this->shipping = $shipping;
        $this->currency = $currency;
        $this->category = $category;
        $this->language = $language;
    }

    /**
     * Validates the route pattern
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function route($key, $operator, array &$values, array $data)
    {
        if (in_array($operator, array('=', '!='))) {
            return true;
        }

        return $this->language->text('Supported operators: %operators', array('%operators' => '= !='));
    }

    /**
     * Validates the path pattern
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function path($key, $operator, array &$values, array $data)
    {
        if (in_array($operator, array('=', '!='))) {
            return true;
        }

        return $this->language->text('Supported operators: %operators', array('%operators' => '= !='));
    }

    /**
     * Validates the date condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function date($key, $operator, array &$values, array $data)
    {
        if (count($values) !== 1) {
            return false;
        }

        $timestamp = strtotime(reset($values));

        if (empty($timestamp)) {
            return $this->language->text('Date is not valid English textual datetime');
        }

        $values = array($timestamp);
        return true;
    }

    /**
     * Validates the number of usage condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function used($key, $operator, array &$values, array $data)
    {
        if (count($values) != 1) {
            return $this->language->text('Only one parameter allowed');
        }

        $value = reset($values);

        if (is_numeric($value)) {
            $values = array((int) $value);
            return true;
        }

        return false;
    }

    /**
     * Validates the price condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function price($key, $operator, array &$values, array $data)
    {
        if (count($values) != 1) {
            return $this->language->text('Only one parameter allowed');
        }

        $components = array_map('trim', explode('|', reset($values)));

        if (count($components) > 2) {
            return $this->language->text('Only one delimiter | allowed');
        }

        if (!is_numeric($components[0])) {
            return $this->language->text('Price must be numeric');
        }

        $price = $components[0];

        if (isset($components[1])) {
            $currency = $components[1];
            if (!$this->currency->get($currency)) {
                return $this->language->text('Unknown currency');
            }
        }

        if (!isset($currency)) {
            $currency = $data['currency'];
        }

        $new_components = array($this->price->amount($price, $currency), $currency);
        $values = array(implode('|', $new_components));
        return true;
    }

    /**
     * Validates the product ID condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function productId($key, $operator, array &$values, array $data)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            return $this->language->text('Only numeric parameters allowed');
        }

        $exists = array_filter($values, function ($product_id) {
            $product = $this->product->get($product_id);
            return isset($product['product_id']);
        });

        if ($count != count($exists)) {
            return $this->language->text('Some products do not exist');
        }

        return true;
    }

    /**
     * Validates the product category ID condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function categoryId($key, $operator, array &$values, array $data)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            return $this->language->text('Only numeric parameters allowed');
        }

        $exists = array_filter($values, function ($category_id) {
            $category = $this->category->get($category_id);
            return isset($category['category_id']);
        });

        if ($count != count($exists)) {
            return $this->language->text('Some categories do not exist');
        }

        return true;
    }

    /**
     * Validates the user ID condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function userId($key, $operator, array &$values, array $data)
    {
        if (count($values) != count(array_filter($values, 'is_numeric'))) {
            return $this->language->text('Only numeric parameters allowed');
        }

        return true;
    }

    /**
     * Validates the role ID condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function userRole($key, $operator, array &$values, array $data)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            return $this->language->text('Only numeric parameters allowed');
        }

        $exists = array_filter($values, function ($role_id) {
            $role = $this->role->get($role_id);
            return isset($role['role_id']);
        });

        if ($count != count($exists)) {
            return $this->language->text('Some roles do not exist');
        }

        return true;
    }

    /**
     * Validates the shipping method condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function shipping($key, $operator, array &$values, array $data)
    {
        $exists = array_filter($values, function ($service) {
            return (bool) $this->shipping->getService($service);
        });

        if (count($values) != count($exists)) {
            return $this->language->text('Some shipping services do not exist');
        }

        return true;
    }

    /**
     * Validates the payment method condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function payment($key, $operator, array &$values, array $data)
    {
        $exists = array_filter($values, function ($service) {
            return (bool) $this->payment->getService($service);
        });

        if (count($values) != count($exists)) {
            return $this->language->text('Some payment services do not exist');
        }

        return true;
    }

    /**
     * Validates the shipping address ID condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function shippingAddressId($key, $operator, array &$values,
            array $data)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            return $this->language->text('Only numeric parameters allowed');
        }

        $exists = array_filter($values, function ($address_id) {
            $address = $this->address->get($address_id);
            return (isset($address['type']) && $address['type'] === 'shipping');
        });

        if ($count != count($exists)) {
            return $this->language->text('Some addresses do not exist');
        }

        return true;
    }

    /**
     * Validates the country code condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function country($key, $operator, array &$values, array $data)
    {
        $exists = array_filter($values, function ($code) {
            $country = $this->country->get($code);
            return isset($country['code']);
        });

        if (count($values) != count($exists)) {
            return $this->language->text('Some countries do not exist');
        }

        return true;
    }

    /**
     * Validates the country state condition
     * @param string $key
     * @param string $operator
     * @param array $values
     * @param array $data
     * @return boolean|string
     */
    public function state($key, $operator, array &$values, array $data)
    {
        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count != count($ids)) {
            return $this->language->text('Only numeric parameters allowed');
        }

        $exists = array_filter($values, function ($state_id) {
            $state = $this->state->get($state_id);
            return isset($state['state_id']);
        });

        if ($count != count($exists)) {
            return $this->language->text('Some country states do not exist');
        }

        return true;
    }

}
