<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Cache;
use core\Container;
use core\helpers\Zip as ZipHelper;
use core\models\Backup as BackupModel;
use core\models\Language as LanguageModel;
use core\exceptions\ModuleException;

/**
 * Manages basic behaviors and data related to modules
 */
class Module extends Model
{

    /**
     * Zip helper instance
     * @var \core\helpers\Zip $zip
     */
    protected $zip;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Backup model instance
     * @var \core\models\Backup $backup
     */
    protected $backup;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param BackupModel $backup
     * @param ZipHelper $zip
     */
    public function __construct(LanguageModel $language, BackupModel $backup,
            ZipHelper $zip)
    {
        parent::__construct();

        $this->zip = $zip;
        $this->backup = $backup;
        $this->language = $language;
    }

    /**
     * Whether the module exists and enabled
     * @param string $module_id
     * @return boolean
     */
    public function isEnabled($module_id)
    {
        $modules = $this->getList();
        return !empty($modules[$module_id]['status']);
    }

    /**
     * Whether the module installed, e.g exists in database
     * @param string $module_id
     * @return boolean
     */
    public function isInstalled($module_id)
    {
        $modules = $this->getList();
        return !empty($modules[$module_id]['installed']);
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
        $module = $this->get($module_id);
        $result = $this->canEnable($module_id);

        $this->call('beforeEnable', $module, $result);

        if ($result !== true) {
            return $result;
        }

        $this->update($module_id, array('status' => 1));
        $this->setOverrideConfig();
        $this->setTranslations($module_id);
        return true;
    }

    /**
     * Calls a module method
     * @param string $method
     * @param array $module
     * @param mixed $result
     * @return mixed
     */
    protected function call($method, array $module, &$result)
    {
        if (!is_callable(array($module['class'], $method))) {
            return null;
        }

        try {
            $module_class = Container::instance($module['class']);
            $result = $module_class->{$method}();
        } catch (ModuleException $e) {
            trigger_error($e->getMessage());
        }

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

        return $this->checkRequirements($module_id);
    }

    /**
     * Checks all requirements for the module
     * @param string $module_id
     * @return mixed
     */
    protected function checkRequirements($module_id)
    {
        $modules = $this->getList();

        $result_core = $this->checkCore($module_id, $modules);

        if ($result_core !== true) {
            return $result_core;
        }

        $result_required = $this->checkRequiredModules($module_id, $modules);

        if ($result_required !== true) {
            return $result_required;
        }

        $result_module_id = $this->checkModuleId($module_id);

        if ($result_module_id !== true) {
            return $result_module_id;
        }

        return true;
    }

    /**
     * Checks a module ID
     * @param string $module_id
     * @return boolean|string
     */
    protected function checkModuleId($module_id)
    {
        if (!$this->config->validModuleId($module_id)) {
            return $this->language->text('Invalid module ID');
        }

        $reserved = $this->getReservedModuleId();

        if (in_array($module_id, $reserved)) {
            return $this->language->text('Module ID %id is reserved and cannot be used');
        }

        return true;
    }

    /**
     * Returns an array of reserved module IDs
     * @return array
     */
    public function getReservedModuleId()
    {
        return array('core', 'gplcart', 'backend', 'frontend', 'mobile');
    }

    /**
     * Checks core version requirements
     * @param string $module_id
     * @param array $modules
     * @return boolean|string
     */
    protected function checkCore($module_id, $modules)
    {
        if (empty($modules[$module_id]['core'])) {
            return $this->language->text('Missing core version');
        }

        if (version_compare(GC_VERSION, $modules[$module_id]['core']) < 0) {
            return $this->language->text('Module incompatible with the current system core version');
        }

        return true;
    }

    /**
     * Checks required modules for a given module
     * @param string $module_id
     * @param array $modules
     * @return boolean|array
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

        if (empty($errors)) {
            return true;
        }

        return $errors;
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

            $data = array(
                'status' => true,
                'settings' => $settings,
                'module_id' => $module_id
            );

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
        $module = $this->get($module_id);
        $result = $this->canDisable($module_id);

        $this->call('beforeDisable', $module, $result);

        if ($result !== true) {
            return $result;
        }

        $this->update($module_id, array('status' => false));
        $this->setOverrideConfig();
        return true;
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
            return $this->language->text('Active theme modules cannot be uninstalled');
        }

        $modules = $this->getList();
        $dependent = $this->checkDependentModules($module_id, $modules);

        if ($dependent === true) {
            return true;
        }

        return $dependent;
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
     * Returns an array of active theme modules
     * @return array
     */
    public function getActiveThemes()
    {
        $themes = &Cache::memory('active.themes');

        if (isset($themes)) {
            return $themes;
        }

        $themes = array($this->config->get('theme_backend', 'backend'));

        $stores = $this->db->fetchAll('SELECT * FROM store', array());

        foreach ($stores as $store) {
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

            foreach ((array) $info['dependencies'] as $dependent) {
                if ($dependent === $module_id && !empty($info['status'])) {
                    $required_by['required_by'][] = $dependent;
                }
            }
        }

        if (!empty($required_by)) {
            return $required_by;
        }

        return true;
    }

