<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use ReflectionClass,
    ReflectionException;

/**
 * Dependency injection container
 */
class Container
{

    /**
     * An array of instances
     * @var array
     */
    protected static $instances = array();

    /**
     * Instantiates and registers a class
     * @param string|array $callable
     * @param array $arguments
     * @return object
     * @throws ReflectionException
     */
    public static function get($callable, array $arguments = array())
    {
        $class = static::getClass($callable);

        if (is_object($class)) {
            return $class;
        }

        static::overrideClass($class);

        $registered = static::registered($class, $arguments);

        if (is_object($registered)) {
            return $registered;
        }

        if (!class_exists($class)) {
            throw new ReflectionException("Class $class does not exist");
        }

        return static::getInstance($class, $arguments);
    }

    /**
     * Returns a registered instance using its namespace and arguments
     * @param string $namespace
     * @param array $arguments
     * @return object
     */
    protected static function getInstance($namespace, array $arguments = array())
    {
        $reflection = new ReflectionClass($namespace);

        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            $instance = new $namespace;
            static::register($namespace, $instance);
            return $instance;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            $instance = new $namespace;
            static::register($namespace, $instance);
            return $instance;
        }

        $dependencies = array();
        foreach ($parameters as $pos => $parameter) {
            $parameter_class = $parameter->getClass();

            if (empty($parameter_class)) {
                $dependencies[$pos] = array_key_exists($pos, $arguments) ? $arguments[$pos] : $parameter;
                continue;
            }

            $dependencies[$pos] = static::get($parameter_class->getName());
        }

        $instance = $reflection->newInstanceArgs($dependencies);
        static::register($namespace, $instance, $arguments);
        return $instance;
    }

    /**
     * Validates and extracts an object or namespace from a callable content
     * @param array|string|object $class
     * @return string|object
     * @throws ReflectionException
     */
    protected static function getClass($class)
    {
        if (!is_array($class)) {
            return $class;
        }

        if (!is_callable($class)) {
            throw new ReflectionException(implode('::', $class) . ' is not callable');
        }

        return reset($class);
    }

    /**
     * Tries to override a class namespace
     * @param string $namespace
     */
    protected static function overrideClass(&$namespace)
    {
        $map = static::getOverrideMap();

        if (isset($map[$namespace])) {
            $override = end($map[$namespace]);
            $namespace = $override;
        }
    }

    /**
     * Returns an array of class override map 
     * @return array
     */
    protected static function getOverrideMap()
    {
        static $map = null;

        if (isset($map)) {
            return $map;
        }

        if (is_readable(GC_CONFIG_OVERRIDE)) {
            $map = require GC_CONFIG_OVERRIDE;
            return $map;
        }

        return $map = array();
    }

    /**
     * Adds a class instance to the storage
     * @param string $namespace
     * @param object $instance
     * @param array $args
     * @return array
     */
    public static function register($namespace, $instance, array $args = array())
    {
        static::$instances[static::getKey($namespace, $args)] = $instance;
        return static::$instances;
    }

    /**
     * Removes one or all instances from the storage
     * @param null|string $namespace
     * @param array $args
     * @return array
     */
    public static function unregister($namespace = null, array $args = array())
    {
        if (isset($namespace)) {
            unset(static::$instances[static::getKey($namespace, $args)]);
            return static::$instances;
        } else {
            static::$instances = array();
            return array();
        }
    }

    /**
     * Returns a registered class instance
     * @param string $namespace
     * @param array $args
     * @return object|bool
     */
    public static function registered($namespace, array $args = array())
    {
        $key = static::getKey($namespace, $args);

        if (isset(static::$instances[$key])) {
            return static::$instances[$key];
        }

        return false;
    }

    /**
     * Makes an object key from its namespace and arguments
     * @param string $namespace
     * @param array $args
     * @return string
     */
    protected static function getKey($namespace, array $args = array())
    {
        $key = strtolower(trim($namespace, '\\'));

        if (empty($args)) {
            return $key;
        }

        sort($args);
        return $key . md5(serialize($args));
    }

}
