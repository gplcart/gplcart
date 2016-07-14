<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;
use core\classes\Tool;
use core\models\Country as ModelsCountry;
use core\models\Install as ModelsInstall;

/**
 * Handles incoming requests and outputs data related to installation process
 */
class Install extends Controller
{

    /**
     * Install model instance
     * @var \core\models\Install $install
     */
    protected $install;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Language selected upon installation
     * @var string
     */
    protected $install_language;

    /**
     * Constructor
     * @param ModelsInstall $install
     * @param ModelsCountry $country
     */
    public function __construct(ModelsInstall $install, ModelsCountry $country)
    {
        parent::__construct();

        $this->install = $install;
        $this->country = $country;
        $this->install_language = $this->session->get('language', null, '');
    }

    /**
     * Dispays install page
     */
    public function install()
    {

        $this->controlInstallMode();
        $this->setInstallLanguage();

        // Install
        if ($this->request->post('install')) {
            $this->submitInstall();
        }

        $this->data['countries'] = $this->country->countries(true);
        $this->data['requirements'] = $this->install->getRequirements();
        $this->data['timezones'] = Tool::timezones();
        $this->data['url_wiki'] = GC_WIKI_URL;
        $this->data['url_licence'] = (file_exists(GC_ROOT_DIR . '/LICENSE')) ? $this->url('LICENSE') : 'https://www.gnu.org/licenses/gpl.html';
        $this->data['settings']['site']['timezone'] = 'Europe/London';
        $this->data['languages'] = $this->language->getAvailable();

        $this->setIssues();
        $this->addCssInstall();
        $this->addJsInstall();
        $this->setTitleInstall();
        $this->outputInstall();
    }

    protected function submitInstall()
    {

        ini_set('max_execution_time', 0);

        $this->submitted = $this->request->post('settings', array());

        $this->validateInstall();

        $errors = $this->formErrors();

        if (!empty($errors)) {
            $this->data['settings'] = $this->submitted;
            return;
        }

        $this->session->delete('install');
        $this->session->set('install', 'processing', true);
        $this->session->set('install', 'settings', $this->submitted);

        if (!$this->install->tables()) {
            $this->redirect('', $this->text('Failed to create all necessary tables in the database'), 'danger');
        }

        if (!$this->install->config($this->submitted)) {
            $this->redirect('', $this->text('Failed to create config.php'), 'danger');
        }

        $result = $this->install->store($this->submitted);

        if ($result !== true) {
            $this->redirect('', $result, 'danger');
        }

        $this->session->delete();
        Tool::deleteCookie();
        $message = $this->text('Congratulations! You have successfully installed your store');
        $this->redirect('admin', $message, 'success');
    }

    /**
     * Sets a language upon installation
     */
    protected function setInstallLanguage()
    {
        $selected = $this->request->post('language');

        // Change language
        if (!empty($selected)) {
            $this->install_language = $selected;
            $this->session->set('language', null, $this->install_language);
            $this->redirect();
        }

        $this->data['settings']['site']['language'] = $this->install_language;
    }

    /**
     * Sets issues data (if any)
     */
    protected function setIssues()
    {

        $this->data['issues'] = $this->install->getRequirementsErrors($this->data['requirements']);

        $this->data['issue_severity'] = '';
        if (isset($this->data['issues']['warning'])) {
            $this->data['issue_severity'] = 'warning';
        }

        if (isset($this->data['issues']['danger'])) {
            $this->data['issue_severity'] = 'danger';
        }
    }

    /**
     * Ensures that installation process is really needed
     */
    protected function controlInstallMode()
    {
        if ($this->config->exists() && !$this->session->get('install', 'processing')) {
            $this->redirect('/');
        }
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
            'region_head' => 'install/head',
            'region_bottom' => 'install/bottom'
        );

        $this->output($variables);
    }

    /**
     * Adds CSS on the installation page
     */
    protected function addCssInstall()
    {

        $this->setCss('files/assets/bootstrap/bootstrap/css/bootstrap.min.css');
        $this->setCss('files/assets/font-awesome/css/font-awesome.min.css');
        $this->setCss('system/modules/frontend/css/install.css');
    }

    /**
     * Adds Js on the installation page
     */
    protected function addJsInstall()
    {
        $this->setJs('system/modules/frontend/js/script.js', 'top');
        $this->setJs('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');
    }

    /**
     * Validates an array of submitted form values
     * @return null
     */
    protected function validateInstall()
    {

        $this->validateDbHost();
        $this->validateDbName();
        $this->validateDbUser();
        $this->validateDbPort();
        $this->validateUserPassword();
        $this->validateUserEmail();
        $this->validateStoreTitle();
        $this->validateStoreCountry();

        $errors = $this->formErrors();

        if (!empty($errors)) {
            return false;
        }

        if ($this->validateDbConnect() === true) {
            return true;
        }

        return false;
    }

    /**
     * Validates database connection
     * @return boolean
     */
    protected function validateDbConnect()
    {
        $connect = $this->install->connect($this->submitted['database']);

        if ($connect === true) {
            return true;
        }

        $this->data['form_errors']['database']['connect'] = $this->text($connect);
        return false;
    }

    /**
     * Validates store country
     * @return boolean
     */
    protected function validateStoreCountry()
    {
        if (empty($this->submitted['store']['country'])) {
            $this->data['form_errors']['store']['country'] = $this->text('Required field');
            return false;
        }

        $countries = $this->country->countries();

        $code = $this->submitted['store']['country'];
        $this->submitted['store']['country_name'] = $countries[$code]['name'];
        $this->submitted['store']['country_native_name'] = $countries[$code]['native_name'];

        return true;
    }

    /**
     * Validates store title
     * @return boolean
     */
    protected function validateStoreTitle()
    {
        if (mb_strlen($this->submitted['store']['title']) > 255) {
            $this->data['form_errors']['store']['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates user e-mail
     * @return boolean
     */
    protected function validateUserEmail()
    {
        if (filter_var($this->submitted['user']['email'], FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        $this->data['form_errors']['user']['email'] = $this->text('Invalid E-mail');
        return false;
    }

    /**
     * Validates user password
     * @return boolean
     */
    protected function validateUserPassword()
    {

        $min_password_length = 8;
        $max_password_length = 255;

        $password_length = mb_strlen($this->submitted['user']['password']);

        if (($min_password_length <= $password_length) && ($password_length <= $max_password_length)) {
            return true;
        }

        $this->data['form_errors']['user']['password'] = $this->text('Content must be %min - %max characters long', array(
            '%min' => $min_password_length, '%max' => $max_password_length));

        return false;
    }

    /**
     * Validates database host
     * @return boolean
     */
    protected function validateDbHost()
    {

        if (empty($this->submitted['database']['host'])) {
            $this->data['form_errors']['database']['host'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates database name
     * @return boolean
     */
    protected function validateDbName()
    {
        if (empty($this->submitted['database']['name'])) {
            $this->data['form_errors']['database']['name'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates database user
     * @return boolean
     */
    protected function validateDbUser()
    {
        if (empty($this->submitted['database']['user'])) {
            $this->data['form_errors']['database']['user'] = $this->text('Required field');
            return false;
        }

        return true;
    }

    /**
     * Validates database port
     * @return boolean
     */
    protected function validateDbPort()
    {
        if (empty($this->submitted['database']['port'])) {
            $this->data['form_errors']['database']['port'] = $this->text('Required field');
            return false;
        }

        return true;
    }

}
