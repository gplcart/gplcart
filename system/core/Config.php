<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use PDO;
use core\Container;
use core\classes\Tool;
use core\classes\Cache;

/**
 * Contains methods to work with system configurations
 */
class Config
{

    /**
     * PDO instance
     * @var \core\classes\Database $db
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

        if (array_key_exists($key, $modules[$module_id]['settings'])) {
            return $modules[$module_id]['settings'][$key];
        }

        return $default;
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
        return Tool::hashEquals($this->token(), (string) $token);
    }

    /**
     * Returns a token based on the current session iD
     * @return string
     */
    public function token()
    {
        return str_replace(array('+', '/', '='), '', base64_encode(crypt(session_id(), $this->key())));
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
        $modules = &Cache::memory('modules');

        if (isset($modules)) {
            return $modules;
        }

        $modules = array();
        $saved_modules = $this->getInstalledModules();

        foreach (scandir(GC_MODULE_DIR) as $module_dir) {
            if (!$this->validModuleId($module_dir)) {
                continue;
            }

            $module_name = $module_dir;
            $module_class = $this->getModuleClass($module_name);
            $module_data = $this->getModuleData($module_class);

            if (empty($module_data['info']['core'])) {
                continue;
            }

            $module_info = $module_data['info'];
            $module_instance = $module_data['instance'];

            if (!empty($module_info['dependencies'])) {
                $module_info['dependencies'] = $this->validModuleId((array) $module_info['dependencies']);
            }

            if (isset($module_info['id']) && !$this->validModuleId($module_info['id'])) {
                continue;
            }

            $module_info['hooks'] = $this->getHooks($module_instance);

            $module_info += array(
                'class' => $module_class,
                'directory' => GC_MODULE_DIR . "/$module_name",
                'name' => $module_name,
                'description' => '',
                'version' => '',
                'author' => '',
                'image' => '',
                'settings' => array(),
                'configure' => false,
                'type' => '',
                'key' => '',
                'id' => $module_name,
                'dependencies' => array()
            );

            if (isset($saved_modules[$module_info['id']])) {
                $module_info['installed'] = true;
                $module_info = Tool::merge($module_info, $saved_modules[$module_info['id']]);
            }

            if (in_array($module_info['id'], array('backend', 'frontend'))) {
                $module_info['status'] = 1;
            }

            $modules[$module_info['id']] = $module_info;
        }

        return $modules;
    }

    /**
     * Returns an array containing module info and instance
     * @param string $class
     * @return boolean|array
     */
    public function getModuleData($class)
    {
        $instance = Container::instance($class);

        if (empty($instance) || !is_callable(array($instance, 'info'))) {
            return false;
        }

        return array('info' => $instance->info(), 'instance' => $instance);
    }

    /**
     * Returns namespaced module class
     * @param string $module_id
     * @return string
     */
    public function getModuleClass($module_id)
    {
        return "modules\\$module_id\\" . ucfirst(str_replace('_', '', $module_id));
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

        $modules = array();
        $sth = $this->db->query('SELECT * FROM module ORDER BY weight ASC');

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $module) {
            $module['settings'] = empty($module['settings']) ? array() : unserialize($module['settings']);
            $modules[$module['module_id']] = $module;
        }

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
            die('Missing database settings');
        }

        $this->exists = true;

        if (isset($this->db)) {
            return true;
        }

        $this->db = Container::instance('core\\classes\\Database', array($this->config['database']));
        $this->config = array_merge($this->config, $this->select());
        $this->key = $this->get('private_key', '');

        if (empty($this->key)) {
            $this->key = Tool::randomString();
            $this->set('private_key', $this->key);
        }

        return true;
    }

    /**
     * Returns an array of settings from the database
     * @return array
     */
    protected function select()
    {
        if (!$this->exists) {
            return array();
        }

        $settings = array();
        $sth = $this->db->query('SELECT * FROM settings');
        foreach ($sth->fetchAll() as $setting) {
            $settings[$setting['id']] = $setting['serialized'] ? unserialize($setting['value']) : $setting['value'];
        }

        return $settings;
    }

    /**
     * Returns an array of methods which are hooks
     * @param object|string $class
     * @return array
     */
    protected function getHooks($class)
    {
        return array_filter(get_class_methods($class), function ($method) {
            return (0 === strpos($method, 'hook'));
        });
    }

    /**
     * Validates / filters module id(s)
     * @param string|array $id
     * @return boolean|array
     */
    protected function validModuleId($id)
    {
        if (is_string($id)) {
            return Tool::validModuleId($id);
        }

        return array_filter((array) $id, function ($string) {
            return Tool::validModuleId($string);
        });
    }

}
