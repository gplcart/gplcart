<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\cli;

use gplcart\core\CliController;
use gplcart\core\models\Install as InstallModel;

/**
 * Handles CLI commands related to system installation
 */
class Install extends CliController
{

    /**
     * Install model instance
     * @var \gplcart\core\models\Install $install
     */
    protected $install;

    /**
     * Installation language
     * @var string
     */
    protected $langcode;

    /**
     * @param InstallModel $install
     */
    public function __construct(InstallModel $install)
    {
        parent::__construct();

        $this->install = $install;
    }

    /**
     * Performs one-step "fast" installation
     */
    public function fastInstall()
    {
        $this->controlAccessInstall();

        $this->validateFastInstall();
        $this->processInstall();
        $this->output();
    }

    /**
     * Validates fast installation
     */
    protected function validateFastInstall()
    {
        $mapping = $this->getMappingInstall();
        $default = $this->getDefaultInstall();

        $this->setSubmittedMapped($mapping, null, $default);
        $this->validateComponent('install');
        $this->outputErrors(null, true);
    }

    /**
     * Returns an array of mapping data used to determine references
     * between CLI options and real data passed to validator
     * @return array
     */
    protected function getMappingInstall()
    {
        return array(
            'db-name' => 'database.name',
            'db-user' => 'database.user',
            'db-password' => 'database.password',
            'db-type' => 'database.type',
            'db-port' => 'database.port',
            'db-host' => 'database.host',
            'user-email' => 'user.email',
            'user-password' => 'user.password',
            'store-host' => 'store.host',
            'store-title' => 'store.title',
            'store-basepath' => 'store.basepath',
            'store-timezone' => 'store.timezone',
            'installer' => 'installer'
        );
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
        $data['database']['password'] = '';
        $data['store']['basepath'] = '';
        $data['store']['title'] = 'GPL Cart';
        $data['store']['host'] = $this->getHostInstall();
        $data['store']['timezone'] = date_default_timezone_get();

        return $data;
    }

    /**
     * Performs step-by-step installation
     */
    public function wizardInstall()
    {
        $this->controlAccessInstall();

        $this->validateWizardInstall();
        $this->processInstall();
        $this->output();
    }

    /**
     * Controls access to installation process
     */
    protected function controlAccessInstall()
    {
        if ($this->config->exists()) {
            $this->outputErrors($this->text('System already installed'), true);
        }
    }

    /**
     * Does installation
     */
    protected function processInstall()
    {
        $settings = $this->getSubmitted();

        // Make sure the database is set up
        // If a connection error has occurred before the database remains uninitialized
        $this->install->connect($settings['database']);

        $result = $this->install->full($settings);

        $message = '';
        if ($result === true) {
            $message = $this->getMessageCompletedInstall();
        }

        $this->hook->fire('cli.install.after', $result, $message, $this);
        $this->line($message);
    }

    /**
     * Sets a message on success installation
     * @return string
     */
    protected function getMessageCompletedInstall()
    {
        $host = $this->getSubmitted('store.host');
        $basepath = $this->getSubmitted('store.basepath');
        $vars = array('@url' => rtrim("$host/$basepath", '/'));
        return $this->text("Your store has been installed.\nURL: @url\nAdmin area: @url/admin\nGood luck!", $vars);
    }

    /**
     * Display simple installation wizard and validates user input
     */
    protected function validateWizardInstall()
    {
        $this->validateInputLanguageInstall();
        $this->validateRequirementsInstall();
        $this->validateInputTitleInstall();
        $this->validateInputEmailInstall();
        $this->validateInputPasswordInstall();
        $this->validateInputBasepathInstall();
        $this->validateInputDbInstall();
        $this->validateInputInstall();
    }

    /**
     * Validates all collected input
     */
    protected function validateInputInstall()
    {
        $language = array(
            $this->langcode => $this->language->getIso($this->langcode)
        );

        $this->setSubmitted('store.language', $language);
        $this->setSubmitted('store.host', $this->getHostInstall());
        $this->setSubmitted('store.timezone', date_default_timezone_get());

        $this->validateComponent('install');

        if ($this->isError('database')) {
            $this->outputErrors();
            $this->validateInputDbInstall();
        }

        $this->outputErrors(null, true);
    }

    /**
     * Returns the current host
     * @return string
     */
    protected function getHostInstall()
    {
        return GC_WIN ? gethostbyname(gethostname()) : gethostname();
    }

