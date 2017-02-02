<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\Zone as ZoneModel;
use gplcart\core\models\City as CityModel;
use gplcart\core\models\State as StateModel;
use gplcart\core\models\Country as CountryModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check address conditions
 */
class Address
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

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
     * 
     * @param ConditionModel $condition
     * @param AddressModel $address
     * @param ZoneModel $zone
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     */
    public function __construct(ConditionModel $condition,
            AddressModel $address, ZoneModel $zone, CityModel $city,
            StateModel $state, CountryModel $country)
    {
        $this->zone = $zone;
        $this->city = $city;
        $this->state = $state;
        $this->country = $country;
        $this->address = $address;
        $this->condition = $condition;
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

            if (isset($data['zone_id'])) {
                $result[] = $data['zone_id'];
            }
        }

        return $result;
    }

    /**
     * Returns true if shipping zone ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shippingZoneId(array $condition, array $data)
    {
        if (empty($data['data']['order']['address']) && empty($data['data']['order']['shipping_address'])) {
            return false;
        }

        // Filter out removed/disabled zones
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

        // First check selected existing address ID
        if (!empty($data['data']['order']['shipping_address'])) {

            $address = $this->address->get($data['data']['order']['shipping_address']);

            if (empty($address)) {
                return false;
            }

            foreach (array('country_zone_id', 'state_zone_id', 'city_zone_id') as $field) {
                $matched = $this->condition->compareNumeric($address[$field], $value, $condition['operator']);
                if ($matched) {
                    return true;
                }
            }
            return false;
        }

        // Check fields when adding a new address
        if (empty($data['data']['order']['address'])) {
            return false;
        }

        $zone_ids = $this->getAddressZoneId($data['data']['order']['address']);

        foreach ($zone_ids as $zone_id) {
            if ($this->condition->compareNumeric($zone_id, $value, $condition['operator'])) {
                return true;
            }
        }

        return false;
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
