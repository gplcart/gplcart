<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\Route;
use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check URL conditions
 */
class Url
{

    /**
     * Route instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Constructor
     * @param ConditionModel $condition
     * @param Route $route
     */
    public function __construct(ConditionModel $condition, Route $route)
    {
        $this->route = $route;
        $this->condition = $condition;
    }

    /**
     * Returns true if route condition is met
     * @param array $condition
     * @return boolean
     */
    public function route(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $route = $this->route->getCurrent();

        if (empty($route['pattern'])) {
            return false;
        }

        return $this->condition->compareString($route['pattern'], (array) $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if path condition is met
     * @param array $condition
     * @return boolean
     */
    public function path(array $condition)
    {
        if (!in_array($condition['operator'], array('=', '!='))) {
            return false;
        }

        $path = $this->route->path();

        $found = false;
        foreach ((array) $condition['value'] as $pattern) {
            if (gplcart_parse_pattern($path, $pattern)) {
                $found = true;
            }
        }

        return ($condition['operator'] === '=') ? $found : !$found;
    }

}
