<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\cli;

use PDO;
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
     * @param InstallModel $install
     */
    public function __construct(InstallModel $install)
    {
        parent::__construct();

        $this->install = $install;
    }

    /**
     * Performs full system installation
     */
    public function install()
    {
        $this->controlAccessInstall();

        if ($this->getParam()) {
            $this->validateFastInstall();
        } else {
            $this->validateWizardInstall();
        }

        $this->processInstall();
        $this->output();
    }

    /**
     * Display simple installation wizard and validates user input
     */
    protected function validateWizardInstall()
    {
        $this->selectLanguage();
        $this->validateRequirementsInstall();
        $this->validateInstallerInstall();
        $this->validateInputTitleInstall();
        $this->validateInputEmailInstall();
        $this->validateInputPasswordInstall();
        $this->validateInputBasepathInstall();
        $this->validateInputDbInstall();
        $this->validateInputInstall();
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
        $this->errors(true);
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
            'db-pass' => 'database.password',
            'db-type' => 'database.type',
            'db-port' => 'database.port',
            'db-host' => 'database.host',
            'email' => 'user.email',
            'pass' => 'user.password',
            'host' => 'store.host',
            'title' => 'store.title',
            'basepath' => 'store.basepath',
            'timezone' => 'store.timezone',
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
     * Controls access to installation process
     */
    protected function controlAccessInstall()
    {
        if ($this->config->isInitialized()) {
            $this->errorExit($this->text('System already installed'));
        }
    }

    /**
     * Does installation
     */
    protected function processInstall()
    {
        if (!$this->isError()) {

            $settings = $this->getSubmitted();
            $this->install->connectDb($settings['database']);
            $result = $this->install->process($settings, $this->current_route);

            if ($result['severity'] === 'success') {
                $this->line($result['message']);
            } else {
                $this->error($result['message']);
            }
        }
    }

    /**
     * Validates all collected input
     */
    protected function validateInputInstall()
    {
        $this->setSubmitted('store.language', $this->langcode);
        $this->setSubmitted('store.host', $this->getHostInstall());
        $this->setSubmitted('store.timezone', date_default_timezone_get());

        $this->validateComponent('install');

        if ($this->isError('database')) {
            $this->errors();
            $this->validateInputDbInstall();
        }

        $this->errors(true);
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
     * Validates installation profile input
     */
    protected function validateInstallerInstall()
    {
        $handlers = $this->install->getHandlers();

        if (count($handlers) < 2) {
            return null;
        }

        $options = array();
        foreach ($handlers as $id => $handler) {
            $options[$id] = $handler['title'];
        }

        $input = $this->menu($options, 'default', $this->text('Installation profile (enter a number)'));

        if (!$this->isValidInput($input, 'installer', 'install')) {
            $this->errors();
            $this->validateInstallerInstall();
        }
    }

    /**
     * Validates system requirements
     */
    protected function validateRequirementsInstall()
    {
        $this->validateComponent('install', array('field' => 'requirements'));
        $this->errors(true);
    }

    /**
     * Validates store title
     */
    protected function validateInputTitleInstall()
    {
        $input = $this->prompt($this->text('Store title'), 'GPLCart');
        if (!$this->isValidInput($input, 'store.title', 'install')) {
            $this->errors();
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
            $this->errors();
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
            $this->errors();
            $this->validateInputPasswordInstall();
        }
    }

    /**
     * Validates server base path input
     */
    protected function validateInputBasepathInstall()
    {
        $input = $this->prompt($this->text('Installation subdirectory'), '');
        if (!$this->isValidInput($input, 'store.basepath', 'install')) {
            $this->errors();
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
            $this->errors();
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
            $this->errors();
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
            $this->errors();
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
            $this->errors();
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
            $this->errors();
            $this->validateInputDbHostInstall();
        }
    }

    /**
     * Validates a database port input
     */
    protected function validateInputDbTypeInstall()
    {
        $drivers = PDO::getAvailableDrivers();
        $title = $this->text('Database type (enter a number)');
        $input = $this->menu(array_combine($drivers, $drivers), 'mysql', $title);

        if (!$this->isValidInput($input, 'database.type', 'install')) {
            $this->errors();
            $this->validateInputDbTypeInstall();
        }
    }

}
