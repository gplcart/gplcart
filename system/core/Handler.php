<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Container;

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
     * @param array $arguments
     * @return mixed
     */
    public static function call($handlers, $handler_id, $method,
            $arguments = array())
    {
        $handler = static::get($handlers, $handler_id, $method);

        if (!empty($handler)) {
            return call_user_func_array($handler, $arguments);
        }

        return false;
    }

    /**
     * Returns a handler
     * @param array $handlers
     * @param string $handler_id
     * @param string $method
     * @return boolean|array
     */
    public static function get($handlers, $handler_id, $method)
    {
        if (isset($handler_id)) {
            if (empty($handlers[$handler_id]['handlers'][$method])) {
                return false;
            }

            $handler = $handlers[$handler_id]['handlers'][$method];
        } else {
            if (empty($handlers['handlers'][$method])) {
                return false;
            }

            $handler = $handlers['handlers'][$method];
        }

        $instance = Container::instance($handler);

        if (empty($instance)) {
            return false;
        }

        $handler[0] = $instance;
        return $handler;
    }

}
