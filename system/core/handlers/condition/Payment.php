<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\Config;
use gplcart\core\models\City as CityModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\Address as AddressModel;
use gplcart\core\handlers\condition\Address as AddressBaseHandler;

/**
 * Provides methods to check payment address conditions
 */
class Payment extends AddressBaseHandler
{

    /**
     * @param Config $config
     * @param CityModel $city
     * @param StateModel $state
     * @param CountryModel $country
     * @param AddressModel $address
     */
    public function __construct(Config $config, CityModel $city, StateModel $state,
            CountryModel $country, AddressModel $address)
    {
        parent::__construct($config, $city, $state, $country, $address);
    }

    /**
     * Whether the payment zone ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function zoneId(array $condition, array $data)
    {
        return parent::zoneId($condition, $data, 'payment_address');
    }

    /**
     * Whether the country condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function countryCode(array $condition, array $data)
    {
        return parent::countryCode($condition, $data, 'payment_address');
    }

    /**
     * Whether the state ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function stateId(array $condition, array $data)
    {
        return parent::stateId($condition, $data, 'payment_address');
    }

}
