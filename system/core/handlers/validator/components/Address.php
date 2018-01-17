<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\City as CityModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\Address as AddressModel;
use gplcart\core\handlers\validator\Component as BaseComponentValidator;

/**
 * Provides methods to validate address data
 */
class Address extends BaseComponentValidator
{

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * @param CountryModel $country
     * @param StateModel $state
     * @param AddressModel $address
     * @param CityModel $city
     */
    public function __construct(CountryModel $country, StateModel $state, AddressModel $address,
            CityModel $city)
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateAddress();
        $this->validateCountryAddress();
        $this->validateStateAddress();
        $this->validateCityAddress();
        $this->validateTypeAddress();
        $this->validateTextFieldsAddress();
        $this->validateUserIdAddress();

        $this->unsetSubmitted('format');
        return $this->getResult();
    }

    /**
     * Validates an address to be updated
     * @return boolean
     */
    protected function validateAddress()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->address->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Address'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a user Id
     * @return boolean
     */
    protected function validateUserIdAddress()
    {
        $field = 'user_id';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $label = $this->translation->text('User');
        $user_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($user_id)) {
            return null;
        }

        if (empty($user_id) || mb_strlen($user_id) > 255) {
            $this->setErrorLengthRange($field, $label);
            return false;
        }

        if (!is_numeric($user_id)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a country code
     * @return boolean|null
     */
    protected function validateCountryAddress()
    {
        $field = 'country';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $label = $this->translation->text('Country');
        $code = $this->getSubmitted($field);

        if (!isset($code)) {
            $format = $this->country->getDefaultFormat();
            $this->setSubmitted('format', $format);
            return null;
        }

        $country = $this->country->get($code);

        if (empty($country['code'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $format = $this->country->getFormat($code, true);
        $this->setSubmitted('format', $format);
        return true;
    }

    /**
     * Validates a country state
     * @return boolean|null
     */
    protected function validateStateAddress()
    {
        $field = 'state_id';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $format = $this->getSubmitted('format');
        $state_id = $this->getSubmitted($field);
        $label = $this->translation->text('State');

        if (!isset($state_id) || empty($format)) {
            return null;
        }

        if (empty($state_id) && !empty($format['state_id']['required'])) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($state_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (empty($state_id)) {
            return true;
        }

        $state = $this->state->get($state_id);

        if (empty($state['state_id'])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a city
     * @return boolean|null
     */
    protected function validateCityAddress()
    {
        $field = 'city_id';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $city_id = $this->getSubmitted($field);
        $label = $this->translation->text('City');

        if ($this->isUpdating() && !isset($city_id)) {
            return null;
        }

        $format = $this->getSubmitted('format');

        if (empty($format)) {
            return null;
        }

        if (empty($city_id)) {
            if (!empty($format['city_id']['required'])) {
                $this->setErrorRequired($field, $label);
                return false;
            }
            return true;
        }

        // City ID can be either numeric ID or non-numeric human name
        if (is_numeric($city_id)) {
            if (!$this->city->get($city_id)) {
                $this->setErrorUnavailable($field, $label);
                return false;
            }
            return true;
        }

        if (mb_strlen($city_id) > 255) {
            $this->setErrorLengthRange($field, $label);
            return false;
        }

        // Try to convert human name to a numeric ID
        $country = $this->getSubmitted('country');
        $state_id = $this->getSubmitted('state_id');

        if (empty($country) || empty($state_id)) {
            return true;
        }

        $conditions = array(
            'name' => $city_id,
            'country' => $country,
            'state_id' => $state_id
        );

        $cities = (array) $this->city->getList($conditions);

        // Loop over results to find exact match,
        // because we search "name" using "LIKE" condition
        foreach ($cities as $city) {
            if (strcasecmp($city['name'], $city_id) === 0) {
                $this->setSubmitted($field, $city['city_id']);
                return true;
            }
        }

        return true;
    }

    /**
     * Validates an address type
     * @return boolean|null
     */
    protected function validateTypeAddress()
    {
        $field = 'type';
        $type = $this->getSubmitted($field);

        if (!isset($type)) {
            return null;
        }

        $types = $this->address->getTypes();

        if (!in_array($type, $types)) {
            $this->setErrorUnavailable($field, $this->translation->text('Type'));
            return false;
        }

        return true;
    }

    /**
     * Validates address text fields
     * @return boolean|null
     */
    protected function validateTextFieldsAddress()
    {
        $source_field = 'format';
        $format = $this->getSubmitted($source_field);

        if (empty($format) || $this->isError('country')) {
            return null;
        }

        $fields = array('address_1', 'address_2', 'phone', 'middle_name',
            'last_name', 'first_name', 'postcode', 'company', 'fax');

        $errors = 0;
        foreach ($fields as $field) {

            if (empty($format[$field])) {
                continue;
            }

            $submitted_value = $this->getSubmitted($field);

            if ($this->isUpdating() && !isset($submitted_value)) {
                continue;
            }

            if (empty($format[$field]['required'])) {
                continue;
            }

            if (!isset($submitted_value) || $submitted_value === '' || mb_strlen($submitted_value) > 255) {
                $errors++;
                $this->setErrorLengthRange($field, $format[$field]['name']);
            }
        }

        return empty($errors);
    }

}
