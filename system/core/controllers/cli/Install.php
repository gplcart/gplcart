<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\cli;

use core\CliController;
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
        $mapping = $this->getMappingInstall();
        $default = $this->getDefaultInstall();
        $this->setSubmittedMapped($mapping, $default);

        $this->validateStoreInstall();
        $this->processInstall();
    }

    /**
     * Performs full system installation and outputs resulting messages
     */
    protected function processInstall()
    {
        if ($this->isError()) {
            $this->output();
        }

        $submitted = $this->getSubmitted();
        $result = $this->install->full($submitted);

        if ($result === true) {
            $this->setMessageComplete($submitted);
        } else {
            $this->setError($result);
        }

        $this->output();
    }

    /**
     * Sets a message on success installation
     * @param array $submitted
     */
    protected function setMessageComplete(array $submitted)
    {
        $url = trim("{$submitted['store']['host']}/{$submitted['store']['basepath']}", '/');

        $message = "\nSuccess. Your store is installed.\n";
        $message .= "Front page: $url\n";
        $message .= "Admin area: $url/admin\n";

        if ($submitted['generated_password']) {
            $message .= "Your admin password: {$submitted['user']['password']}\n";
        }

        $message .= "Good luck!\n";

        $this->setMessage($message, 'success');
    }

    /**
     * Returns an array of default submitted values
     * @return array
     */
    protected function getDefaultInstall()
    {
        $data = array();

        $data['database']['port'] = 3306;
        $data['database']['user'] = 'root';
        $data['database']['type'] = 'mysql';
        $data['database']['host'] = 'localhost';

        $data['store']['basepath'] = '';
        $data['store']['host'] = 'localhost';
        $data['store']['title'] = 'GPL Cart';
        $data['store']['timezone'] = 'Europe/London';

        return $data;
    }

    /**
     * Validates submitted values
     */
    protected function validateStoreInstall()
    {
        $submitted = $this->getSubmitted();
        $submitted['generated_password'] = empty($submitted['user']['password']);

        if ($submitted['generated_password']) {
            $submitted['user']['password'] = $this->user->generatePassword();
        }

        $this->setSubmitted($submitted);
        $this->validate('install');
    }

    /**
     * Returns an array of mapping data used to determine references
     * between CLI options and real data passed to validator
     * 
     * @return array
     */
    protected function getMappingInstall()
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
