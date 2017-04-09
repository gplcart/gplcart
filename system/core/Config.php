<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\exceptions\DatabaseException;

/**
 * Contains methods to work with system configurations
 */
class Config
{

    /**
     * PDO instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Private system key
     * @var string
     */
    protected $key;

    /**
     * Config array from config.php
     * @var array
     */
    protected $config = array();

    /**
     * Whether config.php exists
     * @var boolean
     */
    protected $exists = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * Returns a setting value
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
     * Returns a module setting value
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

        $value = gplcart_array_get_value($modules[$module_id]['settings'], $key);
        return isset($value) ? $value : $default;
    }

    /**
     * Sets a setting in the database
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function set($key, $value)
    {
        if (empty($key) || !isset($value)) {
            return false;
        }

        if (empty($this->db)) {
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
     * Deletes a setting from the database
     * @param string $key
     * @return boolean
     */
    public function reset($key)
    {
        $result = $this->db->delete('settings', array('id' => $key));
        return (bool) $result;
    }

    /**
     * Returns PDO instance
     * @return object
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * Sets database instance
     * @param object $db
     */
    public function setDb($db)
    {
        $this->db = $db;
    }

    /**
     * Returns true if config.php exists i.e the system is installed
     * @return boolean
     */
    public function exists()
    {
        return $this->exists;
    }

    /**
     * Whether a given token is valid
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
     * Returns a private key
     * @return string
     */
    public function key()
    {
        return $this->key;
    }

    /**
     * Returns an array of all available modules
     * @return array
     */
    public function getModules()
    {
        $modules = &Cache::memory(__METHOD__);

        if (isset($modules)) {
            return $modules;
        }

        $installation = !$this->exists();
        $saved_modules = $this->getInstalledModules();

        $modules = array();
        foreach (scandir(GC_MODULE_DIR) as $module_dir) {

            if (!$this->validModuleId($module_dir)) {
                continue;
            }

            $module_name = $module_dir;
            $module_data = $this->getModuleData($module_name);

            if (empty($module_data['info']['core'])) {
                continue;
            }

            $module_info = $module_data['info'];
            $module_instance = $module_data['instance'];

            if (isset($module_info['id']) && !$this->validModuleId($module_info['id'])) {
                continue;
            }

            $module_info['hooks'] = $this->getModuleHooks($module_instance);

            $module_info += array(
                'type' => 'module',
                'id' => $module_name,
                'name' => $module_name,
                'class' => $module_data['class'],
                'directory' => GC_MODULE_DIR . "/$module_name"
            );

            if (isset($saved_modules[$module_info['id']])) {

                // Do not rewrite status and weight set in code
                if (isset($module_info['status'])) {
                    unset($saved_modules[$module_info['id']]['status']);
                }
                if (isset($module_info['weight'])) {
                    unset($saved_modules[$module_info['id']]['weight']);
                }

                $module_info['installed'] = true;
                $module_info = array_merge($module_info, $saved_modules[$module_info['id']]);
            }

            $modules[$module_info['id']] = $module_info;
        }

        return $modules;
    }

    /**
     * Returns an array containing module info and instance
     * @param string $module_id
     * @return array
     */
    public function getModuleData($module_id)
    {
        $instance = $this->getModuleInstance($module_id);

        if (!$instance instanceof \gplcart\core\Module) {
            return array();
        }

        $info = $instance->info();

        return array(
            'info' => $info,
            'instance' => $instance,
            'class' => get_class($instance),
            'id' => isset($info['id']) ? $info['id'] : $module_id
        );
    }

    /**
     * Returns module class instance
     * @param string $module_id
     * @return null|object
     */
    public function getModuleInstance($module_id)
    {
        $class = $this->getModuleClassNamespace($module_id);

        try {
            $instance = Container::get($class);
        } catch (\ReflectionException $exc) {
            trigger_error($exc->getMessage());
            return null;
        }

        return $instance;
    }

    /**
     * Returns namespaced module class
     * @param string $module_id
     * @return string
     */
    public function getModuleClassNamespace($module_id)
    {
        $class_name = $this->getModuleClassName($module_id);
        return "gplcart\\modules\\$module_id\\$class_name";
    }

    /**
     * Creates a module class name from a module ID
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

        $modules = &Cache::memory(__METHOD__);

        if (isset($modules)) {
            return $modules;
        }

        $sql = 'SELECT * FROM module ORDER BY weight ASC';
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
     * Initializes system config
     * @return boolean
     */
    protected function init()
    {
        if (!is_readable(GC_CONFIG_COMMON)) {
            return false;
        }

        $this->config = include GC_CONFIG_COMMON;

        if (empty($this->config['database'])) {
            throw new DatabaseException('Missing database settings');
        }

        $this->exists = true;

        if (isset($this->db)) {
            return true;
        }

        $this->db = Container::get('gplcart\\core\\Database', array($this->config['database']));
        $this->config = array_merge($this->config, $this->select());
        $this->key = $this->get('private_key', '');

        if (empty($this->key)) {
            $this->key = gplcart_string_random();
            $this->set('private_key', $this->key);
        }

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
     * Returns an array of methods which are hooks
     * @param object|string $class
     * @return array
     */
    public function getModuleHooks($class)
    {
        return array_filter(get_class_methods($class), function ($method) {
            return (0 === strpos($method, 'hook'));
        });
    }

    /**
     * Whether the module exists and enabled
     * @param string $module_id
     * @return boolean
     */
    public function isEnabledModule($module_id)
    {
        $modules = $this->getInstalledModules();
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
     * Validates a module id
     * @param string $id
     * @return boolean
     */
    public function validModuleId($id)
    {
        return (bool) preg_match('/^[a-z][a-z0-9_]+$/', $id);
    }

}
