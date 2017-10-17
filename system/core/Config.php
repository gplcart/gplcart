<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\exceptions\Database as DatabaseException;

/**
 * Contains methods to work with system configurations
 */
class Config
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Private system key
     * @var string
     */
    protected $key;

    /**
     * An array of configuration options
     * @var array
     */
    protected $config = array();

    /**
     * Whether the runtime configuration file exists
     * @var boolean
     */
    protected $exists = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        if (is_readable(GC_CONFIG_COMMON)) {

            $this->exists = true;
            $this->config = gplcart_config_get(GC_CONFIG_COMMON);

            $this->setDb();
            $this->config = array_merge($this->config, $this->select());
            $this->setKey();
        }

        date_default_timezone_set($this->get('timezone', 'Europe/London'));

        $this->setErrorReportingLevel();
        $this->setErrorHandlers();
    }

    /**
     * Sets a unique key
     */
    protected function setKey()
    {
        $this->key = $this->get('private_key', '');

        if (empty($this->key)) {
            $this->key = gplcart_string_random();
            $this->set('private_key', $this->key);
        }
    }

    /**
     * Sets the database
     */
    protected function setDb()
    {
        if (isset($this->db)) {
            return false;
        }

        if (empty($this->config['database'])) {
            throw new DatabaseException('Missing database settings');
        }

        $this->db = Container::get('gplcart\\core\\Database');
        $this->db->set($this->config['database']);
        return true;
    }

    /**
     * Sets a system error level
     */
    protected function setErrorReportingLevel()
    {
        $level = $this->get('error_level', 2);

        if ($level == 0) {
            error_reporting(0);
        } else if ($level == 1) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        } else if ($level == 2) {
            error_reporting(E_ALL);
        }
    }

    /**
     * Registers error handlers
     */
    protected function setErrorHandlers()
    {
        /* @var $logger \gplcart\core\Logger */
        $logger = Container::get('gplcart\\core\\Logger');
        $logger->setDb($this->db);

        register_shutdown_function(array($logger, 'shutdownHandler'));
        set_exception_handler(array($logger, 'exceptionHandler'));
        set_error_handler(array($logger, 'errorHandler'), error_reporting());
    }

    /**
     * Returns a value from an array of configuration options
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function get($key = null, $default = null)
    {
        if (!isset($key)) {
            return $this->config;
        }

        if (array_key_exists($key, $this->config)) {
            return $this->config[$key];
        }

        return $default;
    }

    /**
     * Select all or a single setting from the database
     * @param null|string $name
     * @param mixed $default
     * @return mixed
     */
    public function select($name = null, $default = null)
    {
        if (!$this->exists) {
            return isset($name) ? $default : array();
        }

        if (isset($name)) {
            $result = $this->db->fetch('SELECT * FROM settings WHERE id=?', array($name));
            if (empty($result)) {
                return $default;
            }
            if ($result['serialized']) {
                return unserialize($result['value']);
            }
            return $result['value'];
        }

        $results = $this->db->fetchAll('SELECT * FROM settings', array());

        $settings = array();
        foreach ($results as $result) {
            if ($result['serialized']) {
                $result['value'] = unserialize($result['value']);
            }
            $settings[$result['id']] = $result['value'];
        }

        return $settings;
    }

    /**
     * Saves a configuration value in the database (overrides defaults)
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $value)
    {
        if (empty($this->db) || empty($key) || !isset($value)) {
            return false;
        }

        $this->reset($key);

        $serialized = 0;
        if (is_array($value)) {
            $value = serialize($value);
            $serialized = 1;
        }

        $values = array(
            'id' => $key,
            'value' => $value,
            'created' => GC_TIME,
            'serialized' => $serialized
        );

        $this->db->insert('settings', $values);
        return true;
    }

    /**
     * Deletes a configuration value from the database
     * @param string $key
     * @return boolean
     */
    public function reset($key)
    {
        return (bool) $this->db->delete('settings', array('id' => $key));
    }

    /**
     * Returns a module configuration value
     * @param string $module_id
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function module($module_id, $key = null, $default = null)
    {
        $modules = $this->getModules();

        if (empty($modules[$module_id]['settings'])) {
            return $default;
        }

        if (!isset($key)) {
            return (array) $modules[$module_id]['settings'];
        }

        $value = gplcart_array_get($modules[$module_id]['settings'], $key);
        return isset($value) ? $value : $default;
    }

    /**
     * Returns the database class instance
     * @return \gplcart\core\Database
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Whether the runtime configuration file exists
     * @return boolean
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Returns a private key
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Whether the given token is valid
     * @param string $token
     * @return boolean
     */
    public function tokenValid($token)
    {
        return gplcart_string_equals($this->token(), (string) $token);
    }

    /**
     * Returns a token based on the current session iD
     * @return string
     */
    public function token()
    {
        return gplcart_string_encode(crypt(session_id(), $this->key()));
    }

    /**
     * Returns an array of all available modules
     * @param bool|null $cache
     * @return array
     */
    public function getModules($cache = null)
    {
        $modules = &gplcart_static('module.list');

        if (isset($modules)) {
            return $modules;
        }

        if (!isset($cache)) {
            $cache = $this->get('module_cache', 0);
        }

        if ($cache && $this->hasModuleCache()) {
            return $modules = gplcart_config_get(GC_CONFIG_MODULE);
        }

        $installed = $this->getInstalledModules();

        $modules = array();
        foreach (scandir(GC_MODULE_DIR) as $module_id) {

            if (!$this->validModuleId($module_id)) {
                continue;
            }

            $info = $this->getModuleInfo($module_id);

            if (empty($info['core'])) {
                continue;
            }

            $info['directory'] = $this->getModuleDirectory($module_id);

            $info += array('type' => 'module', 'name' => $module_id);

            // Do not override status set in module.json for locked modules
            if (isset($info['status']) && !empty($info['lock'])) {
                unset($installed[$module_id]['status']);
            }

            // Do not override weight set in module.json for locked modules
            if (isset($info['weight']) && !empty($info['lock'])) {
                unset($installed[$module_id]['weight']);
            }

            if (isset($installed[$module_id])) {
                $info['installed'] = true;
                $info = array_replace_recursive($info, $installed[$module_id]);
            }

            if (empty($info['status'])) {
                $modules[$module_id] = $info;
                continue;
            }

            $instance = $this->getModuleInstance($module_id);

            if ($instance instanceof Module) {
                $info['hooks'] = $this->getModuleHooks($instance);
                $info['class'] = get_class($instance);
            }

            $modules[$module_id] = $info;
        }

        gplcart_array_sort($modules);

        if ($cache) {
            gplcart_config_set(GC_CONFIG_MODULE, $modules);
        }

        return $modules;
    }

    /**
     * Clear module cache
     * @return boolean
     */
    public function clearModuleCache()
    {
        if ($this->hasModuleCache()) {
            chmod(GC_CONFIG_MODULE, 0644);
            gplcart_static_clear();
            return unlink(GC_CONFIG_MODULE);
        }

        return false;
    }

    /**
     * Whether module cache exists
     * @return bool
     */
    public function hasModuleCache()
    {
        return is_file(GC_CONFIG_MODULE);
    }

    /**
     * Returns a module data
     * @param string $module_id
     * @return array
     */
    public function getModule($module_id)
    {
        $modules = $this->getModules();
        return empty($modules[$module_id]) ? array() : $modules[$module_id];
    }

    /**
     * Returns a server path to the module
     * @param string $module_id
     * @return string
     */
    public function getModuleDirectory($module_id)
    {
        return GC_MODULE_DIR . "/$module_id";
    }

    /**
     * Returns an array of module JSON definition
     * @param string $module_id
     * @return array
     */
    public function getModuleInfo($module_id)
    {
        static $information = array();

        if (isset($information[$module_id])) {
            return $information[$module_id];
        }

        $file = GC_MODULE_DIR . "/$module_id/module.json";

        $decoded = null;
        if (is_file($file)) {
            $decoded = json_decode(file_get_contents($file), true);
        }

        if (is_array($decoded)) {
            // @todo - remove id key everywhere
            $decoded['id'] = $decoded['module_id'] = $module_id;
            $information[$module_id] = $decoded;
        } else {
            $information[$module_id] = array();
        }

        return $information[$module_id];
    }

    /**
     * Returns the module class instance
     * @param string $module_id
     * @return null|object
     */
    public function getModuleInstance($module_id)
    {
        $namespace = $this->getModuleClassNamespace($module_id);

        try {
            $instance = Container::get($namespace);
        } catch (\Exception $exc) {
            return null;
        }

        return $instance;
    }

    /**
     * Returns a string containing base namespace for the given module ID
     * @param string $module_id
     * @return string
     */
    public function getModuleBaseNamespace($module_id)
    {
        return "gplcart\\modules\\$module_id";
    }

    /**
     * Returns a namespaced class for the given module id
     * @param string $module_id
     * @return string
     */
    public function getModuleClassNamespace($module_id)
    {
        $class_name = $this->getModuleClassName($module_id);
        $base_namespace = $this->getModuleBaseNamespace($module_id);

        return "$base_namespace\\$class_name";
    }

    /**
     * Creates a module class name from the module ID
     * @param string $module_id
     * @return string
     */
    public function getModuleClassName($module_id)
    {
        return ucfirst(str_replace('_', '', $module_id));
    }

    /**
     * Returns an array of all installed modules from the database
     * @return array
     */
    public function getInstalledModules()
    {
        if (empty($this->db)) {
            return array();
        }

        $modules = &gplcart_static('module.installed.list');

        if (isset($modules)) {
            return $modules;
        }

        $sql = 'SELECT * FROM module';
        $options = array('unserialize' => 'settings', 'index' => 'module_id');

        $modules = $this->db->fetchAll($sql, array(), $options);
        return $modules;
    }

    /**
     * Returns an array of enabled modules
     * @return array
     */
    public function getEnabledModules()
    {
        return array_filter($this->getModules(), function ($module) {
            return !empty($module['status']);
        });
    }

    /**
     * Returns an array of methods which are hooks
     * @param object|string $class
     * @return array
     */
    public function getModuleHooks($class)
    {
        $hooks = array();
        foreach (get_class_methods($class) as $method) {
            if (strpos($method, 'hook') === 0) {
                $hooks[] = $method;
            }
        }

        return $hooks;
    }

    /**
     * Whether the module exists and enabled
     * @param string $module_id
     * @return boolean
     */
    public function isEnabledModule($module_id)
    {
        $modules = $this->getEnabledModules();
        return !empty($modules[$module_id]['status']);
    }

    /**
     * Whether the module installed, e.g exists in database
     * @param string $module_id
     * @return boolean
     */
    public function isInstalledModule($module_id)
    {
        $modules = $this->getInstalledModules();
        return isset($modules[$module_id]);
    }

    /**
     * Whether the module is locked
     * @param string $module_id
     * @return boolean
     */
    public function isLockedModule($module_id)
    {
        $info = $this->getModuleInfo($module_id);
        return !empty($info['lock']);
    }

    /**
     * Whether the module is installer
     * @param string $module_id
     * @return boolean
     */
    public function isInstallerModule($module_id)
    {
        $info = $this->getModuleInfo($module_id);
        return isset($info['type']) && $info['type'] === 'installer';
    }

    /**
     * Validates a module id
     * @param string $id
     * @return boolean
     */
    public function validModuleId($id)
    {
        if (in_array($id, array('core', 'gplcart'))) {
            return false;
        }

        return preg_match('/^[a-z][a-z0-9_]+$/', $id) === 1;
    }

}
