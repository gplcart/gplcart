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
     */
    public function backup(array &$submitted)
    {
        $this->validateName($submitted);
        $this->validateHandler($submitted);
        $this->validateModuleId($submitted);
        return $this->getResult();
    }

    /**
     * Performs full restore data validation
     * @param array $submitted
     */
    public function restore(array &$submitted)
    {
        $this->validateBackup($submitted);
        $this->validateHandler($submitted);
        $this->validateModuleId($submitted);
        return $this->getResult();
    }

    /**
     * Validates a backup data
     * @param array $submitted
     * @return boolean
     */
    protected function validateBackup(array &$submitted)
    {
        if (empty($submitted['backup'])) {
            $vars = array('@field' => $this->language->text('Backup'));
            $this->errors['backup'] = $this->language->text('@field is required', $vars);
            return false;
        }

        // $submitted['backup'] can be either integer or array
        // Try to load if it's an integer
        if (is_numeric($submitted['backup'])) {

            $data = $this->backup->get($submitted['backup']);

            if (empty($data)) {
                $vars = array('@name' => $this->language->text('Backup'));
                $this->errors['backup'] = $this->language->text('Object @name does not exist', $vars);
                return false;
            }

            $submitted['backup'] = $data;
        }

        // Set handler and module ID to check later
        $submitted['type'] = $submitted['backup']['type'];
        $submitted['module_id'] = $submitted['backup']['module_id'];
        return true;
    }

    /**
     * Validates a backup type (handler ID)
     * @param array $submitted
     * @return boolean
     */
    protected function validateHandler(array &$submitted)
    {
        if (empty($submitted['type'])) {
            $vars = array('@field' => $this->language->text('Type'));
            $this->errors['type'] = $this->language->text('@field is required', $vars);
            return false;
        }

        $handler = $this->backup->getHandler($submitted['type']);

        if (empty($handler)) {
            $vars = array('@name' => $this->language->text('Type'));
            $this->errors['type'] = $this->language->text('Object @name does not exist', $vars);
            return false;
        }

        $submitted['handler'] = $handler;
        return true;
    }

    /**
     * Validates a module ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateModuleId(array &$submitted)
    {
        if ($this->isError('type')) {
            return null;
        }

        if (!isset($submitted['module_id']) && $submitted['type'] !== 'module') {
            return null;
        }

        $module = $this->module->get($submitted['module_id']);

        if (empty($module)) {
            $vars = array('@name' => $this->language->text('Module'));
            $this->errors['module_id'] = $this->language->text('Object @name does not exist', $vars);
            return false;
        }

        $submitted['module'] = $module;
        return true;
    }

}
