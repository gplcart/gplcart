<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\handlers\condition\Address as AddressBaseHandler;

/**
 * Provides methods to check shipping address conditions
 */
class Shipping extends AddressBaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Whether the shipping zone ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function zoneId(array $condition, array $data)
    {
        return parent::zoneId($condition, $data, 'shipping_address');
    }

    /**
     * Whether the country condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function countryCode(array $condition, array $data)
    {
        return parent::countryCode($condition, $data, 'shipping_address');
    }

    /**
     * Whether the state ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function stateId(array $condition, array $data)
    {
        return parent::stateId($condition, $data, 'shipping_address');
    }

}
