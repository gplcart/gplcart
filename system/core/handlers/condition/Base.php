<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\Handler,
    gplcart\core\Config;

/**
 * Parent class for condition handlers
 */
class Base extends Handler
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Compare two values using an operator
     * @param mixed $a
     * @param mixed $b
     * @param string $operator
     * @return boolean
     */
    protected function compare($a, $b, $operator)
    {
        settype($a, 'array');
        settype($b, 'array');

        if (in_array($operator, array('>=', '<=', '>', '<'))) {
            $a = reset($a);
            $b = reset($b);
        }

        switch ($operator) {
            case '>=':
                return $a >= $b;
            case '<=':
                return $a <= $b;
            case '>':
                return $a > $b;
            case '<':
                return $a < $b;
            case '=':
                return count(array_intersect($a, $b)) > 0;
            case '!=':
                return count(array_intersect($a, $b)) == 0;
        }

        return false;
    }

}
