<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\exceptions\ModuleException;

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
     * Sets method has been called
     * @param string $method
     * @param string $namespace
     */
    protected function setCalled($method, $namespace)
    {
        static::$called[$method][$namespace] = array($namespace, $method);
    }

    /**
     * Executes a hook
     * @param string $hook
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     * @param mixed $d
     * @param mixed $e
     * @return boolean
     */
    public function fire($hook, &$a = null, &$b = null, &$c = null, &$d = null,
            &$e = null)
    {
        $method = $this->getMethod($hook);

        if (empty(static::$hooks[$method])) {
            return false;
        }

        foreach (array_keys(static::$hooks[$method]) as $namespace) {
            $this->call($namespace, $method, $a, $b, $c, $d, $e);
        }

        return true;
    }

    /**
     * Calls a hook method
     * @param string $namespace
     * @param string $method
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     * @param mixed $d
     * @param mixed $e
     * @return boolean
     */
    protected function call($namespace, $method, &$a = null, &$b = null,
            &$c = null, &$d = null, &$e = null)
    {
        $instance = Container::instance(array($namespace, $method));

        if (empty($instance)) {
            return false;
        }

        try {
            $instance->{$method}($a, $b, $c, $d, $e);
            $this->setCalled($method, $namespace);
        } catch (ModuleException $exc) {
            echo $exc->getMessage();
        }

        return true;
    }

    /**
     * Returns a full method name for the hook
     * @param string $hook
     * @return string
     */
    public function getMethod($hook)
    {
        return 'hook' . strtolower(str_replace(".", "", $hook));
    }

    /**
     * Calls a particular module hook 
     * @param string $hook
     * @param string $module
     * @param mixed $a
     * @param mixed $b
     * @param mixed $c
     * @param mixed $d
     * @param mixed $e
     */
    public function fireModule($hook, $module, &$a = null, &$b = null,
            &$c = null, &$d = null, &$e = null)
    {
        $method = $this->getMethod($hook);

        if (empty(static::$hooks[$method])) {
            return false;
        }

        foreach (array_keys(static::$hooks[$method]) as $namespace) {
            if (strpos($namespace, "modules\\$module\\") === 0) {
                return $this->call($namespace, $method, $a, $b, $c, $d, $e);
            }
        }
    }

}
