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
 * Provides methods to check payment address conditions
 */
class Payment extends AddressBaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Whether the payment zone ID condition is met
     * @param array $condition
     * @param array $data
     * @param string $key
     * @return boolean
     */
    public function zoneId(array $condition, array $data, $key = 'payment_address')
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
    public function countryCode(array $condition, array $data, $key = 'payment_address')
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
    public function stateId(array $condition, array $data, $key = 'payment_address')
    {
        return parent::stateId($condition, $data, $key);
    }

}
