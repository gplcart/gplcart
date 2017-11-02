<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

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
     * An array of configuration
     * @var array
     */
    protected $config = array();

    /**
     * Whether the configuration initialized
     * @var boolean
     */
    protected $initialized;

    /**
     * Initialize the system configuration
     * @param string $file
     * @return bool
     */
    public function init($file = GC_FILE_CONFIG_COMPILED)
    {
        $this->initialized = false;

        if (is_file($file)) {
            $this->config = (array) gplcart_config_get($file);
            $this->setDb($this->config['database']);
            $this->config = array_merge($this->config, $this->select());
            $this->setKey();
            $this->setEnvironment();
            $this->initialized = true;
        }

        return $this->initialized;
    }

    /**
     * Configure environment
     */
    protected function setEnvironment()
    {
        $level = $this->get('error_level', 2);

        if ($level == 0) {
            error_reporting(0);
        } else if ($level == 1) {
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
        } else if ($level == 2) {
            error_reporting(E_ALL);
        }

        /* @var $logger \gplcart\core\Logger */
        $logger = Container::get('gplcart\\core\\Logger');
        $logger->setDb($this->db);

        register_shutdown_function(array($logger, 'shutdownHandler'));
        set_exception_handler(array($logger, 'exceptionHandler'));
        set_error_handler(array($logger, 'errorHandler'), error_reporting());

        date_default_timezone_set($this->get('timezone', 'Europe/London'));
    }

    /**
     * Sets the private key
     * @param null|string $key
     * @return string
     */
    public function setKey($key = null)
    {
        if (empty($key)) {
            $key = $this->get('private_key');
            if (empty($key)) {
                $key = gplcart_string_random();
                $this->set('private_key', $key);
            }
        } else {
            $this->set('private_key', $key);
        }

        return $this->key = $key;
    }

    /**
     * Sets the database
     * @param mixed $config
     * @return \gplcart\core\Database
     */
    public function setDb($config)
    {
        $this->db = Container::get('gplcart\\core\\Database');
        $this->db->set($config);
        return $this->db;
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
     * Select all or a single setting from the database
     * @param null|string $name
     * @param mixed $default
     * @return mixed
     */
    public function select($name = null, $default = null)
    {
        if (empty($this->db)) {
            return isset($name) ? $default : array();
        }

        if (isset($name)) {
            return $this->selectOne($name, $default);
        }

        return $this->selectAll();
    }

    /**
     * Select a single setting from the database
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    protected function selectOne($name, $default = null)
    {
        $result = $this->db->fetch('SELECT * FROM settings WHERE id=?', array($name));

        if (empty($result)) {
            return $default;
        }

        if (!empty($result['serialized'])) {
            return unserialize($result['value']);
        }

        return $result['value'];
    }

    /**
     * Select all settings from the database
     * @return array
     */
    protected function selectAll()
    {
        $results = $this->db->fetchAll('SELECT * FROM settings', array());

        $settings = array();
        foreach ($results as $result) {
            if (!empty($result['serialized'])) {
                $result['value'] = unserialize($result['value']);
            }
            $settings[$result['id']] = $result['value'];
        }

        return $settings;
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
     * Returns module setting(s)
     * @param string $module_id
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getFromModule($module_id, $key = null, $default = null)
    {
        $module = $this->getModule($module_id);

        if (empty($module['settings'])) {
            return $default;
        }

        if (!isset($key)) {
            return (array) $module['settings'];
        }

        $value = gplcart_array_get($module['settings'], $key);
        return isset($value) ? $value : $default;
    }

    /**
     * Returns a token based on the current session iD
     * @return string
     */
    public function getToken()
    {
        return gplcart_string_encode(crypt(session_id(), $this->getKey()));
    }

    /**
     * Returns an array of all available modules
     * @param bool|null $get_cached
     * @return array
     */
    public function getModules($get_cached = null)
    {
        if (!isset($get_cached)) {
            $get_cached = (bool) $this->get('module_cache', 0);
        }

        $modules = &gplcart_static("module.list.$get_cached");

        if (isset($modules)) {
            return $modules;
        }

        if ($get_cached) {
            $cache = gplcart_config_get(GC_FILE_CONFIG_COMPILED_MODULE);
            if (!empty($cache)) {
                return $modules = (array) $cache;
            }
        }

        $installed = $this->getInstalledModules();

        $modules = array();
        foreach ($this->scanModules() as $module_id => $info) {
            $modules[$module_id] = $this->prepareModuleInfo($module_id, $info, $installed);
        }

        gplcart_array_sort($modules);

        if ($get_cached) {
            gplcart_config_set(GC_FILE_CONFIG_COMPILED_MODULE, $modules);
        }

        return $modules;
    }

    /**
     * Returns an array of scanned modules
     * @param string $directory
     * @return array
     */
    public function scanModules($directory = GC_DIR_MODULE)
    {
        $modules = array();
        foreach (scandir($directory) as $module_id) {

            if (!$this->isValidModuleId($module_id)) {
                continue;
            }

            $info = $this->getModuleInfo($module_id);

            if (empty($info['core'])) {
                continue;
            }

            $modules[$module_id] = $info;
        }

        return $modules;
    }

    /**
     * Prepare module info
     * @param string $module_id
     * @param array $info
     * @param array $installed
     * @return array
     */
    protected function prepareModuleInfo($module_id, array $info, array $installed)
    {
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

        if (!empty($info['status'])) {
            $instance = $this->getModuleInstance($module_id);
            if ($instance instanceof Module) {
                $info['hooks'] = $this->getModuleHooks($instance);
                $info['class'] = get_class($instance);
            }
        }

        return $info;
    }

    /**
     * Clear module cache
     * @return boolean
     */
    public function clearModuleCache()
    {
        if (gplcart_config_delete(GC_FILE_CONFIG_COMPILED_MODULE)) {
            gplcart_static_clear();
            return true;
        }

        return false;
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
        return GC_DIR_MODULE . "/$module_id";
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

        $file = GC_DIR_MODULE . "/$module_id/module.json";

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
    public function isValidModuleId($id)
    {
        if (in_array($id, array('core', 'gplcart'))) {
            return false;
        }

        return preg_match('/^[a-z][a-z0-9_]+$/', $id) === 1;
    }

    /**
     * Whether the given token is valid
     * @param string $token
     * @return boolean
     */
    public function isValidToken($token)
    {
        return gplcart_string_equals($this->getToken(), (string) $token);
    }

    /**
     * Whether the configuration initialized
     * @return boolean
     */
    public function isInitialized()
    {
        return (bool) $this->initialized;
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
     * Returns the private key
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

}
