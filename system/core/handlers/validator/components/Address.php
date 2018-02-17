<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\City as CityModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\State as StateModel;

/**
 * Provides methods to validate address data
 */
class Address extends Component
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
     * @param CityModel $city
     * @param AddressModel $address
     */
    public function __construct(CountryModel $country, StateModel $state, CityModel $city, AddressModel $address)
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
        $this->validateFieldsAddress();
        $this->validateUserIdAddress();
        $this->validateData();

        $this->unsetSubmitted('update');

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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('User');

        if (empty($value) || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            return true; // Anonymous user
        }

        $user = $this->user->get($value);

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
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $country = $this->country->get($value);
        $label = $this->translation->text('Country');

        if (empty($country)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a country state
     * @return boolean|null
     */
    protected function validateStateAddress()
    {
        $field = 'state_id';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('State');
        $format = $this->getCountryFormatAddress();

        if (empty($value) && !empty($format['state_id']['required'])) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (empty($value)) {
            return true;
        }

        $state = $this->state->get($value);

        if (empty($state)) {
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

        if ($this->isExcluded($field) || $this->isError('country')) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('City');
        $format = $this->getCountryFormatAddress();

        if (empty($value) && !empty($format['city_id']['required'])) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        // City ID can be either numeric ID or non-numeric human name
        if (is_numeric($value)) {
            if ($this->city->get($value)) {
                return true;
            }
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        if (empty($value)) {
            return true;
        }

        if (mb_strlen($value) > 255) {
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
            'name' => $value,
            'country' => $country,
            'state_id' => $state_id
        );

        foreach ((array) $this->city->getList($conditions) as $city) {
            if (strcasecmp($city['name'], $value) === 0) {
                $this->setSubmitted($field, $city['city_id']);
                break;
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
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (!in_array($value, $this->address->getTypes())) {
            $this->setErrorUnavailable($field, $this->translation->text('Type'));
            return false;
        }

        return true;
    }

    /**
     * Validates address fields
     */
    protected function validateFieldsAddress()
    {
        $this->validateFieldAddress('address_1');
        $this->validateFieldAddress('address_2');
        $this->validateFieldAddress('phone');
        $this->validateFieldAddress('middle_name');
        $this->validateFieldAddress('last_name');
        $this->validateFieldAddress('first_name');
        $this->validateFieldAddress('postcode');
        $this->validateFieldAddress('company');
        $this->validateFieldAddress('fax');
    }

    /**
     * Validates an address field associated with the country format
     * @param string $field
     * @return bool|null
     */
    protected function validateFieldAddress($field)
    {
        if ($this->isExcluded($field) || $this->isError('country')) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $format = $this->getCountryFormatAddress();

        if (empty($format[$field]['required'])) {
            return null;
        }

        if (!isset($value) || $value === '' || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $format[$field]['name'], 1, 255);
            return false;
        }

        return true;
    }

    /**
     * Returns the current country format
     * @return array
     */
    protected function getCountryFormatAddress()
    {
        $updating = $this->getUpdating();

        if (isset($updating['country_format'])) {
            return $updating['country_format'];
        }

        $field = 'country';
        $code = $this->getSubmitted($field);

        if (!isset($code)) {
            return $this->country->getDefaultFormat();
        }

        $country = $this->country->get($code);

        if (empty($country['format'])) {
            $this->setErrorUnavailable($field, $this->translation->text('Country'));
            return array();
        }

        return $country['format'];
    }

}
