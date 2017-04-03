<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Install as InstallModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate installation data
 */
class Install extends ComponentValidator
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
    public function install(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateRequirementsInstall();

        if ($this->isError()) {
            return $this->getError();
        }

        $this->validateUserEmailInstall();
        $this->validateUserPasswordInstall();
        $this->validateStoreHostInstall();
        $this->validateStoreTitleInstall();
        $this->validateStoreBasepathInstall();
        $this->validateStoreTimezoneInstall();
        $this->validateInstallerInstall();
        $this->validateDbNameInstall();
        $this->validateDbUserInstall();
        $this->validateDbPasswordInstall();
        $this->validateDbHostInstall();
        $this->validateDbTypeInstall();
        $this->validateDbPortInstall();
        $this->validateDbConnectInstall();

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

        $this->setError('requirements', implode(PHP_EOL, $messages));
        return false;
    }

    /**
     * Validates a user E-mail
     * @return boolean
     */
    protected function validateUserEmailInstall()
    {
        $options = $this->options;
        $this->options['parents'] = 'user';

        $result = $this->validateEmail();

        $this->options = $options; // Restore original
        return $result;
    }

    /**
     * Validates a user password
     * @return boolean
     */
    protected function validateUserPasswordInstall()
    {
        $password = $this->getSubmitted('user.password');

        if (empty($password)) {
            $vars = array('@field' => $this->language->text('Password'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('user.password', $error);
            return false;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($password);

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
     * @return boolean
     */
    protected function validateStoreHostInstall()
    {
        $host = $this->getSubmitted('store.host');

        if (empty($host)) {
            $vars = array('@field' => $this->language->text('Host'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store.host', $error);
            return false;
        }

        if ($host === 'localhost' || gplcart_valid_domain($host)) {
            return true;
        }

        $vars = array('@field' => $this->language->text('Host'));
        $error = $this->language->text('@field has invalid value', $vars);
        $this->setError('store.host', $error);
        return false;
    }

    /**
     * Validates a store title
     * @return boolean
     */
    protected function validateStoreTitleInstall()
    {
        $title = $this->getSubmitted('store.title');

        if (empty($title) || mb_strlen($title) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('store.title', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates store base path
     * @return boolean
     */
    protected function validateStoreBasepathInstall()
    {
        $basepath = $this->getSubmitted('store.basepath');

        if (!isset($basepath) || $basepath === '') {
            return true;
        }

        if (preg_match('/^[a-z0-9]{0,50}$/', $basepath) !== 1) {
            $error = $this->language->text('Invalid basepath');
            $this->setError('store.basepath', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates the store time zone
     * @return boolean
     */
    protected function validateStoreTimezoneInstall()
    {
        $timezone = $this->getSubmitted('store.timezone');

        if (empty($timezone)) {
            $vars = array('@field' => $this->language->text('Timezone'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('store.timezone', $error);
            return false;
        }

        $timezones = gplcart_timezones();

        if (empty($timezones[$timezone])) {
            $error = $this->language->text('Invalid timezone');
            $this->setError('store.timezone', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates an installer ID
     * @return boolean|null
     */
    protected function validateInstallerInstall()
    {
        $installer_id = $this->getSubmitted('installer');

        if (empty($installer_id)) {
            return null;
        }

        $installer = $this->install->get($installer_id);

        if (empty($installer)) {

            $installers = $this->install->getList();
            $list = implode(',', array_keys($installers));

            $vars = array('@field' => $this->language->text('Installer'), '@allowed' => $list);
            $error = $this->language->text('@field has invalid value. Allowed values: @allowed', $vars);

            $this->setError('installer', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database name
     * @return boolean
     */
    protected function validateDbNameInstall()
    {
        $dbname = $this->getSubmitted('database.name');

        if (empty($dbname)) {
            $vars = array('@field' => $this->language->text('Database name'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.name', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database user name
     * @return boolean
     */
    protected function validateDbUserInstall()
    {
        $dbuser = $this->getSubmitted('database.user');

        if (empty($dbuser)) {
            $vars = array('@field' => $this->language->text('Database user'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.user', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database password
     * @return boolean|null
     */
    protected function validateDbPasswordInstall()
    {
        $dbpassword = $this->getSubmitted('database.password');

        if (!isset($dbpassword)) {
            $this->setSubmitted('database.password', '');
            return null;
        }

        return true;
    }

    /**
     * Validates a database host
     * @return boolean
     */
    protected function validateDbHostInstall()
    {
        $dbhost = $this->getSubmitted('database.host');

        if (empty($dbhost)) {
            $vars = array('@field' => $this->language->text('Database host'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.host', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a database driver
     * @return boolean
     */
    protected function validateDbTypeInstall()
    {
        $dbtype = $this->getSubmitted('database.type');

        if (empty($dbtype)) {
            $vars = array('@field' => $this->language->text('Database type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.type', $error);
            return false;
        }

        $drivers = \PDO::getAvailableDrivers();

        if (in_array($dbtype, $drivers)) {
            return true;
        }

        $vars = array('@list' => implode(',', $drivers));
        $error = $this->language->text('Unsupported database driver. Available drivers: @list', $vars);
        $this->setError('database.type', $error);
        return false;
    }

    /**
     * Validates a database port
     * @return boolean
     */
    protected function validateDbPortInstall()
    {
        $dbport = $this->getSubmitted('database.port');

        if (empty($dbport)) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('database.port', $error);
            return false;
        }

        if (!is_numeric($dbport)) {
            $vars = array('@field' => $this->language->text('Database port'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('database.port', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates database connection
     * @return boolean
     */
    protected function validateDbConnectInstall()
    {
        if ($this->isError()) {
            return null; // Do not connect to the database if an error has occurred
        }

        $settings = $this->getSubmitted('database');
        $result = $this->install->connect($settings);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            $result = $this->language->text('Could not connect to database');
        }

        $this->setError('database.connect', (string) $result);
        return false;
    }

}
