<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Install as InstallModel;

/**
 * Provides methods to validate installation data
 */
class Install extends Component
{

    /**
     * Install model instance
     * @var \gplcart\core\models\Install $install
     */
    protected $install;

    /**
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
        if ($this->isExcluded('requirements')) {
            return null;
        }

        if ($this->config->isInitialized()) {
            $error = $this->translation->text('System already installed');
            $this->setError('installed', $error);
            return false;
        }

        $requirements = $this->install->getRequirements();
        $errors = $this->install->getRequirementErrors($requirements);

        if (empty($errors['danger'])) {
            return true;
        }

        $messages = array();
        $messages[] = $this->translation->text('Please fix all critical errors in your environment');

        foreach ($requirements as $items) {
            foreach ($items as $name => $info) {
                if (in_array($name, $errors['danger'])) {
                    $status = empty($info['status']) ? $this->translation->text('No') : $this->translation->text('Yes');
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
        if ($this->isExcluded('user.email')) {
            return null;
        }

        $options = $this->options;
        $this->options['parents'] = 'user';
        $result = $this->validateEmail();
        $this->options = $options; // Restore original

        return $result;
    }

    /**
     * Validates a user password
     * @return boolean|null
     */
    protected function validateUserPasswordInstall()
    {
        $field = 'user.password';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Password');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        list($min, $max) = $this->user->getPasswordLength();

        $length = mb_strlen($value);

        if ($length < $min || $length > $max) {
            $this->setErrorLengthRange($field, $label, $min, $max);
            return false;
        }

        return true;
    }

    /**
     * Validates a host name (domain)
     * @return boolean
     */
    protected function validateStoreHostInstall()
    {
        $field = 'store.host';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $this->translation->text('Host'));
            return false;
        }

        return true;
    }

    /**
     * Validates a store title
     * @return boolean
     */
    protected function validateStoreTitleInstall()
    {
        $field = 'store.title';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value) || mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $this->translation->text('Title'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value) || $value === '') {
            return true;
        }

        if (preg_match('/^[a-z0-9-\/]{0,50}$/', $value) !== 1) {
            $this->setErrorInvalid($field, $this->translation->text('Base path'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setSubmitted($field, date_default_timezone_get());
            return true;
        }

        $timezones = gplcart_timezones();

        if (empty($timezones[$value])) {
            $this->setErrorInvalid($field, $this->translation->text('Timezone'));
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
        $field = 'installer';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setSubmitted('installer', 'default');
            return null;
        }

        $installer = $this->install->getHandler($value);

        if (empty($installer)) {
            $this->setErrorInvalid('installer', $this->translation->text('Installer'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $this->translation->text('Database name'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $this->translation->text('Database user'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $this->translation->text('Database host'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (empty($value)) {
            $this->setErrorRequired($field, $this->translation->text('Database type'));
            return false;
        }

        $drivers = \PDO::getAvailableDrivers();

        if (in_array($value, $drivers)) {
            return true;
        }

        $error = $this->translation->text('Unsupported database driver. Available drivers: @list', array(
            '@list' => implode(',', $drivers)));

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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);
        $label = $this->translation->text('Database port');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
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
            return null;
        }

        $field = 'database.connect';

        if ($this->isExcluded($field)) {
            return null;
        }

        $settings = $this->getSubmitted('database');
        $result = $this->install->connectDb($settings);

        if ($result === true) {
            return true;
        }

        if (empty($result)) {
            $result = $this->translation->text('Could not connect to database');
        }

        $this->setError($field, (string) $result);
        return false;
    }

}
