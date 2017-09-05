<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\City as CityModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\Address as AddressModel;
use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check shipping address conditions
 */
class Shipping extends BaseHandler
{

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * City model instance
     * @var \gplcart\core\models\City $city
     */
    protected $city;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * @param AddressModel $address
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     */
    public function __construct(AddressModel $address, CityModel $city,
            StateModel $state, CountryModel $country)
    {
        parent::__construct();

        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->address = $address;
    }

    /**
     * Returns true if shipping zone ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function zoneId(array $condition, array $data)
    {
        if (isset($data['data']['order']['shipping_address'])) {
            return $this->checkZoneIdByAddressId($condition, $data);
        }

        return $this->checkZoneIdByAddressData($condition, $data);
    }

    /**
     * Returns true if a country condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function countryCode(array $condition, array $data)
    {
        // Check form fields
        if (!empty($data['data']['address']['country'])) {
            $country = $data['data']['address']['country'];
            return $this->compare($country, $condition['value'], $condition['operator']);
        }

        if (empty($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address = $this->address->get($data['data']['order']['shipping_address']);

        if (empty($address['country'])) {
            return false;
        }

        return $this->compare($address['country'], $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a state ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function stateId(array $condition, array $data)
    {
        // Check form fields
        if (isset($data['data']['address']['state_id'])) {
            $state_id = $data['data']['address']['state_id'];
            return $this->compare($state_id, $condition['value'], $condition['operator']);
        }

        if (!isset($data['data']['order']['shipping_address'])) {
            return false;
        }

        $address = $this->address->get($data['data']['order']['shipping_address']);

        if (empty($address['state_id'])) {
            return false;
        }

        return $this->compare($address['state_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a state ID condition is met using an existing address
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    protected function checkZoneIdByAddressId($condition, $data)
    {
        $address = $this->address->get($data['data']['order']['shipping_address']);

        if (empty($address)) {
            return false;
        }

        $fields = array('country_zone_id', 'state_zone_id', 'city_zone_id');

        $ids = array();
        foreach ($fields as $field) {
            $ids[] = $address[$field];
        }

        return $this->compare($ids, $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a state ID condition is met using form fields
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    protected function checkZoneIdByAddressData($condition, $data)
    {
        if (empty($data['data']['order']['address'])) {
            return false;
        }

        $ids = $this->getAddressZoneId($data['data']['order']['address']);
        return $this->compare($ids, $condition['value'], $condition['operator']);
    }

    /**
     * Returns an array of zone IDs from address components
     * @param array $address
     * @return array
     */
    protected function getAddressZoneId(array $address)
    {
        $result = array();
        foreach (array('state_id', 'city_id', 'country') as $field) {

            if (empty($address[$field])) {
                continue;
            }

            if ($field === 'city_id') {
                $data = $this->city->get($address[$field]);
            } else if ($field === 'state_id') {
                $data = $this->state->get($address[$field]);
            } else if ($field === 'country') {
                $data = $this->country->get($address[$field]);
            }

            if (!empty($data['zone_id'])) {
                $result[] = $data['zone_id'];
            }
        }

        return $result;
    }

}
