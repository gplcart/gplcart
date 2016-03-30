<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use Exception;
use core\Config;
use core\Container;
use core\classes\Cache;
use core\models\Language;

class Module
{

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param Config $config
     * @param Language $language
     */
    public function __construct(Config $config, Language $language)
    {
        $this->config = $config;
        $this->db = $this->config->db();
        $this->language = $language;
    }

    /**
     * Whether the module exists and enabled
     * @param string $module_id
     * @return array
     */
    public function enabled($module_id)
    {
        $modules = $this->getList();
        return !empty($modules[$module_id]['status']);
    }

    /**
     * Returns an array of all available modules
     * @return array
     */
    public function getList()
    {
        return $this->config->getModules();
    }

    /**
     * Returns an array of all saved/installed modules from the database
     * @return array
     */
    public function getInstalled()
    {
        return $this->config->getInstalledModules();
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

    /**
     * Returns an array of enabled modules
     * @return array
     */
    public function getEnabled()
    {
        return $this->config->getEnabledModules();
    }

    /**
     * Enables a module
     * @param string $module_id
     * @return boolean
     */
    public function enable($module_id)
    {
        $result = $this->canEnable($module_id);

        $module = $this->get($module_id);
        if (is_callable(array($module['class'], 'beforeEnable'))) {

            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeEnable();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->update($module_id, array('status' => 1));
            $this->setOverrideConfig();
            $this->setTranslations($module_id);
            return true;
        }

        return $result;
    }

    /**
     * Whether a given module can be enabled
     * @param string $module_id
     * @return boolean
     */
    public function canEnable($module_id)
    {
        return $this->canInstall($module_id);
    }

    /**
     * Whether a given module can be installed
     * @param string $module_id
     * @return boolean|string
     */
    public function canInstall($module_id)
    {
        $modules = $this->getList();

        if (!empty($modules[$module_id]['status']) && !empty($modules[$module_id]['installed'])) {
            return $this->language->text('Module already installed');
        }

        if (empty($modules[$module_id]['core'])) {
            return $this->language->text('Missing core version');
        }

        if (version_compare(GC_VERSION, $modules[$module_id]['core']) < 0) {
            return $this->language->text('Module incompatible with the current system core version');
        }

        $results = $this->checkRequiredModules($module_id, $modules);

        if ($results !== true) {
            return $results;
        }

        return true;
    }

    /**
     * Checks required modules for a given module
     * @param string $module_id
     * @param array $modules
     * @return boolean
     */
    public function checkRequiredModules($module_id, array $modules)
    {
        if (empty($modules[$module_id]['dependencies'])) {
            return true;
        }

        $errors = array();

        foreach ((array) $modules[$module_id]['dependencies'] as $required) {
            if (empty($modules[$required])) {
                $errors[] = $this->language->text('Required module %name is missing', array('%name' => $required));
                continue;
            }

            if (empty($modules[$required]['status'])) {
                $errors[] = $this->language->text('Required module %name is disabled', array('%name' => $required));
            }
        }

        if ($errors) {
            return $errors;
        }

        return true;
    }

    /**
     * Returns a module
     * @param string $module_id
     * @return array
     */
    public function get($module_id)
    {
        $modules = $this->getList();
        return isset($modules[$module_id]) ? $modules[$module_id] : array();
    }

    /**
     * Updates a module
     * @param string $module_id
     * @param array $data
     */
    public function update($module_id, array $data)
    {
        $values = array();

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (!empty($data['settings'])) {
            $values['settings'] = serialize((array) $data['settings']);
        }

        $this->db->update('module', $values, array('module_id' => $module_id));
    }

    /**
     * Adds (installs) a module to the database
     * @param array $data
     */
    public function add(array $data)
    {
        $values = array(
            'module_id' => $data['module_id'],
            'status' => !empty($data['module_id']),
            'weight' => isset($data['weight']) ? (int) $data['weight'] : $this->getNextWeight(),
            'settings' => empty($data['settings']) ? serialize(array()) : serialize((array) $data['settings']),
        );

        $this->db->insert('module', $values);
    }

    /**
     * Adds / updates settings for a given module
     * @param string $module_id
     * @param array $settings
     * @return boolean
     */
    public function setSettings($module_id, array $settings)
    {
        $existing = $this->get($module_id);

        if (!empty($existing['installed'])) {
            $this->update($module_id, array('settings' => $settings));
            return true;
        }

        if ($this->isActiveTheme($module_id)) {
            $this->add(array('module_id' => $module_id, 'status' => 1, 'settings' => $settings));
            return true;
        }

        return false;
    }

    /**
     * Saves the override config file
     * @return boolean
     */
    protected function setOverrideConfig()
    {
        $map = $this->getOverrideMap();

        if (file_exists(GC_CONFIG_OVERRIDE)) {
            chmod(GC_CONFIG_OVERRIDE, 0644);
        }

        file_put_contents(GC_CONFIG_OVERRIDE, '<?php return ' . var_export($map, true) . ';');
        chmod(GC_CONFIG_OVERRIDE, 0444);

        return true;
    }

    /**
     * Returns an array of class namespaces to be overridden
     * @return array
     */
    protected function getOverrideMap()
    {
        // Clear all caches before getting enabled modules
        Cache::clear('modules');

        $map = array();
        foreach ($this->getEnabled() as $module_id => $module) {

            $directory = GC_MODULE_DIR . "/$module_id/override";

            if (!is_readable($directory)) {
                continue;
            }

            foreach ($this->scanOverrideFiles($directory) as $file) {
                $original = str_replace('/', '\\', str_replace($directory . '/', '', preg_replace('/Override$/', '', $file)));
                $override = str_replace('/', '\\', str_replace(GC_SYSTEM_DIR . '/', '', $file));
                $map[$original][$module_id] = $override;
            }
        }

        return $map;
    }

    /**
     * Recursively scans module override files
     * @param string $directory
     * @param array $results
     * @return array
     */
    protected function scanOverrideFiles($directory, array &$results = array())
    {
        foreach (scandir($directory) as $value) {
            $path = "$directory/$value";
            if (is_file($path)) {
                if ((substr($path, -4) === '.php')) {
                    $results[] = rtrim($path, '.php');
                }
            } else if ($value != '.' && $value != '..') {
                $this->scanOverrideFiles($path, $results);
            }
        }

        return $results;
    }

    /**
     * Copies translation files into the locale directory
     * @param string $module_id
     * @return boolean
     */
    protected function setTranslations($module_id)
    {
        $files = $this->scanTranslations($module_id);

        if (!$files) {
            return false;
        }

        foreach ($files as $file) {

            $filename = basename($file);
            $langcode = strtok($filename, '.');

            $langcode_folder = GC_LOCALE_DIR . "/{$langcode}/LC_MESSAGES";

            if (!file_exists($langcode_folder) && !mkdir($langcode_folder, 0644, true)) {
                continue;
            }

            $destination = "$langcode_folder/{$module_id}_{$filename}";

            if (!file_exists($destination)) {
                copy($file, $destination);
            }
        }

        return true;
    }

    /**
     * Finds possible translations of the module
     * @param string $module_id
     * @return array
     */
    protected function scanTranslations($module_id)
    {
        $directory = GC_MODULE_DIR . "/$module_id/locale";

        if (file_exists($directory)) {
            return glob("$directory/*.{po,mo}", GLOB_BRACE);
        }

        return array();
    }

    /**
     * Disables a module
     * @param string $module_id
     * @return boolean|string
     */
    public function disable($module_id)
    {
        $result = $this->canDisable($module_id);

        $module = $this->get($module_id);
        if (is_callable(array($module['class'], 'beforeDisable'))) {

            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeDisable();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->update($module_id, array('status' => 0));
            $this->setOverrideConfig();
            return true;
        }
        return $result;
    }

    /**
     * Whether a given module can be disabled
     * @param string $module_id
     * @return boolean
     */
    public function canDisable($module_id)
    {
        return $this->canUninstall($module_id);
    }

    /**
     * Whether a given module can be uninstalled
     * @param string $module_id
     * @return boolean|string
     */
    public function canUninstall($module_id)
    {
        if ($this->isActiveTheme($module_id)) {
            return $this->language->text('Active theme modules cannot be uninstalled');
        }

        $modules = $this->getList();
        $dependent = $this->checkDependentModules($module_id, $modules);

        if ($dependent !== true) {
            return $dependent;
        }

        return true;
    }

    /**
     * Whetner a given module is an active theme
     * @param string $module_id
     * @return boolean
     */
    public function isActiveTheme($module_id)
    {
        return in_array($module_id, $this->getActiveThemes());
    }

    /**
     * Returns an array of actiive theme modules
     * @return array
     */
    public function getActiveThemes()
    {
        $themes = &Cache::memory('active.themes');

        if (isset($themes)) {
            return $themes;
        }

        $themes = array($this->config->get('theme_backend', 'backend'));

        $sth = $this->db->query('SELECT * FROM store');

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $store) {
            $data = unserialize($store['data']);

            foreach ($data as $key => $value) {
                if (0 === strpos($key, 'theme')) {
                    $themes[] = $value;
                }
            }
        }

        return $themes;
    }