    /**
     * Installs a module
     * @param string $module_id
     * @param boolean $status
     * @return mixed
     */
    public function install($module_id, $status = true)
    {
        // Clear static cache to see available modules.
        // Important when uploading a module!
        Cache::clearMemory('modules');

        $module = $this->get($module_id);
        $result = $this->canInstall($module_id);

        $this->call('beforeInstall', $module, $result);

        if ($result !== true) {
            // Make sure the troubled module is uninstalled
            $this->db->delete('module', array('module_id' => $module_id));
            return $result;
        }

        $this->add(array('module_id' => $module_id, 'status' => $status));
        $this->setOverrideConfig();
        $this->setTranslations($module_id);
        return true;
    }

    /**
     * Deletes a module from disk
     * @param string $module_id
     * @return boolean
     */
    public function delete($module_id)
    {
        if ($this->isInstalled($module_id) || $this->isEnabled($module_id)) {
            return false;
        }

        return gplcart_file_delete_recursive(GC_MODULE_DIR . "/$module_id");
    }

    /**
     * Returns the max weight of the installed modules
     * @return integer
     */
    public function getMaxWeight()
    {
        return (int) $this->db->fetchColumn('SELECT MAX(weight) FROM module', array());
    }

    /**
     * Uninstalls a module
     * @param string $module_id
     * @return mixed
     */
    public function uninstall($module_id)
    {
        $result = $this->canUninstall($module_id);
        $module = $this->get($module_id);

        $this->call('beforeUninstall', $module, $result);

        if ($result !== true) {
            return $result;
        }

        $this->db->delete('module', array('module_id' => $module_id));
        $this->setOverrideConfig();
        return true;
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
        Cache::clearMemory('modules');

        $map = array();
        foreach ($this->getEnabled() as $module) {

            $directory = GC_MODULE_DIR . "/{$module['id']}/override";

            if (!is_readable($directory)) {
                continue;
            }

            foreach ($this->scanOverrideFiles($directory) as $file) {
                $original = str_replace('/', '\\', str_replace($directory . '/', '', preg_replace('/Override$/', '', $file)));
                $override = str_replace('/', '\\', str_replace(GC_SYSTEM_DIR . '/', '', $file));
                $map[$original][$module['id']] = $override;
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

            // Define the language code by the filename
            // and get expected directory for the language
            $destination = GC_LOCALE_DIR . "/{$info['filename']}";

            // If it does't exist, try to create it
            if (!file_exists($destination) && !mkdir($destination, 0644, true)) {
                continue;
            }

            // Try to copy
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

    /**
     * Installs a module from a zip archive
     * @param string $file
     * @return mixed
     */
    public function installFromZip($file)
    {
        $module_id = $this->getIdFromZip($file);

        if (empty($module_id)) {
            return $this->language->text('Invalid zip content');
        }

        $module = $this->get($module_id);

        if (empty($module)) {
            return $this->installFromZipNew($file, $module_id);
        }

        return $this->installFromZipUpdate($file, $module);
    }

    /**
     * Installs a new module from a ZIP file
     * @param string $file
     * @param string $module_id
     * @return boolean|string
     */
    protected function installFromZipNew($file, $module_id)
    {
        if (!$this->extractFromZip($file)) {
            return $this->language->text('Failed to extract the module files');
        }

        return $this->install($module_id, false);
    }

    /**
     * Updates an existing module from a ZIP file
     * @param string $file
     * @param array $module
     * @return boolean|string
     */
    protected function installFromZipUpdate($file, array $module)
    {
        // Only disabled modules can by updated
        if ($this->isEnabled($module['id'])) {
            return $this->language->text('Disable the module before updating');
        }

        // Backup the current version
        if (!$this->backup($module)) {
            return $this->language->text('Failed to backup the module');
        }

        // Extract and override
        if (!$this->extractFromZip($file)) {
            return $this->language->text('Failed to extract the module files');
        }

        if ($this->isInstalled($module['id'])) {
            return true;
        }

        // Install but not enable
        return $this->install($module['id'], false);
    }

    /**
     * Backups an existing module
     * @param array|string $module
     * @return boolean
     */
    public function backup($module)
    {
        if (!is_array($module)) {
            $module = $this->get($module);
        }

        $vars = array('@name' => $module['name'], '@date' => date("D M j G:i:s"));
        $name = $this->language->text('Module @name. Backed up on @date', $vars);
        $data = array('name' => $name, 'module' => $module);

        $result = $this->backup->backup('module', $data);
        return is_numeric($result);
    }

    /**
     * Extracts module files from a ZIP file
     * @param string $file
     * @return boolean
     */
    protected function extractFromZip($file)
    {
        return $this->zip->set($file)->extract(GC_MODULE_DIR);
    }

    /**
     * Returns an array of files in a ZIP file
     * @param string $file
     * @return array
     */
    public function getFilesFromZip($file)
    {
        try {
            $files = $this->zip->set($file)->getList();
        } catch (ModuleException $e) {
            trigger_error($e->getMessage());
            return array();
        }

        if (count($files) < 2) {
            return array();
        }

        return $files;
    }

    /**
     * Returns a module id from zip file or false on error
     * @param string $file
     * @return boolean
     */
    public function getIdFromZip($file)
    {
        $list = $this->getFilesFromZip($file);

        if (empty($list)) {
            return false;
        }

        $folder = reset($list);

        if (strrchr($folder, '/') !== '/') {
            return false;
        }

        $nested = 0;
        foreach ($list as $item) {
            if (strpos($item, $folder) === 0) {
                $nested++;
            }
        }

        if (count($list) != $nested) {
            return false;
        }

        $id = rtrim($folder, '/');

        if ($this->checkModuleId($id) === true) {
            return $id;
        }

        return false;
    }

}
