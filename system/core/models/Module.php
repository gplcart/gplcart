<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use DirectoryIterator;
use gplcart\core\Hook;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\Module as ModuleCore;
use gplcart\core\traits\Dependency as DependencyTrait;

/**
 * Manages basic behaviors and data related to modules
 */
class Module
{

    use DependencyTrait;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Module class instance
     * @var \gplcart\core\Module $module
     */
    protected $module;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param ModuleCore $module
     * @param Translation $translation
     */
    public function __construct(Hook $hook, ModuleCore $module, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->module = $module;
        $this->translation = $translation;
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

        $this->module->update($module_id, array('status' => 1));
        $this->module->clearCache();
        $this->setOverrideConfig();

        $this->hook->attach("module.enable.after|$module_id", $result, $this);
        return $result;
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

        if ($this->module->isInstalled($module_id)) {
            $this->module->update($module_id, array('status' => false));
        } else {
            $this->module->add(array('status' => false, 'module_id' => $module_id));
        }

        $this->module->clearCache();
        $this->setOverrideConfig();

        $this->hook->attach("module.disable.after|$module_id", $result, $this);
        return $result;
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
            $this->module->delete($module_id);
            return $result;
        }

        $this->module->add(array('module_id' => $module_id, 'status' => $status));
        $this->module->clearCache();
        $this->setOverrideConfig();

        $this->hook->attach("module.install.after|$module_id", $result, $this);
        return $result;
    }

    /**
     * Un-install a module
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

        $this->module->delete($module_id);
        $this->module->clearCache();
        $this->setOverrideConfig();

        $this->hook->attach("module.uninstall.after|$module_id", $result, $this);
        return $result;
    }

    /**
     * Whether a given module can be enabled
     * @param string $module_id
     * @return boolean|string
     */
    public function canEnable($module_id)
    {
        if ($this->module->isEnabled($module_id)) {
            return $this->translation->text('Module already installed and enabled');
        }

        if ($this->module->isLocked($module_id)) {
            return $this->translation->text('Module is locked in code');
        }

        if ($this->module->isInstaller($module_id)) {
            return $this->translation->text('Modules that are installers cannot be enabled');
        }

        try {
            $this->module->getInstance($module_id); // Test module class
        } catch (Exception $ex) {
            return $ex->getMessage();
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
        if ($this->module->isInstalled($module_id)) {
            return $this->translation->text('Module already installed');
        }

        if ($this->module->isLocked($module_id)) {
            return $this->translation->text('Module is locked in code');
        }

        if ($this->module->isInstaller($module_id)) {
            return $this->translation->text('Modules that are installers cannot be installed when system is set up');
        }

        try {
            $this->module->getInstance($module_id); // Test module
        } catch (Exception $ex) {
            return $ex->getMessage();
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
     * Whether a given module can be un-installed
     * @param string $module_id
     * @return mixed
     */
    public function canUninstall($module_id)
    {
        if ($this->module->isActiveTheme($module_id)) {
            return $this->translation->text('Modules that are active themes cannot be disabled/uninstalled');
        }

        if ($this->module->isLocked($module_id)) {
            return $this->translation->text('Module is locked in code');
        }

        $modules = $this->module->getList();
        $dependent = $this->checkDependentModules($module_id, $modules);

        return $dependent === true ? true : $dependent;
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

        $module = $this->module->getInfo($module_id);

        $result_core = $this->checkCore($module);

        if ($result_core !== true) {
            return $result_core;
        }

        $result_php = $this->checkPhpVersion($module);

        if ($result_php !== true) {
            return $result_php;
        }

        if ($this->module->isInstaller($module_id)) {
            return $this->translation->text('Modules that are installers cannot be installed/enabled when system is set up');
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

        $components = $this->getVersionComponents($module['php']);

        if (empty($components)) {
            return $this->translation->text('Requires incompatible version of @name', array('@name' => 'PHP'));
        }

        list($operator, $number) = $components;

        if (!version_compare(PHP_VERSION, $number, $operator)) {
            return $this->translation->text('Requires incompatible version of @name', array('@name' => 'PHP'));
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
        $modules = $this->module->getList(false); // Get non-cached modules
        $validated = $this->validateDependencies($modules, true);

        if (empty($validated[$module_id]['errors'])) {
            return true;
        }

        $translated = array();
        foreach ($validated[$module_id]['errors'] as $error) {
            list($text, $arguments) = $error;
            $translated[] = $this->translation->text($text, $arguments);
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
        if ($this->module->isValidId($module_id)) {
            return true;
        }

        return $this->translation->text('Invalid module ID');
    }

    /**
     * Checks core version requirements
     * @param array $module
     * @return boolean|string
     */
    public function checkCore(array $module)
    {
        if (empty($module['core'])) {
            return $this->translation->text('Missing core version');
        }

        if (version_compare(gplcart_version(), $module['core']) < 0) {
            return $this->translation->text('Module incompatible with the current system core version');
        }

        return true;
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
            return $this->translation->text('Required by') . ': ' . implode(', ', $required_by);
        }

        return true;
    }

    /**
     * Saves the override config file
     * @return boolean
     */
    public function setOverrideConfig()
    {
        return gplcart_config_set(GC_FILE_CONFIG_COMPILED_OVERRIDE, $this->getOverrideMap());
    }

    /**
     * Returns an array of class name spaces to be overridden
     * @return array
     */
    public function getOverrideMap()
    {
        gplcart_static_clear();

        $map = array();
        foreach ($this->module->getEnabled() as $module) {

            $directory = GC_DIR_MODULE . "/{$module['id']}/override/classes";

            if (!is_readable($directory)) {
                continue;
            }

            foreach ($this->scanOverrideFiles($directory) as $file) {
                $original = str_replace('/', '\\', str_replace($directory . '/', '', preg_replace('/Override$/', '', $file)));
                $override = str_replace('/', '\\', str_replace(GC_DIR_SYSTEM . '/', '', $file));
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
        foreach (new DirectoryIterator($directory) as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $results[] = $file->getFilename();
            } elseif ($file->isDir() && !$file->isDot()) {
                $this->scanOverrideFiles($file->getRealPath(), $results);
            }
        }

        return $results;
    }

}
