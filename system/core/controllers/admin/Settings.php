<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;

class Settings extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays edit settings form
     */
    public function settings()
    {
        $this->controlAccessSuperAdmin();

        $default = $this->getDefaultSettings();

        foreach ($default as $key => $value) {
            $this->setData("settings.$key", $this->config($key, $value));
        }

        if ($this->isPosted('save')) {
            $this->submit();
        }

        $this->setDataSettings();

        $this->setTitleSettings();
        $this->setBreadcrumbSettings();
        $this->outputSettings();
    }

    /**
     * Sets titles on the settings form page
     */
    protected function setTitleSettings()
    {
        $this->setTitle($this->text('Settings'));
    }

    /**
     * Sets breadcrumbs on the settings form page
     */
    protected function setBreadcrumbSettings()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders settings page
     */
    protected function outputSettings()
    {
        $this->output('settings/settings');
    }

    /**
     * Returns an array of settings with their default values
     * @return array
     */
    protected function getDefaultSettings()
    {
        return array(
            'cron_key' => '',
            'error_level' => 2,
            'gapi_email' => '',
            'gapi_certificate' => '',
            'email_method' => 'mail',
            'smtp_auth' => 1,
            'smtp_secure' => 'tls',
            'smtp_host' => array('smtp.gmail.com'),
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_port' => 587
        );
    }

    /**
     * Prepares settings values before passing them to template
     */
    protected function setDataSettings()
    {
        $smtp_host = $this->getData('settings.smtp_host');

        if (isset($smtp_host)) {
            $this->setData('settings.smtp_host', implode("\n", (array) $smtp_host));
        }
    }

    /**
     * Saves settings
     */
    protected function submit()
    {
        $this->setSubmitted('settings');
        $this->validate();

        if ($this->hasErrors('settings')) {
            return;
        }

        if ($this->isPosted('delete_gapi_certificate')) {
            unlink(GC_FILE_DIR . '/' . $this->config('gapi_certificate'));
            $this->config->reset('gapi_certificate');
        }

        $submitted = $this->getSubmitted();

        foreach ($submitted as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Validates submitted settings
     */
    protected function validate()
    {
        $smtp_host = $this->getSubmitted('smtp_host');
        $this->setSubmitted('smtp_host', Tool::stringToArray($smtp_host));

        $cron_key = $this->getSubmitted('cron_key');
        if (empty($cron_key)) {
            $this->setSubmitted('cron_key', Tool::randomString());
        }

        $this->addValidator('gapi_email', array(
            'email' => array()));

        $this->addValidator('gapi_certificate', array(
            'upload' => array(
                'file' => $this->request->file('gapi_certificate')
        )));

        $errors = $this->setValidators();

        if (!empty($errors)) {
            return;
        }

        $uploaded = $this->getValidatorResult('gapi_certificate');

        if (!empty($uploaded)) {
            $this->setSubmitted('gapi_certificate', $uploaded);
        }
    }

}
