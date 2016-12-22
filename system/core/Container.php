<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use ReflectionClass;
use ReflectionException;

/**
 * Dependency injection container
 */
class Container
{

    /**
     * Instance storage
     * @var array
     */
    protected static $registry = array();

    /**
     * Override class map
     * @var array
     */
    protected static $override_config;

    /**
     * Instantiates and registers a class
     * @param string|array $class
     * @param array $arguments
     * @param boolean $share
     * @return object
     * @throws ReflectionException
     */
    public static function instance($class, array $arguments = array(),
            $share = true)
    {
        if (is_array($class)) {
            if (!is_callable($class)) {
                $method = implode('::', $class);
                throw new ReflectionException("$method is not callable");
            }

            $class = reset($class);
        }

        if (is_object($class)) {
            return $class; // TODO: register?
        }

        // Override class namespace
        if (!isset(static::$override_config) && is_readable(GC_CONFIG_OVERRIDE)) {
            static::$override_config = include GC_CONFIG_OVERRIDE;
        }

        if (isset(static::$override_config[$class])) {
            $override = end(static::$override_config[$class]);
            $class = $override;
        }

        $registered = static::registered($class);

        if ($share && is_object($registered)) {
            return $registered;
        }

        if (!class_exists($class)) {
            throw new ReflectionException("Class $class does not exist");
        }

        $reflection = new ReflectionClass($class);

        $constructor = $reflection->getConstructor();

        if (empty($constructor)) {
            $instance = new $class;
            return $share ? static::register($class, $instance) : $instance;
        }

        $parameters = $constructor->getParameters();

        if (empty($parameters)) {
            $instance = new $class;
            return $share ? static::register($class, $instance) : $instance;
        }

        $dependencies = array();
        foreach ($parameters as $parameter) {
            $parameter_class = $parameter->getClass();

            if (!empty($parameter_class)) {
                $dependencies[] = static::instance($parameter_class->getName());
            }
        }

        $instance = $reflection->newInstanceArgs($dependencies + $arguments);
        return static::register($class, $instance);
    }

    /**
     * Adds a class to the storage
     * @param string $namespace
     * @param object $instance
     * @return object
     */
    public static function register($namespace, $instance)
    {
        static::$registry[strtolower(trim($namespace, '\\'))] = $instance;
        return $instance;
    }

    /**
     * Removes a class(es) from the storage
     * @param null|string $class
     * @return boolean
     */
    public static function unregister($class = null)
    {
        if (!isset($class)) {
            static::$registry = array(); // Unregister all
            return false;
        }

        $key = strtolower(trim($class, '\\'));
        unset(static::$registry[$key]);
        return true;
    }

    /**
     * Returns a registered class instance
     * @param string $class
     * @return object
     */
    public static function registered($class)
    {
        $key = strtolower(trim($class, '\\'));

        if (isset(static::$registry[$key])) {
            return static::$registry[$key];
        }
    }

}
