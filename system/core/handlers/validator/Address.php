<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\City as ModelsCity;
use core\models\State as ModelsState;
use core\models\Country as ModelsCountry;
use core\models\Address as ModelsAddress;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate address data
 */
class Address extends BaseValidator
{
    /**
     * City model instance
     * @var \core\models\City $city
     */
    protected $city;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Constructor
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsAddress $address
     * @param ModelsCity $city
     */
    public function __construct(ModelsCountry $country, ModelsState $state,
            ModelsAddress $address, ModelsCity $city)
    {
        parent::__construct();

        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->address = $address;
    }

    /**
     * Performs full address data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function address(array &$submitted, array $options = array())
    {
        $this->validateAddress($submitted, $options);
        $this->validateCountryAddress($submitted, $options);
        $this->validateStateAddress($submitted, $options);
        $this->validateCityAddress($submitted, $options);
        $this->validateTypeAddress($submitted, $options);
        $this->validateTextFieldsAddress($submitted, $options);
        $this->validateUserId($submitted, $options);

        unset($submitted['format']);

        return $this->getResult();
    }

    /**
     * Validates an address to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateAddress(array &$submitted, array $options)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->address->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Address')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a country code
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateCountryAddress(array &$submitted, array $options)
    {
        $country_code = $this->getSubmitted('country', $submitted, $options);

        if (!empty($submitted['update']) && !isset($country_code)) {
            return null;
        }

        if (!isset($country_code)) {

            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Country')
            ));

            $this->setError('country', $error, $options);
            return false;
        }

        $country = $this->country->get($country_code);

        if (empty($country)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Country')));
            $this->setError('country', $error, $options);
            return false;
        }

        $submitted['format'] = $this->country->getFormat($country_code, true);
        return true;
    }

    /**
     * Validates a country state
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateStateAddress(array &$submitted, array $options)
    {
        $state_id = $this->getSubmitted('state_id', $submitted, $options);

        if (!isset($state_id)) {
            return null;
        }

        if (empty($submitted['format'])) {
            return null;
        }

        if (empty($state_id) && !empty($submitted['format']['state_id']['required'])) {

            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('State')
            ));

            $this->setError('state_id', $error, $options);
            return false;
        }

        if (!is_numeric($state_id)) {
            $options = array('@field' => $this->language->text('State'));
            $error = $this->language->text('@field must be numeric', $options);
            $this->setError('state_id', $error, $options);
            return false;
        }

        if (empty($state_id)) {
            return true;
        }

        $state = $this->state->get($state_id);

        if (empty($state)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('State')));
            $this->setError('state_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a city
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateCityAddress(array &$submitted, array $options)
    {
        $city_id = $this->getSubmitted('city_id', $submitted, $options);

        if (!empty($submitted['update']) && !isset($city_id)) {
            return null;
        }

        if (empty($submitted['format'])) {
            return null;
        }

        if (empty($city_id)) {

            if (!empty($submitted['format']['city_id']['required'])) {
                $error = $this->language->text('@field is required', array(
                    '@field' => $this->language->text('City')
                ));
                $this->setError('city_id', $error, $options);
                return false;
            }

            return true;
        }

        if (is_numeric($city_id)) {
            $city = $this->city->get($city_id);
            if (empty($city)) {
                $error = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('City')));
                $this->setError('city_id', $error, $options);
                return false;
            }
            return true;
        }

        if (mb_strlen($city_id) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('City'));
            $error = $this->language->text('@field must not be longer than @max characters', $options);
            $this->setError('city_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an address type
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateTypeAddress(array &$submitted, array $options)
    {
        $type = $this->getSubmitted('type', $submitted, $options);

        if (!empty($submitted['update']) && !isset($type)) {
            return null;
        }

        $types = $this->address->getTypes();

        if (isset($type) && !in_array($type, $types)) {
            $error = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Type')));
            $this->setError('type', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates address text fields
     * @param array $submitted
     * @param array $options
     * @return boolean|null
     */
    protected function validateTextFieldsAddress(&$submitted, $options)
    {
        $country_code = $this->getSubmitted('country', $submitted, $options);

        if (!isset($country_code) || $this->isError('country', $options)) {
            return null;
        }

        if (empty($submitted['format'])) {
            return null;
        }

        $format = $this->country->getFormat($country_code, true);

        $fields = array('address_1', 'address_2', 'phone', 'middle_name',
            'last_name', 'first_name', 'postcode', 'company', 'fax');

        foreach ($fields as $field) {

            if (empty($format[$field])) {
                continue;
            }

            if (!empty($submitted['update']) && !isset($submitted[$field])) {
                continue;
            }

            if (empty($format[$field]['required'])) {
                continue;
            }

            if (!isset($submitted[$field])//
                    || $submitted[$field] === ''//
                    || mb_strlen($submitted[$field]) > 255) {

                $options = array('@min' => 1, '@max' => 255, '@field' => $format[$field]['name']);
                $error = $this->language->text('@field must be @min - @max characters long', $options);
                $this->setError($field, $error, $options);
            }
        }

        return empty($error);
    }

}
