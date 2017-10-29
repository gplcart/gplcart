<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check order conditions
 */
class Order extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Whether the shipping service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shippingMethod(array $condition, array $data)
    {
        if (!isset($data['order']['shipping'])) {
            return false;
        }

        return $this->compare($data['order']['shipping'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the payment service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function paymentMethod(array $condition, array $data)
    {
        if (!isset($data['order']['payment'])) {
            return false;
        }

        return $this->compare($data['order']['payment'], $condition['value'], $condition['operator']);
    }

}
