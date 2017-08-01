<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Config;

/**
 * Provides methods to work with system hooks (event system)
 */
class Hook
{

    /**
     * Array of registered hooks
     * @var array
     */
    protected $hooks = array();

    /**
     * Array of invoked hooks
     * @var array
     */
    protected $called = array();

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    /**
     * Registers all hooks from all modules
     */
    public function attachAll()
    {
        foreach ($this->config->getEnabledModules() as $module) {
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
        $this->hooks[strtolower($method)][$class] = array($class, $method);
        return $this->hooks;
    }

    /**
     * Removes a hook
     * @param string $method
     * @param string $class
     * @return array
     */
    public function unregister($method, $class)
    {
        unset($this->hooks[strtolower($method)][$class]);
        return $this->hooks;
    }

    /**
     * Returns a hook data
     * @return array
     */
    public function getRegistered()
    {
        return $this->hooks;
    }

    /**
     * Returns an array of invoked hooks
     * @return array
     */
    public function getCalled()
    {
        return $this->called;
    }

    /**
     * Sets method has been called
     * @param string $method
     * @param string $namespace
     */
    protected function setCalled($method, $namespace)
    {
        $this->called[$method][$namespace] = array($namespace, $method);
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
    public function attach($hook, &$a = null, &$b = null, &$c = null, &$d = null,
            &$e = null)
    {
        if (strpos($hook, '|') !== false) {
            list($hook, $module_id) = explode('|', $hook, 2);
        }

        $method = $this->getMethod($hook);

        if (isset($module_id)) {
            $namespace = $this->config->getModuleClassNamespace($module_id);
            return $this->call($namespace, $method, $a, $b, $c, $d, $e);
        }

        if (empty($this->hooks[$method])) {
            return false;
        }

        foreach (array_keys($this->hooks[$method]) as $namespace) {
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
        try {
            $instance = Container::get(array($namespace, $method));
            $instance->{$method}($a, $b, $c, $d, $e);
            $this->setCalled($method, $namespace);
        } catch (\Exception $exc) {
            return false;
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

}
