<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\exceptions\SystemLogical;

/**
 * Provides methods to work with system hooks (event system)
 */
class Hook
{

    /**
     * Array of registered hooks
     * @var array
     */
    protected static $hooks = array();

    /**
     * Array of invoked hooks
     * @var array
     */
    protected static $called = array();

    /**
     * Registers modules hooks
     * @param array $modules
     */
    public function modules(array $modules)
    {
        foreach ($modules as $module) {
            if (empty($module['hooks'])) {
                continue;
            }

            foreach ($module['hooks'] as $method) {
                $this->register($method, $module['class']);
            }
        }
    }

    /**
     * Registers a single hook
     * @param string $method
     * @param string $class
     * @return array
     */
    public function register($method, $class)
    {
        static::$hooks[strtolower($method)][$class] = array($class, $method);
        return static::$hooks;
    }

    /**
     * Removes a hook
     * @param string $method
     * @param string $class
     * @return array
     */
    public function unregister($method, $class)
    {
        unset(static::$hooks[strtolower($method)][$class]);
        return static::$hooks;
    }

    /**
     * Returns a hook data
     * @return array
     */
    public function getRegistered()
    {
        return static::$hooks;
    }

    /**
     * Returns an array of invoked hooks
     * @return array
     */
    public function getCalled()
    {
        return static::$called;
    }

    /**
     * Executes a hook
     * @param string $hook
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     * @return boolean
     */
    public function fire($hook, &$a = null, &$b = null, &$c = null)
    {
        $method = 'hook' . strtolower(str_replace(".", "", $hook));

        if (empty(static::$hooks[$method])) {
            return false;
        }

        foreach (array_keys(static::$hooks[$method]) as $namespace) {
            $instance = Container::instance(array($namespace, $method));

            if (empty($instance)) {
                continue;
            }

            try {
                $instance->{$method}($a, $b, $c);
                static::$called[$method][$namespace] = array($namespace, $method);
            } catch (SystemLogical $e) {
                echo $e->getMessage();
            }
        }

        return true;
    }

}
