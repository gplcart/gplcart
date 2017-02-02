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
 * Provides methods to check date/time conditions
 */
class Date
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
     * Returns true if a date condition is met
     * @param array $condition
     * @return boolean
     */
    public function date(array $condition)
    {
        $value = strtotime(reset($condition['value']));

        if (empty($value)) {
            return false;
        }

        return $this->condition->compareNumeric(GC_TIME, $value, $condition['operator']);
    }

}
