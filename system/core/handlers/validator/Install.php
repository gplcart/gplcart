<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Install as InstallModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate installation data
 */
class Install extends BaseValidator
{

    /**
     * Install model instance
     * @var \core\models\Install $install
     */
    protected $install;

    /**
     * Constructor
     * @param InstallModel $install
     */
    public function __construct(InstallModel $install)
    {
        parent::__construct();

        $this->install = $install;
    }

    /**
     * Performs full installation data validation
     * @param array $submitted
     */
    public function install(array &$submitted)
    {
        $this->validateRequirementsInstall();

        if (!empty($this->errors)) {
            return $this->errors;
        }

        $this->validateUserEmailInstall($submitted);
        $this->validateUserPasswordInstall($submitted);
        $this->validateStoreHostInstall($submitted);
        $this->validateStoreTitleInstall($submitted);
        $this->validateStoreBasepathInstall($submitted);
        $this->validateStoreTimezoneInstall($submitted);
        $this->validateInstallerInstall($submitted);
        $this->validateDbNameInstall($submitted);
        $this->validateDbUserInstall($submitted);
        $this->validateDbPasswordInstall($submitted);
        $this->validateDbHostInstall($submitted);
        $this->validateDbTypeInstall($submitted);
        $this->validateDbPortInstall($submitted);
        $this->validateDbConnectInstall($submitted);

        return $this->getResult();
    }

    /**
     * Checks system requirements
     * @return boolean
     */
    protected function validateRequirementsInstall()
    {
        if ($this->install->isInstalled()) {
            $error = $this->language->text('System already installed');
            $this->setError('installed', $error);
            return false;
        }

        $requirements = $this->install->getRequirements();
        $errors = $this->install->getRequirementErrors($requirements);

        if (empty($errors['danger'])) {
            return true;
        }

        $messages = $this->language->text('Please fix all critical errors in your environment') . ":\n";

        foreach ($requirements as $items) {
            foreach ($items as $name => $info) {

                if (!in_array($name, $errors['danger'])) {
                    continue;
                }

                $status = empty($info['status']) ? $this->language->text('No') : $this->language->text('Yes');
                $messages .= "  {$info['message']} - $status\n";
            }
        }

        $this->setError('requirements', $messages);
        return false;
    }

    /**
     * Validates a user E-mail
     * @param array $submitted
     * @return boolean
     */
    protected function validateUserEmailInstall(array &$submitted)
    {
        if (empty($submitted['user']['email'])) {
            $vars = array('@field' => $this->language->text('Email'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user.email', $error);
            return false;
        }

        if (!filter_var($submitted['user']['email'], FILTER_VALIDATE_EMAIL)) {
            $error = $this->language->text('Invalid E-mail');
            $this->setError('user.email', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a user password
     * @param array $submitted
     * @return boolean
     */
    protected function validateUserPasswordInstall(array &$submitted)
    {
        if (empty($submitted['user']['password'])) {
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Password')
            ));
            $this->setError('user.password', $error);
            return false;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($submitted['user']['password']);

        if ($length < $limit['min'] || $length > $limit['max']) {
            $vars = array('@min' => $limit['min'], '@max' => $limit['max'], '@field' => $this->language->text('Password'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('user.password', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a hostname (domain)
     * @param array $submitted
     * @return boolean
     */
    protected function validateStoreHostInstall(array &$submitted)
    {
        if (empty($submitted['store']['host'])) {
            $error = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Host')
            ));
            $this->setError('store.host', $error);
            return false;
        }

        if ($submitted['store']['host'] === 'localhost') {
            return true;
        }

        if (gplcart_valid_domain($submitted['store']['host'])) {
            return true;
        }

        $error = $this->language->text('Invalid host');
        $this->setError('store.host', $error);
        return false;
    }

    /**
     * Validates a store title
     * @param array $submitted
     * @return boolean
     */
    protected function validateStoreTitleInstall(array &$submitted)
    {
        if (empty($submitted['store']['title']) || mb_strlen($submitted['store']['title']) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('store.title', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates store base path
     * @param array $submitted
     * @return boolean
     */
    protected function validateStoreBasepathInstall(array &$submitted)
    {
        if (!isset($submitted['store']['basepath'])//
                || $submitted['store']['basepath'] === '') {
            return true;
        }

        if (!preg_match('/^[a-z0-9]{0,50}$/', $submitted['store']['basepath'])) {
            $error = $this->language->text('Invalid basepath');
            $this->setError('store.basepath', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates the store time zone
     * @param array $submitted
     * @return boolean
     */
    protected function validateStoreTimezoneInstall(array $submitted)
    {
        if (empty($submitted['store']['timezone'])) {
            $vars = array('@field' => $this->language->text('Timezone'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store.timezone', $error);
            return false;
        }

        $timezones = gplcart_timezones();

        if (empty($timezones[$submitted['store']['timezone']])) {
            $error = $this->language->text('Invalid timezone');
            $this->setError('store.timezone', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates an installer ID
     * @param array $submitted
     * @return boolean
     */
    protected function validateInstallerInstall(array &$submitted)
    {
        if (empty($submitted['installer'])) {
            return null;
        }

        $installer = $this->install->get($submitted['installer']);

        if (empty($installer)) {
            $installers = $this->install->getList();
            $list = implode(',', array_keys($installers));
            $error = $this->language->text("Invalid installer ID. Available installers: @list", array('@list' => $list));
            $this->setError('installer', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database name
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbNameInstall(array &$submitted)
    {
        if (empty($submitted['database']['name'])) {
            $vars = array('@field' => $this->language->text('Database name'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.name', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database user name
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbUserInstall(array &$submitted)
    {
        if (empty($submitted['database']['user'])) {
            $vars = array('@field' => $this->language->text('Database user'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.user', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database password
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbPasswordInstall(array &$submitted)
    {
        if (!isset($submitted['database']['password'])) {
            $submitted['database']['password'] = '';
            return null;
        }
    }

    protected function validateDbHostInstall(array $submitted)
    {
        if (empty($submitted['database']['host'])) {
            $vars = array('@field' => $this->language->text('Database host'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.host', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database driver
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbTypeInstall(array &$submitted)
    {
        if (empty($submitted['database']['type'])) {
            $vars = array('@field' => $this->language->text('Database type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.type', $error);
            return false;
        }

        $drivers = \PDO::getAvailableDrivers();

        if (in_array($submitted['database']['type'], $drivers)) {
            return true;
        }

        $vars = array('@list' => implode(',', $drivers));
        $error = $this->language->text('Unsupported database driver. Available drivers: @list', $vars);
        $this->setError('database.type', $error);
        return false;
    }

    /**
     * Validates a database port
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbPortInstall(array &$submitted)
    {
        if (empty($submitted['database']['port'])) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.port', $error);
            return false;
        }

        if (!is_numeric($submitted['database']['port'])) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('database.port', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates database connection
     * @param array $submitted
     * @return boolean
     */
    protected function validateDbConnectInstall(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        $result = $this->install->connect($submitted['database']);

        if ($result === true) {
            return true;
        }

        $this->setError('database.connect', $result);
        return false;
    }

}
