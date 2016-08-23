<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use ZipArchive;
use core\Model;
use core\Container;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to modules
 */
class Module extends Model
{

    /**
     * ZipArchive instance
     * @var \ZipArchive $zip
     */
    protected $zip;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ZipArchive $zip
     */
    public function __construct(ModelsLanguage $language, ZipArchive $zip)
    {
        parent::__construct();

        $this->zip = $zip;
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

        if (is_callable(array($module['class'], 'beforeEnable'))) {
            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeEnable();
            } catch (\core\exceptions\UsageModule $e) {
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

        return true;
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
            'status' => !empty($data['status']),
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
            $this->add(array('module_id' => $module_id, 'status' => true, 'settings' => $settings));
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

        if (is_callable(array($module['class'], 'beforeDisable'))) {

            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeDisable();
            } catch (\core\exceptions\UsageModule $e) {
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->update($module_id, array('status' => false));
            $this->setOverrideConfig();
            return true;
        }
        return $result;
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
                if ($dependent == $module_id && !empty($info['status'])) {
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

        if (is_callable(array($module['class'], 'beforeInstall'))) {
            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeInstall();
            } catch (\core\exceptions\UsageModule $e) {
                // uninstall trouble module
                $this->db->delete('module', array('module_id' => $module_id));
                echo $e->getMessage();
            }
        }

        if ($result === true) {
            $this->add(array('module_id' => $module_id, 'status' => $status));
            $this->setOverrideConfig();
            $this->setTranslations($module_id);
            return true;
        }

        return $result;
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

        return Tool::deleteDirecoryRecursive(GC_MODULE_DIR . "/$module_id");
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
     * Uninstalls a module
     * @param string $module_id
     * @return mixed
     */
    public function uninstall($module_id)
    {
        $result = $this->canUninstall($module_id);
        $module = $this->get($module_id);

        if (is_callable(array($module['class'], 'beforeUninstall'))) {
            try {
                $module_class = Container::instance($module['class']);
                $result = $module_class->beforeUninstall();
            } catch (\core\exceptions\UsageModule $e) {
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
            } elseif ($value != '.' && $value != '..') {
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
            return glob("$directory/*.{po}", GLOB_BRACE);
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
        if (!file_exists($file)) {
            return $this->language->text('File %file not found', array('%file' => $file));
        }

        $module_id = $this->getIdFromZip($file);

        if (empty($module_id)) {
            return $this->language->text('Invalid zip content');
        }

        // Only disabled existing modules can by updated
        if ($this->isEnabled($module_id)) {
            return $this->language->text('Module %id already installed and enabled.'
                            . ' Disable it before updating.', array('%id' => $module_id));
        }

        $backup = $this->backup($module_id);

        if (empty($backup)) {
            return $this->language->text('Failed to backup module %id', array(
                        '%id' => $module_id));
        }

        $extracted = $this->zip->extractTo(GC_MODULE_DIR);

        if (!$extracted) {
            return $this->language->text('Failed to extract module files');
        }

        if ($this->isInstalled($module_id)) {
            return true;
        }

        return $this->install($module_id, false); // Install but not enable
    }

    /**
     * Backups existing module by renaming its folder name
     * @param string $module_id
     * @return boolean|string
     */
    protected function backup($module_id)
    {
        $suffix = $this->getBackupKey();
        $source = GC_MODULE_DIR . "/$module_id";
        $target = $source . $suffix . date("Y-m-d-H-i-s");

        if (!file_exists($source)) {
            return true;
        }

        return rename($source, $target) ? $target : false;
    }

    /**
     * Returns a string containing key to be appended to module folder name
     * @return string
     */
    public function getBackupKey()
    {
        return '~backup';
    }

    /**
     * Returns an array of files in the zip
     * @param string $file
     * @return boolean|array
     */
    public function getFilesFromZip($file)
    {
        $zip = $this->zip->open($file);

        if (empty($zip)) {
            return false;
        }

        // At leas 2 files needed - folder and main module class
        if ($this->zip->numFiles < 2) {
            return false;
        }

        // Get simple array of zip files
        $list = array();
        for ($i = 0; $i < $this->zip->numFiles; $i++) {
            $list[] = $this->zip->getNameIndex($i);
        }

        return $list;
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

        if ('/' !== strrchr($folder, '/')) {
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

        if (!Tool::validModuleId($id)) {
            return false;
        }

        return $id;
    }

}
