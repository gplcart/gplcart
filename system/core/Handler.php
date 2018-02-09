<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use BadMethodCallException;
use OutOfRangeException;
use UnexpectedValueException;

/**
 * Provides methods to retrieve and execute handlers
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
     */
    public static function call($handlers, $handler_id, $method, $arguments = array())
    {
        $callback = static::get($handlers, $handler_id, $method);
        return call_user_func_array($callback, $arguments);
    }

    /**
     * Returns a handler
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $name
     * @return object|array
     * @throws BadMethodCallException
     * @throws UnexpectedValueException
     */
    public static function get($handlers, $handler_id, $name)
    {
        $callable = static::getCallable($handlers, $handler_id, $name);

        if (is_array($callable)) {

            if (is_callable($callable)) {
                $callable[0] = Container::get($callable[0]);
                return $callable;
            }

            throw new BadMethodCallException(implode('::', $callable) . ' is not callable');
        }

        if ($callable instanceof \Closure) {
            return $callable;
        }

        throw new UnexpectedValueException('Unexpected handler format');
    }

    /**
     * Returns a callable data from the handler array
     * @param array $handlers
     * @param string|null $handler_id
     * @param string $name
     * @return array|object
     * @throws OutOfRangeException
     */
    protected static function getCallable($handlers, $handler_id, $name)
    {
        if (isset($handler_id)) {

            if (empty($handlers[$handler_id]['handlers'][$name])) {
                throw new OutOfRangeException("Unknown handler ID $handler_id and/or method name $name");
            }

            return $handlers[$handler_id]['handlers'][$name];
        }

        if (empty($handlers['handlers'][$name])) {
            throw new OutOfRangeException("Unknown handler method name $name");
        }

        return $handlers['handlers'][$name];
    }

}
