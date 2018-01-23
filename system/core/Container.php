<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use ReflectionClass;
use ReflectionException;

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
     * @param string $class
     * @return object
     * @throws ReflectionException
     */
    public static function get($class)
    {
        $key = strtolower($class);

        if (isset(static::$instances[$key])) {
            return static::$instances[$key];
        }

        static::override($class);

        if (!class_exists($class)) {
            throw new ReflectionException("Class $class does not exist");
        }

        $instance = static::getInstance($class);
        static::register($class, $instance);
        return $instance;
    }

    /**
     * Returns an instance using a class name
     * @param string $class
     * @return object
     */
    public static function getInstance($class)
    {
        $reflection = new ReflectionClass($class);
        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            return new $class;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            return new $class;
        }

        $dependencies = array();
        foreach ($parameters as $parameter) {
            $parameter_class = $parameter->getClass();
            $dependencies[] = static::get($parameter_class->getName());
        }

        return $reflection->newInstanceArgs($dependencies);
    }

    /**
     * Override a class namespace
     * @param string $class
     * @return string
     */
    protected static function override(&$class)
    {
        $map = gplcart_config_get(GC_FILE_CONFIG_COMPILED_OVERRIDE);

        if (isset($map[$class])) {
            $override = end($map[$class]);
            $class = $override;
        }

        return $class;
    }

    /**
     * Adds a class instance to the storage
     * @param string $class
     * @param object $instance
     * @return array
     */
    public static function register($class, $instance)
    {
        static::$instances[strtolower($class)] = $instance;
        return static::$instances;
    }

    /**
     * Removes one or all instances from the storage
     * @param null|string $class
     * @return array
     */
    public static function unregister($class = null)
    {
        if (isset($class)) {
            unset(static::$instances[strtolower($class)]);
            return static::$instances;
        }

        return static::$instances = array();
    }

    /**
     * Whether the namespace already registered
     * @param string $class
     * @return bool
     */
    public static function registered($class)
    {
        return isset(static::$instances[strtolower($class)]);
    }

}
