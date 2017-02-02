<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\trigger;

use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check order conditions
 */
class Order
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Constructor
     * @param ConditionModel $condition
     */
    public function __construct(ConditionModel $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Returns true if a number of usage condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function used(array $condition, array $data)
    {
        if (!isset($data['rule']['used'])) {
            return false;
        }

        $value = reset($condition['value']);
        return $this->condition->compareNumeric((int) $data['rule']['used'], (int) $value, $condition['operator']);
    }

    /**
     * Returns true if a shipping service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function shipping(array $condition, array $data)
    {
        if (!isset($data['data']['order']['shipping'])) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareString($data['data']['order']['shipping'], $value, $condition['operator']);
    }

    /**
     * Returns true if a payment service condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function payment(array $condition, array $data)
    {
        if (!isset($data['data']['order']['payment'])) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareString($data['data']['order']['payment'], $value, $condition['operator']);
    }

}
