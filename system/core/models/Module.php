<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Hook;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to modules
 */
class Module extends Model
{

    use \gplcart\core\traits\Dependency;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * @param Hook $hook
     * @param LanguageModel $language
     */
    public function __construct(Hook $hook, LanguageModel $language)
    {
        parent::__construct();

        $this->hook = $hook;
        $this->language = $language;
    }

    /**
     * Whether the module exists and enabled
     * @param string $module_id
     * @return boolean
     */
    public function isEnabled($module_id)
    {
        return $this->config->isEnabledModule($module_id);
    }

    /**
     * Whether the module is locked
     * @param string $module_id
     * @return bool
     */
    public function isLocked($module_id)
    {
        return $this->config->isLockedModule($module_id);
    }

    /**
     * Whether the module installed, e.g exists in database
     * @param string $module_id
     * @return boolean
     */
    public function isInstalled($module_id)
    {
        return $this->config->isInstalledModule($module_id);
    }

    /**
     * Whether the module is an installer
     * @param string $module_id
     * @return bool
     */
    public function isInstaller($module_id)
    {
        return $this->config->isInstallerModule($module_id);
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
     * @return boolean|string
     */
    public function enable($module_id)
    {
        $result = $this->canEnable($module_id);

        $this->hook->attach("module.enable.before|$module_id", $result, $this);

        if ($result !== true) {
            return $result;
        }

        $this->update($module_id, array('status' => 1));

        $this->setOverrideConfig();
        $this->setTranslations($module_id);

        $this->hook->attach("module.enable.after|$module_id", $result, $this);
        return $result;
    }

    /**
     * Whether a given module can be enabled
     * @param string $module_id
     * @return boolean|string
     */
    public function canEnable($module_id)
    {
        if ($this->isEnabled($module_id)) {
            return $this->language->text('Module already installed and enabled');
        }

        if ($this->isLocked($module_id)) {
            return $this->language->text('Module is locked in code');
        }

        if ($this->isInstaller($module_id)) {
            return $this->language->text('Installers cannot be enabled');
        }

        // Test module class
        // If a fatal error occurs here, the module won't be enabled
        $this->config->getModuleInstance($module_id);

        return $this->checkRequirements($module_id);
    }

    /**
     * Whether a given module can be installed (and enabled)
     * @param string $module_id
     * @return mixed
     */
    public function canInstall($module_id)
    {
        if ($this->isInstalled($module_id)) {
            return $this->language->text('Module already installed');
        }

        if ($this->isLocked($module_id)) {
            return $this->language->text('Module is locked in code');
        }

        if ($this->isInstaller($module_id)) {
            return $this->language->text('Installers cannot be installed');
        }

        // Test module class
        // If a fatal error occurs here, the module won't be installed
        $instance = $this->config->getModuleInstance($module_id);

        if (!$instance instanceof \gplcart\core\Module) {
            return $this->language->text('Failed to instantiate the main module class');
        }

        return $this->checkRequirements($module_id);
    }

    /**
     * Whether a given module can be disabled
     * @param string $module_id
     * @return boolean|string
     */
    public function canDisable($module_id)
    {
        return $this->canUninstall($module_id);
    }

    /**
     * Whether a given module can be uninstalled
     * @param string $module_id
     * @return mixed
     */
    public function canUninstall($module_id)
    {
        if ($this->isActiveTheme($module_id)) {
            return $this->language->text('Active theme modules cannot be disabled/uninstalled');
        }

        if ($this->isLocked($module_id)) {
            return $this->language->text('Module is locked in code');
        }

        $modules = $this->getList();
        $dependent = $this->checkDependentModules($module_id, $modules);

        if ($dependent === true) {
            return true;
        }

        return $dependent;
    }

    /**
     * Checks all requirements for the module
     * @param string $module_id
     * @return mixed
     */
    public function checkRequirements($module_id)
    {
        $result_module_id = $this->checkModuleId($module_id);

        if ($result_module_id !== true) {
            return $result_module_id;
        }

        $module = $this->config->getModuleInfo($module_id);

        $result_core = $this->checkCore($module);

        if ($result_core !== true) {
            return $result_core;
        }

        $result_php = $this->checkPhpVersion($module);

        if ($result_php !== true) {
            return $result_php;
        }

        if (isset($module['type']) && $module['type'] === 'installer') {
            return $this->language->text('Cannot install/enable installer modules on runtime');
        }

        return $this->checkDependenciesModule($module_id);
    }

    /**
     * Checks PHP version compatibility for the module ID
     * @param array $module
     * @return boolean|string
     */
    public function checkPhpVersion(array $module)
    {
        if (empty($module['php'])) {
            return true;
        }

        $components = $this->getVersionComponentsTrait($module['php']);

        if (empty($components)) {
            return $this->language->text('Requires incompatible version of @name', array('@name' => 'PHP'));
        }

        list($operator, $number) = $components;

        if (!version_compare(PHP_VERSION, $number, $operator)) {
            return $this->language->text('Requires incompatible version of @name', array('@name' => 'PHP'));
        }

        return true;
    }

    /**
     * Checks module dependencies
     * @param string $module_id
     * @return boolean|array
     */
    protected function checkDependenciesModule($module_id)
    {
        $modules = $this->getList();
        $validated = $this->validateDependenciesTrait($modules, true);

        if (empty($validated[$module_id]['errors'])) {
            return true;
        }

        $translated = array();
        foreach ($validated[$module_id]['errors'] as $error) {
            list($text, $arguments) = $error;
            $translated[] = $this->language->text($text, $arguments);
        }

        return $translated;
    }

    /**
     * Checks a module ID
     * @param string $module_id
     * @return boolean|string
     */
    public function checkModuleId($module_id)
    {
        if ($this->config->validModuleId($module_id)) {
            return true;
        }

        return $this->language->text('Invalid module ID');
    }

    /**
     * Checks core version requirements
     * @param array $module
     * @return boolean|string
     */
    public function checkCore(array $module)
    {
        if (empty($module['core'])) {
            return $this->language->text('Missing core version');
        }

        if (version_compare(GC_VERSION, $module['core']) < 0) {
            return $this->language->text('Module incompatible with the current system core version');
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
        $this->db->update('module', $data, array('module_id' => $module_id));
    }

    /**
     * Adds (installs) a module to the database
     * @param array $data
     */
    public function add(array $data)
    {
        $data += array('weight' => $this->getNextWeight());
        $this->db->insert('module', $data);
    }

    /**
     * Adds/updates settings for a given module
     * @param string $module_id
     * @param array $settings
     * @return boolean
     */
    public function setSettings($module_id, array $settings)
    {
        if ($this->isInstalled($module_id)) {
            $this->update($module_id, array('settings' => $settings));
            return true;
        }

        if ($this->isActiveTheme($module_id)) {
            $data = array('status' => true, 'settings' => $settings, 'module_id' => $module_id);
            $this->add($data);
            return true;
        }

        return false;
    }

    /**
     * Disables a module
     * @param string $module_id
     * @return boolean|string
     */
    public function disable($module_id)
    {
        $result = $this->canDisable($module_id);

        $this->hook->attach("module.disable.before|$module_id", $result, $this);

        if ($result !== true) {
            return $result;
        }

        if ($this->isInstalled($module_id)) {
            $this->update($module_id, array('status' => false));
        } else {
            $this->add(array('status' => false, 'module_id' => $module_id));
        }

        $this->setOverrideConfig();

        $this->hook->attach("module.disable.after|$module_id", $result, $this);
        return $result;
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
        $themes = &gplcart_static(__METHOD__);

        if (isset($themes)) {
            return $themes;
        }

        $themes = array($this->config->get('theme_backend', 'backend'));

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
     * Checks dependent modules
     * @param string $module_id
     * @param array $modules
     * @return mixed
     */
    public function checkDependentModules($module_id, array $modules)
    {
        unset($modules[$module_id]);

        $required_by = array();
        foreach ($modules as $info) {
            if (empty($info['dependencies'])) {
                continue;
            }
            foreach (array_keys($info['dependencies']) as $dependency) {
                if ($dependency === $module_id && !empty($info['status'])) {
                    $required_by[] = $info['name'];
                }
            }
        }

        if (!empty($required_by)) {
            return $this->language->text('Required by') . ': ' . implode(', ', $required_by);
        }

        return true;
    }

    /**
     * Install a module
     * @param string $module_id
     * @param boolean $status
     * @return mixed
     */
    public function install($module_id, $status = true)
    {
        gplcart_static_clear();

        $result = $this->canInstall($module_id);

        $this->hook->attach("module.install.before|$module_id", $result, $this);

        if ($result !== true) {
            $this->db->delete('module', array('module_id' => $module_id));
            return $result;
        }

        $this->add(array('module_id' => $module_id, 'status' => $status));

        $this->setOverrideConfig();
        $this->setTranslations($module_id);

        $this->hook->attach("module.install.after|$module_id", $result, $this);
        return $result;
    }

    /**
     * Returns the max weight of the installed modules
     * @return integer
     */
    public function getMaxWeight()
    {
        return (int) $this->db->fetchColumn('SELECT COUNT(*) FROM module', array());
    }

    /**
     * Uninstall a module
     * @param string $module_id
     * @return mixed
     */
    public function uninstall($module_id)
    {
        $result = $this->canUninstall($module_id);

        $this->hook->attach("module.uninstall.before|$module_id", $result, $this);

        if ($result !== true) {
            return $result;
        }

        $this->db->delete('module', array('module_id' => $module_id));
        $this->setOverrideConfig();

        $this->hook->attach("module.uninstall.after|$module_id", $result, $this);
        return $result;
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
     * Returns an array of class name spaces to be overridden
     * @return array
     */
    protected function getOverrideMap()
    {
        gplcart_static_clear();

        $map = array();
        foreach ($this->getEnabled() as $module) {

            $directory = GC_MODULE_DIR . "/{$module['id']}/override/classes";

            if (!is_readable($directory)) {
                continue;
            }

            foreach ($this->scanOverrideFiles($directory) as $file) {
                $original = str_replace('/', '\\', str_replace($directory . '/', '', preg_replace('/Override$/', '', $file)));
                $override = str_replace('/', '\\', str_replace(GC_SYSTEM_DIR . '/', '', $file));
                $map["gplcart\\$original"][$module['id']] = "gplcart\\$override";
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
                if (substr($path, -4) === '.php') {
                    $results[] = rtrim($path, '.php');
                }
            } elseif ($value !== '.' && $value !== '..') {
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

        if (empty($files)) {
            return false;
        }

        $copied = 0;
        foreach ($files as $file) {

            $info = pathinfo($file);
            $destination = GC_LOCALE_DIR . "/{$info['filename']}";

            if (!file_exists($destination) && !mkdir($destination, 0775, true)) {
                continue;
            }

            $destination .= "/{$module_id}_{$info['basename']}";
            $copied += (int) copy($file, $destination);
        }

        return count($files) == $copied;
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
            return gplcart_file_scan($directory, array('csv'));
        }

        return array();
    }

    /**
     * Returns the next weight value for a new module
     * @return integer
     */
    protected function getNextWeight()
    {
        return $this->getMaxWeight() + 1;
    }

}
