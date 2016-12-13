<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Backup as BackupModel;
use core\models\Module as ModuleModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate backup/restore operations
 */
class Backup extends BaseValidator
{
    /**
     * Backup model instance
     * @var \core\models\Backup $backup
     */
    protected $backup;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Constructor
     * @param BackupModel $backup
     * @param ModuleModel $module
     */
    public function __construct(BackupModel $backup, ModuleModel $module)
    {
        parent::__construct();

        $this->backup = $backup;
        $this->module = $module;
    }

    /**
     * Performs full backup data validation
     * @param array $submitted
     * @param array $options
     */
    public function backup(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateName($options);
        $this->validateHandler($options);
        $this->validateModuleId($options);
        return $this->getResult();
    }

    /**
     * Performs full restore data validation
     * @param array $submitted
     * @param array $options
     */
    public function restore(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateBackup($options);
        $this->validateHandler($options);
        $this->validateModuleId($options);
        return $this->getResult();
    }

    /**
     * Validates a backup data
     * @param array $options
     * @return boolean
     */
    protected function validateBackup(array $options)
    {
        $backup = $this->getSubmitted('backup', $options);

        if (empty($backup)) {
            $vars = array('@field' => $this->language->text('Backup'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('backup', $error, $options);
            return false;
        }

        // Can be either integer or array
        // Try to load if it's an integer
        if (is_numeric($backup)) {

            $backup = $this->backup->get($backup);

            if (empty($backup)) {
                $vars = array('@name' => $this->language->text('Backup'));
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError('backup', $error, $options);
                return false;
            }

            $this->setSubmitted('backup', $backup, $options);
        }

        // Set handler and module ID to check later
        $this->setSubmitted('type', $backup['type'], $options);
        $this->setSubmitted('module_id', $backup['module_id'], $options);
        return true;
    }

    /**
     * Validates a backup type (handler ID)
     * @param array $options
     * @return boolean
     */
    protected function validateHandler(array $options)
    {
        $type = $this->getSubmitted('type', $options);

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $handler = $this->backup->getHandler($type);

        if (empty($handler)) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $this->setSubmitted('handler', $handler, $options);
        return true;
    }

    /**
     * Validates a module ID
     * @param array $options
     * @return boolean
     */
    protected function validateModuleId(array $options)
    {
        if ($this->isError('type', $options)) {
            return null;
        }

        $type = $this->getSubmitted('type', $options);
        $module_id = $this->getSubmitted('module_id', $options);

        if (!isset($module_id) && $type !== 'module') {
            return null;
        }

        $module = $this->module->get($module_id);

        if (empty($module)) {
            $vars = array('@name' => $this->language->text('Module'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('module_id', $error, $options);
            return false;
        }

        $this->setSubmitted('module', $module, $options);
        return true;
    }

}
