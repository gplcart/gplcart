<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use InvalidArgumentException;

/**
 * Provides methods to work with various system handlers
 */
class Handler
{

    /**
     * Call a handler
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public static function call($handlers, $handler_id, $method, $arguments = array())
    {
        try {
            $callback = static::get($handlers, $handler_id, $method);
            return call_user_func_array($callback, $arguments);
        } catch (\Exception $ex) {
            throw new InvalidArgumentException($ex->getMessage());
        }
    }

    /**
     * Returns a handler
     * @param array $handlers
     * @param string $handler_id
     * @param string $name
     * @return object|array
     * @throws InvalidArgumentException
     */
    public static function get($handlers, $handler_id, $name)
    {
        if (isset($handler_id)) {
            if (empty($handlers[$handler_id]['handlers'][$name])) {
                throw new InvalidArgumentException("No such handler name '$name'");
            }
            $handler = $handlers[$handler_id]['handlers'][$name];
        } else {
            if (empty($handlers['handlers'][$name])) {
                throw new InvalidArgumentException("No such handler name '$name'");
            }
            $handler = $handlers['handlers'][$name];
        }

        if (is_array($handler)) {
            if (is_callable($handler)) {
                $handler[0] = Container::get($handler[0]);
                return $handler;
            }
            throw new InvalidArgumentException(implode('::', $handler) . ' is not callable');
        } else if ($handler instanceof \Closure) {
            return $handler;
        }

        throw new InvalidArgumentException('Unexpected handler format');
    }

}
