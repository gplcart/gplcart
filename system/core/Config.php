<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core;

use PDO;
use Exception;
use core\Container;
use core\classes\Tool;
use core\classes\Cache;

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
     * @return null
     * @throws Exception
     */
    public function __construct()
    {
        if (!is_readable(GC_CONFIG_COMMON)) {
            return;
        }

        $this->config = include GC_CONFIG_COMMON;

        if (empty($this->config['database'])) {
            throw new Exception('Missing database settings');
        }

        $this->exists = true;

        if (isset($this->db)) {
            return;
        }

        $this->db = Container::instance('core\\classes\\Database', array($this->config['database']));
        $this->config = array_merge($this->config, $this->select());

        $this->key = $this->get('private_key', '');

        if (!$this->key) {
            $this->key = Tool::randomString();
            $this->set('private_key', $this->key);
        }
    }

    /**
     * Returns an array of settings from the database
     * @return type
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
        if (empty($this->db)) {
            return false;
        }

        $this->reset($key);

        $serialized = false;

        if (is_array($value)) {
            $value = serialize($value);
            $serialized = true;
        }

        $values = array('id' => $key, 'value' => $value, 'created' => GC_TIME, 'serialized' => $serialized);
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
    public function db()
    {
        return $this->db;
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
        return Tool::hashEquals($this->token(), $token);
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

            if (!$this->validModuleName($module_dir)) {
                continue;
            }

            $module_name = $module_dir;
            $module_file = GC_MODULE_DIR . "/$module_name/$module_name.php";
            $module_namespace = "modules\\$module_name\\$module_name";

            $instance = Container::instance($module_namespace);

            if (!$instance || !is_callable(array($instance, 'info'))) {
                continue;
            }

            $module_info = $instance->info();

            if (empty($module_info['core'])) {
                continue;
            }

            if (!empty($module_info['dependencies'])) {
                $module_info['dependencies'] = $this->validModuleName((array) $module_info['dependencies']);
            }

            if (isset($module_info['id']) && !$this->validModuleName($module_info['id'])) {
                continue;
            }

            $module_info['hooks'] = $this->getHooks($instance);

            $module_info += array(
                'file' => $module_file,
                'class' => $module_namespace,
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
     * Returns an array of methods which are hooks
     * @param object|string $class
     * @return array
     */
    protected function getHooks($class)
    {
        return array_filter(get_class_methods($class), function($method) {
            return (0 === strpos($method, 'hook'));
        });
    }

    /**
     * Validates / filters module name(s)
     * @param string|array $name
     * @return boolean|array
     */
    protected function validModuleName($name)
    {
        if (is_string($name)) {
            return preg_match('/^[a-z0-9]+$/', $name);
        }

        return array_filter((array) $name, function($module_id) {
            return $this->validModuleName($module_id);
        });
    }

    /**
     * Returns an array of all installed modules from the database
     * @return array
     */
    public function getInstalledModules()
    {
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
        return array_filter($this->getModules(), function($module) {
            return !empty($module['status']);
        });
    }

}
