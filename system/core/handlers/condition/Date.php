<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

/**
 * Provides methods to check date/time conditions
 */
class Date extends Base
{

    /**
     * Whether the date condition is met
     * @param array $condition
     * @return boolean
     */
    public function current(array $condition)
    {
        $value = strtotime(reset($condition['value']));
        return empty($value) ? false : $this->compare(GC_TIME, $value, $condition['operator']);
    }

}