    /**
     * Checks dependent modules
     * @param string $module_id
     * @param array $modules
     * @return boolean|array
     */
    public function checkDependentModules($module_id, array $modules)
    {
        unset($modules[$module_id]);

        $required_by = array();

        foreach ($modules as $info) {

            if (empty($info['dependencies'])) {
                continue;
            }

            foreach ((array) $info['dependencies'] as $dependent) {
                if ($dependent == $module_id && !empty($info['status'])) {
                    $required_by['required_by'][] = $dependent;
                }
            }
        }

        if ($required_by) {
            return $required_by;
        }

        return true;
    }

    /**
     * Installs a module
     * @param string $module_id
     * @return boolean|string
     */
    public function install($module_id)
    {
        $result = $this->canInstall($module_id);
        $module = $this->get($module_id);

        if (is_callable(array($module['class'], 'beforeInstall'))) {

            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeInstall();
            } catch (Exception $e) {
                // uninstall trouble module
                $this->db->delete('module', array('module_id' => $module_id));
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->add(array('module_id' => $module_id, 'status' => 1));
            $this->setOverrideConfig();
            $this->setTranslations($module_id);
            return true;
        }

        return $result;
    }

    /**
     * Returns the max weight of the installed modules
     * @return integer
     */
    public function getMaxWeight()
    {
        $sth = $this->db->query('SELECT MAX(weight) FROM module');
        return (int) $sth->fetchColumn();
    }

    /**
     * Returns the next weight value for a new module
     * @return integer
     */
    protected function getNextWeight()
    {
        return $this->getMaxWeight() + 1;
    }

    /**
     * Uninstalls a module
     * @param string $module_id
     * @return array|boolean
     */
    public function uninstall($module_id)
    {
        $result = $this->canUninstall($module_id);
        $module = $this->get($module_id);

        if (is_callable(array($module['class'], 'beforeUninstall'))) {

            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeUninstall();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->db->delete('module', array('module_id' => $module_id));
            $this->setOverrideConfig();
            return true;
        }

        return $result;
    }

}
