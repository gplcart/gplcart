<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check price rule conditions
 */
class PriceRule
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
        return $this->condition->compare($data['rule']['used'], $condition['value'], $condition['operator']);
    }

}
