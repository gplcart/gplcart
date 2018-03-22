<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception;

/**
 * Parent class for modules
 */
class Module
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = $this->config->getDb();
    }

    /**
     * Returns module setting(s)
     * @param string $module_id
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getSettings($module_id, $key = null, $default = null)
    {
        $module = $this->get($module_id);

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
     * Adds/updates settings for a given module
     * @param string $module_id
     * @param array $settings
     * @return boolean
     */
    public function setSettings($module_id, array $settings)
    {
        $result = false;

        if ($this->isInstalled($module_id)) {
            $this->update($module_id, array('settings' => $settings));
            $result = true;
        } else if ($this->isActiveTheme($module_id)) {
            $data = array('status' => true, 'settings' => $settings, 'module_id' => $module_id);
            $this->add($data);
            $result = true;
        }

        return $result;
    }

    /**
     * Updates a module
     * @param string $module_id
     * @param array $data
     */
    public function update($module_id, array $data)
    {
        $data['modified'] = GC_TIME;
        $this->db->update('module', $data, array('module_id' => $module_id));
    }

    /**
     * Adds (installs) a module to the database
     * @param array $data
     */
    public function add(array $data)
    {
        $weight = (int) $this->db->fetchColumn('SELECT COUNT(*) FROM module', array());

        $data += array('weight' => $weight + 1);
        $data['created'] = $data['modified'] = GC_TIME;
        $this->db->insert('module', $data);
    }

    /**
     * Delete a module from the database
     * @param string $module_id
     * @return bool
     */
    public function delete($module_id)
    {
        return (bool) $this->db->delete('module', array('module_id' => $module_id));
    }

    /**
     * Whether a given module is an active theme
     * @param string $module_id
     * @return boolean
     */
    public function isActiveTheme($module_id)
    {
        return in_array($module_id, $this->getActiveThemes());
    }

    /**
     * Returns an array of active theme modules
     * @return array
     */
    public function getActiveThemes()
    {
        $themes = &gplcart_static('module.active.themes');

        if (isset($themes)) {
            return $themes;
        }

        $themes = array($this->config->get('theme_backend', 'backend'));

        if (!$this->db->isInitialized()) {
            return $themes;
        }

        $stores = $this->db->fetchAll('SELECT * FROM store', array());

        foreach ($stores as $store) {
            $data = unserialize($store['data']);
            foreach ($data as $key => $value) {
                if (strpos($key, 'theme') === 0) {
                    $themes[] = $value;
                }
            }
        }

        return $themes;
    }

    /**
     * Returns an array of all available modules
     * @return array
     */
    public function getList()
    {
        $modules = &gplcart_static('module.list');

        if (isset($modules)) {
            return $modules;
        }

        $installed = $this->getInstalled();

        $modules = array();

        foreach ($this->scan() as $module_id => $info) {
            $modules[$module_id] = $this->prepareInfo($module_id, $info, $installed);
        }

        gplcart_array_sort($modules);

        return $modules;
    }

    /**
     * Returns an array of scanned module IDs
     * @param string $directory
     * @return array
     */
    public function scan($directory = GC_DIR_MODULE)
    {
        $modules = array();

        foreach (scandir($directory) as $module_id) {

            if (!$this->isValidId($module_id)) {
                continue;
            }

            $info = $this->getInfo($module_id);

            if (!isset($info['core'])) {
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
    protected function prepareInfo($module_id, array $info, array $installed)
    {
        $info['directory'] = $this->getDirectory($module_id);

        $info += array(
            'type' => 'module',
            'name' => $module_id,
            'version' => null
        );

        // Do not override status set in module.json for locked modules
        if (isset($info['status']) && !empty($info['lock'])) {
            unset($installed[$module_id]['status']);
        }

        if ($info['version'] === 'core') {
            $info['version'] = GC_VERSION;
        }

        if (!empty($info['dependencies'])) {
            foreach ((array) $info['dependencies'] as $dependency_module_id => $dependency_version) {
                if ($dependency_version === 'core') {
                    $info['dependencies'][$dependency_module_id] = GC_VERSION;
                }
            }
        }

        // Do not override weight set in module.json for locked modules
        if (isset($info['weight']) && !empty($info['lock'])) {
            unset($installed[$module_id]['weight']);
        }

        if (isset($installed[$module_id])) {

            $info['installed'] = true;

            if (empty($installed[$module_id]['settings'])) {
                unset($installed[$module_id]['settings']);
            }

            $info = array_replace($info, $installed[$module_id]);
        }

        if (!empty($info['status'])) {

            try {
                $instance = $this->getInstance($module_id);
                $info['hooks'] = $this->getHooks($instance);
                $info['class'] = get_class($instance);
            } catch (Exception $exc) {
                return $info;
            }
        }

        return $info;
    }

    /**
     * Returns an array of module data
     * @param string $module_id
     * @return array
     */
    public function get($module_id)
    {
        $modules = $this->getList();
        return empty($modules[$module_id]) ? array() : $modules[$module_id];
    }

    /**
     * Returns an absolute path to the module directory
     * @param string $module_id
     * @return string
     */
    public function getDirectory($module_id)
    {
        return GC_DIR_MODULE . "/$module_id";
    }

    /**
     * Returns an array of module data from module.json file
     * @param string $module_id
     * @todo - remove 'id' key everywhere
     * @return array
     */
    public function getInfo($module_id)
    {
        static $information = array();

        if (isset($information[$module_id])) {
            return $information[$module_id];
        }

        $file = $this->getModuleInfoFile($module_id);

        $decoded = null;

        if (is_file($file)) {
            $decoded = json_decode(file_get_contents($file), true);
        }

        if (is_array($decoded)) {
            $decoded['id'] = $decoded['module_id'] = $module_id;
            $information[$module_id] = $decoded;
        } else {
            $information[$module_id] = array();
        }

        return $information[$module_id];
    }

    /**
     * Returns a path to the module info file
     * @param string $module_id
     * @return string
     */
    public function getModuleInfoFile($module_id)
    {
        return GC_DIR_MODULE . "/$module_id/module.json";
    }

    /**
     * Returns the module class instance
     * @param string $module_id
     * @return object
     */
    public function getInstance($module_id)
    {
        return Container::get($this->getClass($module_id));
    }

    /**
     * Returns a base namespace for the module ID
     * @param string $module_id
     * @return string
     */
    public function getNamespace($module_id)
    {
        return "gplcart\\modules\\$module_id";
    }

    /**
     * Returns a namespaced class for the module ID
     * @param string $module_id
     * @return string
     */
    public function getClass($module_id)
    {
        return $this->getNamespace($module_id) . '\\Main';
    }

    /**
     * Returns an array of all installed modules
     * @return array
     */
    public function getInstalled()
    {
        if (!$this->db->isInitialized()) {
            return array();
        }

        $modules = &gplcart_static('module.installed.list');

        if (isset($modules)) {
            return $modules;
        }

        $options = array(
            'index' => 'module_id',
            'unserialize' => 'settings'
        );

        return $modules = $this->db->fetchAll('SELECT * FROM module', array(), $options);
    }

    /**
     * Returns an array of enabled modules
     * @return array
     */
    public function getEnabled()
    {
        return array_filter($this->getList(), function ($module) {
            return !empty($module['status']);
        });
    }

    /**
     * Returns an array of class methods which are hooks
     * @param object|string $class
     * @return array
     */
    public function getHooks($class)
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
    public function isEnabled($module_id)
    {
        $modules = $this->getEnabled();
        return !empty($modules[$module_id]['status']);
    }

    /**
     * Whether the module is installed, i.e exists in database
     * @param string $module_id
     * @return boolean
     */
    public function isInstalled($module_id)
    {
        $modules = $this->getInstalled();
        return isset($modules[$module_id]);
    }

    /**
     * Whether the module is locked
     * @param string $module_id
     * @return boolean
     */
    public function isLocked($module_id)
    {
        $info = $this->getInfo($module_id);
        return !empty($info['lock']);
    }

    /**
     * Whether the module is installer
     * @param string $module_id
     * @return boolean
     */
    public function isInstaller($module_id)
    {
        $info = $this->getInfo($module_id);
        return isset($info['type']) && $info['type'] === 'installer';
    }

    /**
     * Validates a module ID
     * @param string $id
     * @return boolean
     */
    public function isValidId($id)
    {
        if (preg_match('/^[a-z][a-z0-9_]+$/', $id) !== 1) {
            return false;
        }

        $reserved = array(
            'core',
            'gplcart',
            'module',
            // Hooks in 3-d party modules usually defined as "module.MODULEID.something"
            // To prevent possible conflicts with core hooks in the same hook name space also reserve these names:
            'enable',
            'disable',
            'install',
            'uninstall'
        );

        return !in_array($id, $reserved);
    }

    /**
     * Returns an array of modules by the type
     * @param string $type
     * @param boolean $enabled
     * @return array
     */
    public function getByType($type, $enabled = false)
    {
        $modules = $enabled ? $this->getEnabled() : $this->getList();

        foreach ($modules as $id => $info) {
            if ($type !== $info['type']) {
                unset($modules[$id]);
            }
        }

        return $modules;
    }

}
