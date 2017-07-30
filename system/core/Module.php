<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Container;

/**
 * Parent class for modules
 */
abstract class Module
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hook = Container::get('gplcart\\core\\Hook');
        $this->config = Container::get('gplcart\\core\\Config');
        $this->db = $this->config->getDb();
    }

    /**
     * Returns a class instance
     * @param $string Either module ID or namespaced class name
     * @return object
     */
    protected function getInstance($string)
    {
        if (strpos($string, 'gplcart\\') === 0) {
            return Container::get($string);
        }

        return $this->config->getModuleInstance($string);
    }

    /**
     * Returns a class instance for the given module and model name
     * @param string $model
     * @param string|null $module_id
     * @return object
     */
    protected function getModel($model, $module_id = null)
    {
        if (!isset($module_id)) {
            return $this->getInstance("gplcart\\core\\models\\$model");
        }

        $basenamespace = $this->config->getModuleBaseNamespace($module_id);
        return $this->getInstance("$basenamespace\\models\\$model");
    }

    /**
     * Returns a class instance for the given module and handler name
     * @param string $helper
     * @param string|null $module_id
     * @return object
     */
    protected function getHelper($helper, $module_id = null)
    {
        if (!isset($module_id)) {
            return $this->getInstance("gplcart\\core\\helpers\\$helper");
        }

        $basenamespace = $this->config->getModuleBaseNamespace($module_id);
        return $this->getInstance("$basenamespace\\helpers\\$helper");
    }

    /**
     * Returns library instance
     * @return \gplcart\core\Library
     */
    protected function getLibrary()
    {
        return $this->getInstance('gplcart\\core\\Library');
    }

}
