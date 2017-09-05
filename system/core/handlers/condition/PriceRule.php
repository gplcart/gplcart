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
 * Provides methods to check price rule conditions
 */
class PriceRule extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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

        return $this->compare($data['rule']['used'], $condition['value'], $condition['operator']);
    }

}
