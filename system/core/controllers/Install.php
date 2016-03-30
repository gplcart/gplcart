<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Controller;
use core\models\Country;
use core\models\Install as I;
use core\classes\Tool;

class Install extends Controller
{

    /**
     * Instal model instance
     * @var \core\models\Install $install
     */
    protected $install;

    /**
     * Country model instance
     * @var \core\models\Country $country 
     */
    protected $country;

    /**
     * Constructor
     * @param I $install
     * @param Country $country
     */
    public function __construct(I $install, Country $country)
    {
        parent::__construct();

        $this->install = $install;
        $this->country = $country;
    }

    /**
     * Dispays install page
     */
    public function install()
    {
        if ($this->config->exists() && !$this->session->get('install', 'processing')) {
            $this->redirect('/');
        }

        $language = $this->session->get('language', null, '');
        $selected_language = $this->request->post('language');

        // Change language
        if ($selected_language) {
            $language = $selected_language;
            $this->session->set('language', null, $language);
            $this->redirect();
        }

        // Install
        if ($this->request->post('install')) {

            ini_set('max_execution_time', 0);

            $submitted = $this->request->post('settings', array());

            $this->validate($submitted);

            if ($this->formErrors()) {
                $this->data['settings'] = $submitted;
            } else {

                $this->session->delete('install');
                $this->session->set('install', 'processing', true);
                $this->session->set('install', 'settings', $submitted);

                if (!$this->install->tables()) {
                    $this->redirect('', $this->text('Failed to create all necessary tables in the database'), 'danger');
                }

                if (!$this->install->config($submitted)) {
                    $this->redirect('', $this->text('Failed to create config.php'), 'danger');
                }

                $result = $this->install->store($submitted);

                if ($result !== true) {
                    $this->redirect('', $result, 'danger');
                }

                $this->session->delete();
                Tool::deleteCookie();
                $message = $this->text('Congratulations! You have successfully installed your store');
                $this->redirect('/', $message, 'success');
            }
        }

        $this->data['countries'] = $this->country->countries(true);
        $this->data['requirements'] = $this->install->getRequirements();
        $this->data['issues'] = $this->install->getRequirementsErrors($this->data['requirements']);
        $this->data['timezones'] = Tool::timezones();
        $this->data['url_wiki'] = GC_WIKI;
        $this->data['url_licence'] = (file_exists(GC_ROOT_DIR . '/LICENSE')) ? $this->url('LICENSE') : 'http://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html';

        $this->data['settings']['site']['timezone'] = 'Europe/London';
        $this->data['settings']['site']['language'] = $language;

        $this->data['issue_severity'] = '';
        if (isset($this->data['issues']['warning'])) {
            $this->data['issue_severity'] = 'warning';
        }

        if (isset($this->data['issues']['danger'])) {
            $this->data['issue_severity'] = 'danger';
        }

        $this->data['languages'] = $this->language->getAvailable();

        $this->document->css('files/assets/bootstrap/bootstrap/css/bootstrap.min.css');
        $this->document->css('files/assets/font-awesome/css/font-awesome.min.css');
        $this->document->css('system/modules/frontend/css/install.css');

        $this->document->js('files/assets/jquery/jquery-1.11.3.js', 'top');
        $this->document->js('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');
        $this->document->js('system/modules/frontend/js/script.js', 'top');

        $this->document->meta(array('charset' => 'utf-8'));
        $this->document->meta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $this->document->meta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $this->document->meta(array('name' => 'author', 'content' => 'GPL Cart'));

        $this->setTitle($this->text('Installing GPL Cart'));

        $this->output(array(
            'layout' => 'install/layout',
            'region_content' => 'install/content',
            'region_head' => 'install/head',
            'region_bottom' => 'install/bottom'
        ));
    }

    /**
     * Validates an array of submitted form values
     * @param array $submitted
     * @return null
     */
    protected function validate(&$submitted)
    {
        if (!$submitted['database']['host']) {
            $this->data['form_errors']['database']['host'] = $this->text('Required field');
        }

        if (!$submitted['database']['name']) {
            $this->data['form_errors']['database']['name'] = $this->text('Required field');
        }

        if (!$submitted['database']['user']) {
            $this->data['form_errors']['database']['user'] = $this->text('Required field');
        }

        if (!$submitted['database']['port']) {
            $this->data['form_errors']['database']['port'] = $this->text('Required field');
        }

        $min_password_length = 8;
        $max_password_length = 255;
        $password_length = mb_strlen($submitted['user']['password']);
        $password_length_ok = (($min_password_length <= $password_length) && ($password_length <= $max_password_length));

        if (!$password_length_ok) {
            $this->data['form_errors']['user']['password'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => $min_password_length, '%max' => $max_password_length));
        }

        if (!filter_var($submitted['user']['email'], FILTER_VALIDATE_EMAIL)) {
            $this->data['form_errors']['user']['email'] = $this->text('Invalid E-mail');
        }

        if (mb_strlen($submitted['store']['title']) > 255) {
            $this->data['form_errors']['store']['title'] = $this->text('Content must not exceed %s characters', array('%s' => 255));
        }

        if (empty($submitted['store']['country'])) {
            $this->data['form_errors']['store']['country'] = $this->text('Required field');
        }

        if (isset($this->data['form_errors'])) {
            return;
        }

        $connect = $this->install->connect($submitted['database']);

        if ($connect !== true) {
            $this->data['form_errors']['database']['connect'] = $this->text($connect);
            return;
        }

        $countries = $this->country->countries();

        $code = $submitted['store']['country'];
        $submitted['store']['country_name'] = $countries[$code]['name'];
        $submitted['store']['country_native_name'] = $countries[$code]['native_name'];
    }

}
