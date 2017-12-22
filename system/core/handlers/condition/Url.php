<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\Route;
use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check URL conditions
 */
class Url extends BaseHandler
{

    /**
     * Route instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * @param Route $route
     */
    public function __construct(Route $route)
    {
        $this->route = $route;
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

        $route = $this->route->get();

        if ($route['pattern'] === '') {
            $route['pattern'] = '/'; // Fix front page
        }

        return $this->compare($route['pattern'], $condition['value'], $condition['operator']);
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

        $path = $this->route->getPath();

        $found = false;
        foreach ((array) $condition['value'] as $pattern) {
            if (gplcart_path_match($path, $pattern)) {
                $found = true;
            }
        }

        return $condition['operator'] === '=' ? $found : !$found;
    }

}
