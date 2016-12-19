<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;
use ReflectionException;

/**
 * Provides methods to work with various system handlers
 */
class Handler
{

    /**
     * Calls a handler
     * @param array $handlers
     * @param string $handler_id
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function call($handlers, $handler_id, $method, $args = array())
    {
        $handler = static::get($handlers, $handler_id, $method);

        if (empty($handler[0])) {
            return false;
        }

        return call_user_func_array($handler, $args);
    }

    /**
     * Returns a handler
     * @param array $handlers
     * @param string $handler_id
     * @param string $name
     * @return boolean|array
     */
    public static function get($handlers, $handler_id, $name)
    {
        if (isset($handler_id)) {
            if (empty($handlers[$handler_id]['handlers'][$name])) {
                return false;
            }

            $handler = $handlers[$handler_id]['handlers'][$name];
        } else {
            if (empty($handlers['handlers'][$name])) {
                return false;
            }

            $handler = $handlers['handlers'][$name];
        }

        $handler[0] = Container::instance($handler);
        return $handler;
    }

}
