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
     * @param string $namespace
     * @return object
     * @throws ReflectionException
     */
    public static function get($namespace)
    {
        static::override($namespace);

        $key = strtolower($namespace);
        if (isset(static::$instances[$key])) {
            return static::$instances[$key];
        }

        if (!class_exists($namespace)) {
            throw new ReflectionException("Class $namespace does not exist");
        }

        return static::getInstance($namespace);
    }

    /**
     * Returns a registered instance using its namespace and arguments
     * @param string $namespace
     * @return object
     */
    public static function getInstance($namespace)
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
        foreach ($parameters as $parameter) {
            $parameter_class = $parameter->getClass();
            $dependencies[] = static::get($parameter_class->getName());
        }

        $instance = $reflection->newInstanceArgs($dependencies);
        static::register($namespace, $instance);
        return $instance;
    }

    /**
     * Override a class namespace
     * @param string $namespace
     */
    protected static function override(&$namespace)
    {
        static $map = null;

        if (!isset($map) && is_file(GC_CONFIG_OVERRIDE)) {
            $map = require GC_CONFIG_OVERRIDE;
        }

        if (isset($map[$namespace])) {
            $override = end($map[$namespace]);
            $namespace = $override;
        }
    }

    /**
     * Adds a class instance to the storage
     * @param string $namespace
     * @param object $instance
     * @return array
     */
    public static function register($namespace, $instance)
    {
        static::$instances[strtolower($namespace)] = $instance;
        return static::$instances;
    }

    /**
     * Removes one or all instances from the storage
     * @param null|string $namespace
     * @return array
     */
    public static function unregister($namespace = null)
    {
        if (isset($namespace)) {
            unset(static::$instances[strtolower($namespace)]);
            return static::$instances;
        }

        return static::$instances = array();
    }

    /**
     * Whether the namepace already registered
     * @param string $namespace
     * @return bool
     */
    public static function registered($namespace)
    {
        return isset(static::$instances[strtolower($namespace)]);
    }

}
