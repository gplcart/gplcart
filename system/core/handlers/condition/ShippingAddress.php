<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

/**
 * Provides methods to check shipping address conditions
 */
class ShippingAddress extends Address
{

    /**
     * Whether the shipping zone ID condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function zoneId(array $condition, array $data, $key = 'shipping_address')
    {
        return parent::zoneId($condition, $data, $key);
    }

    /**
     * Whether the country condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function countryCode(array $condition, array $data, $key = 'shipping_address')
    {
        return parent::countryCode($condition, $data, $key);
    }

    /**
     * Whether the state ID condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function stateId(array $condition, array $data, $key = 'shipping_address')
    {
        return parent::stateId($condition, $data, $key);
    }

}
