<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Install as InstallModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate installation data
 */
class Install extends BaseValidator
{

    /**
     * Install model instance
     * @var \gplcart\core\models\Install $install
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
     * @param array $options
     * @return array|boolean
     */
    public function install(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateRequirementsInstall($options);

        if ($this->isError()) {
            return $this->getError();
        }

        $this->validateUserEmailInstall($options);
        $this->validateUserPasswordInstall($options);
        $this->validateStoreHostInstall($options);
        $this->validateStoreTitleInstall($options);
        $this->validateStoreBasepathInstall($options);
        $this->validateStoreTimezoneInstall($options);
        $this->validateInstallerInstall($options);
        $this->validateDbNameInstall($options);
        $this->validateDbUserInstall($options);
        $this->validateDbPasswordInstall($options);
        $this->validateDbHostInstall($options);
        $this->validateDbTypeInstall($options);
        $this->validateDbPortInstall($options);
        $this->validateDbConnectInstall($options);

        return $this->getResult();
    }

    /**
     * Checks system requirements
     * @param array $options
     * @return boolean
     */
    protected function validateRequirementsInstall(array $options)
    {
        if ($this->install->isInstalled()) {
            $error = $this->language->text('System already installed');
            $this->setError('installed', $error, $options);
            return false;
        }

        $requirements = $this->install->getRequirements();
        $errors = $this->install->getRequirementErrors($requirements);

        if (empty($errors['danger'])) {
            return true;
        }

        $messages = array();
        $messages[] = $this->language->text('Please fix all critical errors in your environment');

        foreach ($requirements as $items) {
            foreach ($items as $name => $info) {
                if (in_array($name, $errors['danger'])) {
                    $status = empty($info['status']) ? $this->language->text('No') : $this->language->text('Yes');
                    $messages[] = " {$info['message']} - $status";
                }
            }
        }

        $this->setError('requirements', implode(PHP_EOL, $messages), $options);
        return false;
    }

    /**
     * Validates a user E-mail
     * @param array $options
     * @return boolean
     */
    protected function validateUserEmailInstall(array $options)
    {
        $options += array('parents' => 'user');
        return $this->validateEmail($options);
    }

    /**
     * Validates a user password
     * @param array $options
     * @return boolean
     */
    protected function validateUserPasswordInstall(array $options)
    {
        $password = $this->getSubmitted('user.password', $options);

        if (empty($password)) {
            $vars = array('@field' => $this->language->text('Password'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user.password', $error, $options);
            return false;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($password);

        if ($length < $limit['min'] || $length > $limit['max']) {
            $vars = array('@min' => $limit['min'], '@max' => $limit['max'], '@field' => $this->language->text('Password'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('user.password', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a hostname (domain)
     * @param array $options
     * @return boolean
     */
    protected function validateStoreHostInstall(array $options)
    {
        $host = $this->getSubmitted('store.host', $options);

        if (empty($host)) {
            $vars = array('@field' => $this->language->text('Host'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store.host', $error, $options);
            return false;
        }

        if ($host === 'localhost' || gplcart_valid_domain($host)) {
            return true;
        }

        $vars = array('@field' => $this->language->text('Host'));
        $error = $this->language->text('@field has invalid value', $vars);
        $this->setError('store.host', $error, $options);
        return false;
    }

    /**
     * Validates a store title
     * @param array $options
     * @return boolean
     */
    protected function validateStoreTitleInstall(array $options)
    {
        $title = $this->getSubmitted('store.title', $options);

        if (empty($title) || mb_strlen($title) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('store.title', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates store base path
     * @param array $options
     * @return boolean
     */
    protected function validateStoreBasepathInstall(array $options)
    {
        $basepath = $this->getSubmitted('store.basepath', $options);

        if (!isset($basepath) || $basepath === '') {
            return true;
        }

        if (preg_match('/^[a-z0-9]{0,50}$/', $basepath) !== 1) {
            $error = $this->language->text('Invalid basepath');
            $this->setError('store.basepath', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates the store time zone
     * @param array $options
     * @return boolean
     */
    protected function validateStoreTimezoneInstall(array $options)
    {
        $timezone = $this->getSubmitted('store.timezone', $options);

        if (empty($timezone)) {
            $vars = array('@field' => $this->language->text('Timezone'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store.timezone', $error, $options);
            return false;
        }

        $timezones = gplcart_timezones();

        if (empty($timezones[$timezone])) {
            $error = $this->language->text('Invalid timezone');
            $this->setError('store.timezone', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an installer ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateInstallerInstall(array $options)
    {
        $installer_id = $this->getSubmitted('installer', $options);

        if (empty($installer_id)) {
            return null;
        }

        $installer = $this->install->get($installer_id);

        if (empty($installer)) {

            $installers = $this->install->getList();
            $list = implode(',', array_keys($installers));

            $vars = array('@field' => $this->language->text('Installer'), '@allowed' => $list);
            $error = $this->language->text('@field has invalid value. Allowed values: @allowed', $vars);

            $this->setError('installer', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a database name
     * @param array $options
     * @return boolean
     */
    protected function validateDbNameInstall(array $options)
    {
        $dbname = $this->getSubmitted('database.name', $options);

        if (empty($dbname)) {
            $vars = array('@field' => $this->language->text('Database name'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.name', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a database user name
     * @param array $options
     * @return boolean
     */
    protected function validateDbUserInstall(array $options)
    {
        $dbuser = $this->getSubmitted('database.user', $options);

        if (empty($dbuser)) {
            $vars = array('@field' => $this->language->text('Database user'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.user', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a database password
     * @param array $options
     * @return boolean|null
     */
    protected function validateDbPasswordInstall(array $options)
    {
        $dbpassword = $this->getSubmitted('database.password', $options);

        if (!isset($dbpassword)) {
            $this->setSubmitted('database.password', '', $options);
            return null;
        }

        return true;
    }

    /**
     * Validates a database host
     * @param array $options
     * @return boolean
     */
    protected function validateDbHostInstall(array $options)
    {
        $dbhost = $this->getSubmitted('database.host', $options);

        if (empty($dbhost)) {
            $vars = array('@field' => $this->language->text('Database host'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.host', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a database driver
     * @param array $options
     * @return boolean
     */
    protected function validateDbTypeInstall(array $options)
    {
        $dbtype = $this->getSubmitted('database.type', $options);

        if (empty($dbtype)) {
            $vars = array('@field' => $this->language->text('Database type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.type', $error, $options);
            return false;
        }

        $drivers = \PDO::getAvailableDrivers();

        if (in_array($dbtype, $drivers)) {
            return true;
        }

        $vars = array('@list' => implode(',', $drivers));
        $error = $this->language->text('Unsupported database driver. Available drivers: @list', $vars);
        $this->setError('database.type', $error, $options);
        return false;
    }

    /**
     * Validates a database port
     * @param array $options
     * @return boolean
     */
    protected function validateDbPortInstall(array $options)
    {
        $dbport = $this->getSubmitted('database.port', $options);

        if (empty($dbport)) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.port', $error, $options);
            return false;
        }

        if (!is_numeric($dbport)) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('database.port', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates database connection
     * @param array $options
     * @return boolean
     */
    protected function validateDbConnectInstall(array $options)
    {
        if ($this->isError()) {
            return null; // Do not connect to the database if an error has occurred
        }

        $settings = $this->getSubmitted('database', $options);
        $result = $this->install->connect($settings);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            $result = $this->language->text('Could not connect to database');
        }

        $this->setError('database.connect', (string) $result, $options);
        return false;
    }

}
