<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Route;
use gplcart\core\models\User as UserModel;
use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\models\Language as LanguageModel;

class Condition
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Zone model instance
     * @var \gplcart\core\models\Zone $zone
     */
    protected $zone;

    /**
     * Constructor
     * @param CurrencyModel $currency
     * @param PriceModel $price
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     * @param ProductModel $product
     * @param CategoryModel $category
     * @param UserRoleModel $role
     * @param AddressModel $address
     * @param CountryModel $country
     * @param StateModel $state
     * @param LanguageModel $language
     * @param ZoneModel $zone
     * @param UserModel $user
     * @param Route $route
     */
    public function __construct(CurrencyModel $currency, PriceModel $price,
            PaymentModel $payment, ShippingModel $shipping,
            ProductModel $product, CategoryModel $category, UserRoleModel $role,
            AddressModel $address, CountryModel $country, StateModel $state,
            LanguageModel $language, ZoneModel $zone, UserModel $user,
            Route $route
    )
    {
        $this->user = $user;
        $this->zone = $zone;
        $this->route = $route;
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
     * Validates zone ID
     * @param array $values
     * @param string $operator
     * @return boolean
     */
    public function shippingZoneId(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        $zone = $this->zone->get(reset($values));

        if (empty($zone)) {
            $vars = array('@name' => $this->language->text('Condition'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the route pattern
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function route(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        $routes = $this->route->getList();

        foreach ($values as $value) {
            if (empty($routes[$value])) {
                $vars = array('@name' => $this->language->text('Condition'));
                return $this->language->text('@name is unavailable', $vars);
            }
        }

        return true;
    }

    /**
     * Validates the path pattern
     * @param array $values
     * @param string $operator
     * @return boolean|string
     */
    public function path(array $values, $operator)
    {
        if (!in_array($operator, array('=', '!='))) {
            return $this->language->text('Unsupported operator');
        }

        foreach ($values as $value) {
            if (preg_match("~$value~", null) === false) {
                $vars = array('@field' => $this->language->text('Condition'));
                return $this->language->text('@field has invalid value', $vars);
            }
        }

        return true;
    }

    /**
     * Validates the date condition
     * @param array $values
     * @return boolean|string
     */
    public function date(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $timestamp = strtotime(reset($values));

        if (empty($timestamp) || $timestamp <= GC_TIME) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        return true;
    }

    /**
     * Validates the number of usage condition
     * @param array $values
     * @return boolean|string
     */
    public function used(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        if (ctype_digit(reset($values))) {
            return true;
        }

        $vars = array('@field' => $this->language->text('Condition'));
        return $this->language->text('@field has invalid value', $vars);
    }

    /**
     * Validates the price condition
     * @param array $values
     * @return boolean|string
     */
    public function price(array $values)
    {
        if (count($values) != 1) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $components = array_map('trim', explode('|', reset($values)));

        if (count($components) > 2) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        if (!is_numeric($components[0])) {
            $vars = array('@field' => $this->language->text('Price'));
            return $this->language->text('@field must be numeric', $vars);
        }

        if (empty($components[1])) {
            $components[1] = $this->currency->getDefault();
        } else if (!$this->currency->get($components[1])) {
            $vars = array('@name' => $this->language->text('Currency'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the product ID condition
     * @param array $values
     * @return boolean|string
     */
    public function productId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($product_id) {
            $product = $this->product->get($product_id);
            return isset($product['product_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Product'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the product category ID condition
     * @param array $values
     * @return boolean|string
     */
    public function categoryId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($category_id) {
            $category = $this->category->get($category_id);
            return isset($category['category_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Category'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the user ID condition
     * @param array $values
     * @return boolean|string
     */
    public function userId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($user_id) {
            $user = $this->user->get($user_id);
            return isset($user['user_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('User'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the role ID condition
     * @param array $values
     * @return boolean|string
     */
    public function userRole(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($role_id) {
            $role = $this->role->get($role_id);
            return isset($role['role_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Role'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the shipping method condition
     * @param array $values
     * @return boolean|string
     */
    public function shipping(array $values)
    {
        $exists = array_filter($values, function ($method_id) {
            return (bool) $this->shipping->get($method_id);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Shipping'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the payment method condition
     * @param array $values
     * @return boolean|string
     */
    public function payment(array $values)
    {
        $exists = array_filter($values, function ($method_id) {
            return (bool) $this->payment->get($method_id);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Payment'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the country code condition
     * @param array $values
     * @return boolean|string
     */
    public function country(array $values)
    {
        $exists = array_filter($values, function ($code) {
            $country = $this->country->get($code);
            return isset($country['code']);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Country'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the country state condition
     * @param array $values
     * @return boolean|string
     */
    public function state(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($state_id) {
            $state = $this->state->get($state_id);
            return isset($state['state_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('State'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