    /**
     * Validate database details input
     */
    protected function validateInputDbInstall()
    {
        $this->validateInputDbNameInstall();
        $this->validateInputDbUserInstall();
        $this->validateInputDbPasswordInstall();
        $this->validateInputDbPortInstall();
        $this->validateInputDbHostInstall();
        $this->validateInputDbTypeInstall();
    }

    /**
     * Validates a language input
     */
    protected function validateInputLanguageInstall()
    {
        $this->langcode = 'en';
        $languages = $this->language->getAvailable();
        $languages[$this->langcode] = true;

        if (count($languages) > 1) {
            $selected = $this->menu(array_keys($languages), 'en', $this->text('Language (enter a number)'));
            if (empty($selected)) {
                $this->outputErrors($this->text('Invalid language'));
                $this->validateInputLanguageInstall();
            } else {
                $this->langcode = (string) $selected;
                $this->language->set($this->langcode);
            }
        }
    }

    /**
     * Validates system requirements
     */
    protected function validateRequirementsInstall()
    {
        $this->validateComponent('install', array('field' => 'requirements'));
        $this->outputErrors(null, true);
    }

    /**
     * Validates store title
     */
    protected function validateInputTitleInstall()
    {
        $input = $this->prompt($this->text('Store title'), 'GPL Cart');
        if (!$this->isValidInput($input, 'store.title', 'install')) {
            $this->outputErrors();
            $this->validateInputTitleInstall();
        }
    }

    /**
     * Validates a user E-mail
     */
    protected function validateInputEmailInstall()
    {
        $input = $this->prompt($this->text('E-mail'), '');
        if (!$this->isValidInput($input, 'user.email', 'install')) {
            $this->outputErrors();
            $this->validateInputEmailInstall();
        }
    }

    /**
     * Validates user password
     */
    protected function validateInputPasswordInstall()
    {
        $input = $this->prompt($this->text('Password'), '');
        if (!$this->isValidInput($input, 'user.password', 'install')) {
            $this->outputErrors();
            $this->validateInputPasswordInstall();
        }
    }

    /**
     * Validates server basepath input
     */
    protected function validateInputBasepathInstall()
    {
        $input = $this->prompt($this->text('Installation subdirectory'), '');
        if (!$this->isValidInput($input, 'store.basepath', 'install')) {
            $this->outputErrors();
            $this->validateInputBasepathInstall();
        }
    }

    /**
     * Validates a database name input
     */
    protected function validateInputDbNameInstall()
    {
        $input = $this->prompt($this->text('Database name'), '');
        if (!$this->isValidInput($input, 'database.name', 'install')) {
            $this->outputErrors();
            $this->validateInputDbNameInstall();
        }
    }

    /**
     * Validates a database username input
     */
    protected function validateInputDbUserInstall()
    {
        $input = $this->prompt($this->text('Database user'), 'root');
        if (!$this->isValidInput($input, 'database.user', 'install')) {
            $this->outputErrors();
            $this->validateInputDbUserInstall();
        }
    }

    /**
     * Validates a database password input
     */
    protected function validateInputDbPasswordInstall()
    {
        $input = $this->prompt($this->text('Database password'), '');
        if (!$this->isValidInput($input, 'database.password', 'install')) {
            $this->outputErrors();
            $this->validateInputDbPasswordInstall();
        }
    }

    /**
     * Validates a database port input
     */
    protected function validateInputDbPortInstall()
    {
        $input = $this->prompt($this->text('Database port'), '3306');
        if (!$this->isValidInput($input, 'database.port', 'install')) {
            $this->outputErrors();
            $this->validateInputDbPortInstall();
        }
    }

    /**
     * Validates a database host input
     */
    protected function validateInputDbHostInstall()
    {
        $input = $this->prompt($this->text('Database host'), 'localhost');
        if (!$this->isValidInput($input, 'database.host', 'install')) {
            $this->outputErrors();
            $this->validateInputDbHostInstall();
        }
    }

    /**
     * Validates a database port input
     */
    protected function validateInputDbTypeInstall()
    {
        $drivers = \PDO::getAvailableDrivers();
        $input = $this->menu($drivers, 'mysql', $this->text('Database type (enter a number)'));
        if (!$this->isValidInput($input, 'database.type', 'install')) {
            $this->outputErrors();
            $this->validateInputDbTypeInstall();
        }
    }

}
