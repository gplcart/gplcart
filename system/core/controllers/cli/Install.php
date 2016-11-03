<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\cli;

use core\CliController;
use core\classes\Tool;
use core\models\User as ModelsUser;
use core\models\Install as ModelsInstall;

/**
 * Handles CLI commands related to system installation
 */
class Install extends CliController
{

    /**
     * Install model instance
     * @var \core\models\Install $install
     */
    protected $install;

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param ModelsInstall $install
     * @param ModelsUser $user
     */
    public function __construct(ModelsInstall $install, ModelsUser $user)
    {
        parent::__construct();

        $this->user = $user;
        $this->install = $install;
    }

    /**
     * Processes installation
     */
    public function storeInstall()
    {
        $map = $this->getMapInstall();
        $submitted = $this->setSubmitted($map);
        $generated_password = empty($submitted['user']['password']);

        $this->validateStoreInstall($submitted);

        if ($this->isError()) {
            $this->output();
        }

        $connect = $this->install->connect($submitted['database']);

        if ($connect !== true) {
            $this->setError($connect);
            $this->output();
        }

        $result = $this->install->full($submitted);

        if ($result !== true) {
            $this->setError($result);
        }

        $message = "Success. Your store is installed.\n"
                . "Go to {$submitted['store']['host']}/{$submitted['store']['basepath']}\n";
        
        if ($generated_password) {
            $message .= "Your admin password: {$submitted['user']['password']}\n";
        }

        $this->setMessage($message);
        $this->output();
    }

    /**
     * Validates submitted values
     * @param array $data
     */
    protected function validateStoreInstall(&$data)
    {

        // Required
        if (empty($data['database']['name'])) {
            $this->setError('--db-name is required option');
        }

        if (empty($data['user']['email'])) {
            $this->setError('--user-email is required option');
        } elseif (!filter_var($data['user']['email'], FILTER_VALIDATE_EMAIL)) {
            $this->setError('--user-email has invalid e-mail');
        }

        if (empty($data['store']['host'])) {
            $this->setError('--store-host is required option');
        }

        // Optional
        if (empty($data['database']['user'])) {
            $data['database']['user'] = 'root';
        }

        if (empty($data['database']['password'])) {
            $data['database']['password'] = '';
        }

        if (empty($data['database']['host'])) {
            $data['database']['host'] = 'localhost';
        }

        if (empty($data['database']['type'])) {
            $data['database']['type'] = 'mysql';
        }

        if (empty($data['database']['port'])) {
            $data['database']['port'] = 3306;
        } else if (!is_numeric($data['database']['port'])) {
            $this->setError('--db-port must be numeric');
        }

        if (empty($data['user']['password'])) {
            $data['user']['password'] = $this->user->generatePassword();
        } else {

            $limit = $this->user->getPasswordLength();
            $length = mb_strlen($data['user']['password']);

            if ($length > $limit['max'] || $length < $limit['min']) {
                $error = "--user-password must be {$limit['min']} - {$limit['max']} characters long";
                $this->setError($error);
            }
        }

        if (empty($data['store']['title'])) {
            $data['store']['title'] = 'GPL Cart';
        } else if (mb_strlen($data['store']['title']) > 255) {
            $this->setError('--store-title must contain no more than 255 characters');
        }

        if (empty($data['store']['basepath'])) {
            $data['store']['basepath'] = '';
        } else if (mb_strlen($data['store']['basepath']) > 255) {
            $this->setError('--store-basepath must contain no more than 255 characters');
        }

        if (empty($data['store']['timezone'])) {
            $data['store']['timezone'] = 'Europe/London';
        } else {

            $timezones = Tool::timezones();
            if (empty($timezones[$data['store']['timezone']])) {
                $this->setError('--store-timezone has wrong value. You can skip it and change later from UI');
            }
        }

        if (isset($data['installer'])) {
            $exists = $this->install->get($data['installer']);
            if (empty($exists)) {
                $list = implode(',', array_keys($this->install->getList()));
                $this->setError("--installer ID not found. Available installers: $list. You can skip this option for 'default'");
            }
        }
    }

    /**
     * Returns an array of mapping data used to determine references
     * between CLI options and real data passed to validator
     * 
     * @return array
     */
    protected function getMapInstall()
    {
        return array(
            'db-name' => 'database.name',
            'user-email' => 'user.email',
            'store-host' => 'store.host',
            'db-user' => 'database.user',
            'db-password' => 'database.password',
            'db-type' => 'database.type',
            'db-port' => 'database.port',
            'db-host' => 'database.host',
            'user-password' => 'user.password',
            'store-title' => 'store.title',
            'store-basepath' => 'store.basepath',
            'store-timezone' => 'store.timezone',
            'installer' => 'installer'
        );
    }

}
