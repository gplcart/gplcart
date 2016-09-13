<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\classes\Tool;
use core\models\Install as ModelsInstall;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to installation process
 */
class Install extends FrontendController
{

    /**
     * Install model instance
     * @var \core\models\Install $install
     */
    protected $install;

    /**
     * Language selected upon installation
     * @var string
     */
    protected $install_language = '';

    /**
     * Constructor
     * @param ModelsInstall $install
     */
    public function __construct(ModelsInstall $install)
    {
        parent::__construct();

        $this->install = $install;
        $this->install_language = $this->request->get('lang', '');
    }

    /**
     * Dispays install page
     */
    public function install()
    {
        $this->controlAccessInstall();

        $this->submitInstall();

        $timezones = Tool::timezones();
        $languages = $this->getLanguagesInstall();
        $requirements = $this->getRequirementsInstall();

        $issues = $this->getRequirementErrorsInstall($requirements);
        $severity = $this->getSeverityInstall($issues);

        $this->setData('issues', $issues);
        $this->setData('severity', $severity);
        $this->setData('url_wiki', GC_WIKI_URL);
        $this->setData('timezones', $timezones);
        $this->setData('languages', $languages);
        $this->setData('requirements', $requirements);
        $this->setData('url_licence', $this->url('license.txt'));
        $this->setData('settings.store.timezone', 'Europe/London');
        $this->setData('settings.store.language', $this->install_language);

        $this->setCssInstall();
        $this->setTitleInstall();
        $this->outputInstall();
    }

    /**
     * Returns an array of ISO languages
     * @return type
     */
    protected function getLanguagesInstall()
    {
        $iso = $this->language->getIso();
        $available = $this->language->getAvailable();

        return array_intersect_key($iso, $available);
    }

    /**
     * Controls access to installer
     */
    protected function controlAccessInstall()
    {
        if ($this->config->exists() && !$this->session->get('install', 'processing')) {
            $this->redirect('/');
        }
    }

    /**
     * Returns an array of system requirements
     * @return array
     */
    protected function getRequirementsInstall()
    {
        return $this->install->getRequirements();
    }

    /**
     * Returns an array of requirement errors
     * @param array $requirements
     * @return array
     */
    protected function getRequirementErrorsInstall(array $requirements)
    {
        return $this->install->getRequirementErrors($requirements);
    }

    /**
     * Returns a string with the current severity
     * @param array $issues
     * @return string
     */
    protected function getSeverityInstall(array $issues)
    {
        if (isset($issues['warning'])) {
            return 'warning';
        }

        if (isset($issues['danger'])) {
            return 'danger';
        }

        return '';
    }

    /**
     * Starts installing the system
     */
    protected function submitInstall()
    {
        if (!$this->isPosted('install')) {
            return;
        }

        $this->setSubmitted('settings');

        $this->validateInstall();

        if (!$this->hasErrors('settings')) {
            $this->processInstall();
        }
    }

    /**
     * Performs all needed operations to install the system
     */
    protected function processInstall()
    {
        $this->processStartInstall();
        $this->processTablesInstall();
        $this->processConfigInstall();
        $this->processStoreInstall();
        $this->processFinishInstall();
    }

    /**
     * Prepares installation
     */
    protected function processStartInstall()
    {
        ini_set('max_execution_time', 0);

        $submitted = $this->getSubmitted();

        $this->session->delete('install');
        $this->session->set('install', 'processing', true);
        $this->session->set('install', 'settings', $submitted);
    }

    /**
     * Imports tables from database config file
     */
    protected function processTablesInstall()
    {
        $result = $this->install->tables();

        if ($result !== true) {
            $url = $this->url('', $this->query);
            $this->redirect($url, $this->text('Failed to create all necessary tables in the database'), 'danger');
        }
    }

    /**
     * Creates main config file
     */
    protected function processConfigInstall()
    {
        $submitted = $this->getSubmitted();

        if (!$this->install->config($submitted)) {
            $url = $this->url('', $this->query);
            $this->redirect($url, $this->text('Failed to create config.php'), 'danger');
        }
    }

    /**
     * Sets up the store
     */
    protected function processStoreInstall()
    {
        $submitted = $this->getSubmitted();
        $result = $this->install->store($submitted);

        if ($result !== true) {
            $url = $this->url('', $this->query);
            $this->redirect($url, (string) $result, 'danger');
        }
    }

    /**
     * Finishes the installation process
     */
    protected function processFinishInstall()
    {
        $this->session->delete();
        Tool::deleteCookie();

        $message = $this->text('Congratulations! You have successfully installed your store');
        $this->redirect('admin', $message, 'success');
    }

    /**
     * Sets titles on the installation page
     */
    protected function setTitleInstall()
    {
        $this->setTitle($this->text('Installing GPL Cart'));
    }

    /**
     * Renders installation page
     */
    protected function outputInstall()
    {
        $variables = array(
            'layout' => 'install/layout',
            'region_body' => 'install/body',
            'region_head' => 'install/head'
        );

        $this->output($variables);
    }

    /**
     * Adds CSS on the installation page
     */
    protected function setCssInstall()
    {
        $this->setCss('system/modules/frontend/css/install.css', 99);
    }

    /**
     * Validates an array of submitted form values
     */
    protected function validateInstall()
    {
        $language = array(
            $this->install_language => $this->language->getIso($this->install_language)
        );

        $this->setSubmitted('store.language', $language);

        $this->addValidator('database.host', array(
            'required' => array()
        ));

        $this->addValidator('database.name', array(
            'required' => array()
        ));

        $this->addValidator('database.user', array(
            'required' => array()
        ));

        $this->addValidator('database.port', array(
            'numeric' => array('required' => true)
        ));

        $this->addValidator('user.password', array(
            'length' => $this->user->getPasswordLength()
        ));

        $this->addValidator('user.email', array(
            'required' => array(),
            'email' => array()
        ));

        $this->addValidator('store.title', array(
            'length' => array('min' => 1, 'max' => 255)
        ));

        $errors = $this->setValidators();

        if (!empty($errors)) {
            return;
        }

        $db = $this->getSubmitted('database');
        $connect = $this->install->connect($db);

        if ($connect !== true) {
            $this->setError('database.connect', $connect);
        }
    }

}
