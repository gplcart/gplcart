<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\pricerule;

use core\Config;
use core\models\Price;
use core\models\State;
use core\models\Payment;
use core\models\Product;
use core\models\Address;
use core\models\Country;
use core\models\Category;
use core\models\Currency;
use core\models\Shipping;
use core\models\UserRole;

class Validate
{

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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Currency $currency
     * @param Price $price
     * @param Payment $payment
     * @param Shipping $shipping
     * @param Product $product
     * @param Category $category
     * @param UserRole $role
     * @param Address $address
     * @param Country $country
     * @param State $state
     * @param Config $config
     */
    public function __construct(Currency $currency, Price $price, Payment $payment, Shipping $shipping, Product $product, Category $category, UserRole $role, Address $address, Country $country, State $state, Config $config)
    {
        $this->role = $role;
        $this->price = $price;
        $this->state = $state;
        $this->config = $config;
        $this->address = $address;
        $this->product = $product;
        $this->payment = $payment;
        $this->country = $country;
        $this->shipping = $shipping;
        $this->currency = $currency;
        $this->category = $category;
        $this->db = $this->config->getDb();
    }

    /**
     * Validates the date condition
     * @param array $values
     * @return boolean
     */
    public function date(&$values)
    {
        if (count($values) !== 1) {
            return false;
        }

        $timestamp = strtotime(reset($values));

        if (!empty($timestamp)) {
            $values = array($timestamp);
            return true;
        }

        return false;
    }

    /**
     * Validates the number of usage condition
     * @param array $values
     * @return boolean
     */
    public function used(&$values)
    {
        if (count($values) !== 1) {
            return false;
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
     * @param array $values
     * @return boolean
     */
    public function price(&$values, $rule)
    {
        if (count($values) !== 1) {
            return false;
        }

        $components = array_map('trim', explode('|', reset($values)));

        if (count($components) > 2) {
            return false;
        }

        if (!is_numeric($components[0])) {
            return false;
        }

        $price = $components[0];

        if (isset($components[1])) {
            $currency = $components[1];
            if (!$this->currency->get($currency)) {
                return false;
            }
        }

        if (!isset($currency)) {
            $currency = $rule['currency'];
        }

        $new_components = array($this->price->amount($price, $currency), $currency);
        $values = array(implode('|', $new_components));
        return true;
    }

    /**
     * Validates the product ID condition
     * @param array $values
     * @return boolean
     */
    public function productId($values)
    {
        if (empty($values)) {
            return false;
        }

        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count !== count($ids)) {
            return false;
        }

        $exists = array_filter($values, function ($product_id) {
            $product = $this->product->get($product_id);
            return !empty($product['status']);
        });

        return ($count === count($exists));
    }

    /**
     * Validates the product category ID condition
     * @param array $values
     * @return boolean
     */
    public function categoryId($values)
    {
        if (empty($values)) {
            return false;
        }

        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count !== count($ids)) {
            return false;
        }

        $exists = array_filter($values, function ($category_id) {
            $category = $this->category->get($category_id);
            return !empty($category['status']);
        });

        return ($count === count($exists));
    }

    /**
     * Validates the user ID condition
     * @param array $values
     * @return boolean
     */
    public function userId($values)
    {
        if (empty($values)) {
            return false;
        }

        return (count($values) === count(array_filter($values, 'is_numeric')));
    }

    /**
     * Validates the role ID condition
     * @param array $values
     * @return boolean
     */
    public function userRole($values)
    {
        if (empty($values)) {
            return false;
        }

        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count !== count($ids)) {
            return false;
        }

        $exists = array_filter($values, function ($role_id) {
            $role = $this->role->get($role_id);
            return !empty($role['status']);
        });

        return ($count === count($exists));
    }

    /**
     * Validates the shipping method condition
     * @param array $values
     * @return boolean
     */
    public function shipping($values)
    {
        if (empty($values)) {
            return false;
        }

        $exists = array_filter($values, function ($service) {
            return (bool) $this->shipping->getService($service);
        });

        return (count($values) === count($exists));
    }

    /**
     * Validates the payment method condition
     * @param array $values
     * @return boolean
     */
    public function payment($values)
    {
        if (empty($values)) {
            return false;
        }

        $exists = array_filter($values, function ($service) {
            return (bool) $this->payment->getService($service);
        });

        return (count($values) === count($exists));
    }

    /**
     * Validates the shipping address ID condition
     * @param array $values
     * @return boolean
     */
    public function shippingAddressId($values)
    {
        if (empty($values)) {
            return false;
        }

        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count !== count($ids)) {
            return false;
        }

        $exists = array_filter($values, function ($address_id) {
            $address = $this->address->get($address_id);
            return (isset($address['type']) && $address['type'] === 'shipping');
        });

        return ($count === count($exists));
    }

    /**
     * Validates the country code condition
     * @param array $values
     * @return boolean
     */
    public function country($values)
    {
        if (empty($values)) {
            return false;
        }

        $exists = array_filter($values, function ($code) {
            $country = $this->country->get($code);
            return !empty($country['status']);
        });

        return (count($values) === count($exists));
    }

    /**
     * Validates the country state condition
     * @param array $values
     * @return boolean
     */
    public function state($values)
    {
        if (empty($values)) {
            return false;
        }

        $count = count($values);
        $ids = array_filter($values, 'is_numeric');

        if ($count !== count($ids)) {
            return false;
        }

        $exists = array_filter($values, function ($state_id) {
            $state = $this->state->get($state_id);
            return !empty($state['status']);
        });

        return ($count === count($exists));
    }
}
