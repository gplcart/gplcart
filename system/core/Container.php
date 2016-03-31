<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core;

use ReflectionClass;

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
     * TODO: rebuild argument
     * @param string $class
     * @param array $arguments
     * @param boolean $share
     * @return object
     */
    public static function instance($class, $arguments = array(), $share = true)
    {
        if (is_array($class)) {

            if (!is_callable($class)) {
                return (object) array();
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

        if ($share && $registered) {
            return $registered;
        }

        if (!class_exists($class)) {
            return (object) array();
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
     * Returns a registered class instance
     * @param string $class
     * @return object|boolean
     */
    protected static function registered($class)
    {
        $key = strtolower(trim($class, '\\'));
        return isset(static::$registry[$key]) ? static::$registry[$key] : false;
    }

    /**
     * Removes a class(es) from the storage
     * @param null|string $class
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

}
