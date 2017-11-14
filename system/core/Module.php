<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Config;

/**
 * Parent class for modules
 */
abstract class Module
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
     * Returns a class instance
     * @param $string
     * @return object
     */
    protected function getInstance($string)
    {
        if (strpos($string, 'gplcart\\') === 0) {
            return Container::get($string);
        }

        return $this->config->getModuleInstance($string);
    }

    /**
     * Returns a class instance for the given module and model name
     * @param string $model
     * @param string|null $module_id
     * @return object
     */
    protected function getModel($model, $module_id = null)
    {
        if (!isset($module_id)) {
            return $this->getInstance("gplcart\\core\\models\\$model");
        }

        $base_namespace = $this->config->getModuleBaseNamespace($module_id);
        return $this->getInstance("$base_namespace\\models\\$model");
    }

    /**
     * Returns a class instance for the given module and handler name
     * @param string $helper
     * @param string|null $module_id
     * @return object
     */
    protected function getHelper($helper, $module_id = null)
    {
        if (!isset($module_id)) {
            return $this->getInstance("gplcart\\core\\helpers\\$helper");
        }

        $base_namespace = $this->config->getModuleBaseNamespace($module_id);
        return $this->getInstance("$base_namespace\\helpers\\$helper");
    }

    /**
     * Returns library instance
     * @return \gplcart\core\Library
     */
    protected function getLibrary()
    {
        /* @var $instance \gplcart\core\Library */
        $instance = $this->getInstance('gplcart\\core\\Library');
        return $instance;
    }

    /**
     * Returns language model instance
     * @return \gplcart\core\models\Language
     */
    protected function getLanguage()
    {
        /* @var $model \gplcart\core\models\Language */
        $model = $this->getModel('Language');
        return $model;
    }

    /**
     * Returns an asset directory or file
     * @param string $module_id
     * @param string $file
     * @param string|null $type
     * @return string
     */
    protected function getAsset($module_id, $file, $type = null)
    {
        if (!isset($type)) {
            $type = pathinfo($file, PATHINFO_EXTENSION);
        }

        $directory = $this->config->getModuleDirectory($module_id);
        return rtrim("$directory/$type/$file", '/');
    }

    /**
     * Install a database
     * @param string $table
     * @param array $scheme
     * @return boolean|string
     */
    protected function installDbTable($table, array $scheme)
    {
        $language = $this->getLanguage();

        if ($this->db->tableExists($table)) {
            return $language->text('Table @name already exists', array('@name' => $table));
        }

        if (!$this->db->import($scheme)) {
            $this->db->deleteTable($table);
            return $language->text('An error occurred while importing database table @name', array('@name' => $table));
        }

        return true;
    }

    /**
     * Returns a module template path without extension
     * @param string $module_id
     * @param string $template
     * @return string
     */
    protected function getTemplate($module_id, $template)
    {
        $directory = $this->config->getModuleDirectory($module_id);
        return "$directory/templates/$template";
    }

    /**
     * Delete all module settings
     * @param string $module_id
     */
    protected function deleteModuleSettings($module_id)
    {
        foreach (array_keys($this->config->select()) as $key) {
            if (strpos($key, "module_{$module_id}_") === 0) {
                $this->config->reset($key);
            }
        }
    }

}
