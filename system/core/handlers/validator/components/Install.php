<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

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

        $result = $this->validateEmailComponent();

        $this->options = $options; // Restore original
        return $result;
    }

    /**
     * Validates a user password
     * @return boolean
     */
    protected function validateUserPasswordInstall()
    {
        $field = 'user.password';
        $label = $this->language->text('Password');
        $password = $this->getSubmitted($field);

        if (empty($password)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($password);

        if ($length < $limit['min'] || $length > $limit['max']) {
            $this->setErrorLengthRange($field, $label, $limit['min'], $limit['max']);
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
        $field = 'store.host';
        $label = $this->language->text('Host');
        $host = $this->getSubmitted($field);

        if (empty($host)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if ($host === 'localhost' || gplcart_valid_domain($host)) {
            return true;
        }

        $this->setErrorInvalidValue($field, $label);
        return false;
    }

    /**
     * Validates a store title
     * @return boolean
     */
    protected function validateStoreTitleInstall()
    {
        $field = 'store.title';
        $title = $this->getSubmitted($field);

        if (empty($title) || mb_strlen($title) > 255) {
            $this->setErrorLengthRange($field, $this->language->text('Title'));
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
        $field = 'store.basepath';
        $basepath = $this->getSubmitted($field);

        if (!isset($basepath) || $basepath === '') {
            return true;
        }

        if (preg_match('/^[a-z0-9]{0,50}$/', $basepath) !== 1) {
            $this->setErrorInvalidValue($field, $this->language->text('Base path'));
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
        $field = 'store.timezone';
        $label = $this->language->text('Timezone');
        $timezone = $this->getSubmitted($field);

        if (empty($timezone)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $timezones = gplcart_timezones();

        if (empty($timezones[$timezone])) {
            $this->setErrorInvalidValue($field, $label);
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
        $field = 'database.name';
        $dbname = $this->getSubmitted($field);

        if (empty($dbname)) {
            $this->setErrorRequired($field, $this->language->text('Database name'));
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
        $field = 'database.user';
        $dbuser = $this->getSubmitted($field);

        if (empty($dbuser)) {
            $this->setErrorRequired($field, $this->language->text('Database user'));
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
        $field = 'database.password';
        $dbpassword = $this->getSubmitted($field);

        if (!isset($dbpassword)) {
            $this->setSubmitted($field, '');
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
        $field = 'database.host';
        $dbhost = $this->getSubmitted($field);

        if (empty($dbhost)) {
            $this->setErrorRequired($field, $this->language->text('Database host'));
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
        $field = 'database.type';
        $dbtype = $this->getSubmitted($field);

        if (empty($dbtype)) {
            $this->setErrorRequired($field, $this->language->text('Database type'));
            return false;
        }

        $drivers = \PDO::getAvailableDrivers();

        if (in_array($dbtype, $drivers)) {
            return true;
        }

        $vars = array('@list' => implode(',', $drivers));
        $error = $this->language->text('Unsupported database driver. Available drivers: @list', $vars);
        $this->setError($field, $error);
        return false;
    }

    /**
     * Validates a database port
     * @return boolean
     */
    protected function validateDbPortInstall()
    {
        $field = 'database.port';
        $label = $this->language->text('Database port');
        $dbport = $this->getSubmitted($field);

        if (empty($dbport)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($dbport)) {
            $this->setErrorNumeric($field, $label);
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
