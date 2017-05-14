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
     * Processes installation
     */
    public function storeInstall()
    {
        if ($this->config->exists()) {
            $this->outputErrors($this->text('System already installed'), true);
        }

        $this->validateInstall();
        $this->processInstall();
        $this->output();
    }

    /**
     * Does installation
     */
    protected function processInstall()
    {
        $result = $this->install->full($this->getSubmitted());

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
    protected function validateInstall()
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
        $host = GC_WIN ? gethostbyname(gethostname()) : gethostname();
        $this->setSubmitted('store.host', $host);
        $this->setSubmitted('store.timezone', date_default_timezone_get());

        $this->validateComponent('install');

        if ($this->isError('database')) {
            $this->outputErrors();
            $this->validateInputDbInstall();
        }

        $this->outputErrors(null, true);
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
