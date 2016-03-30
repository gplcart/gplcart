<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;
use core\models\File;

class Settings extends Controller
{

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param File $file
     */
    public function __construct(File $file)
    {
        parent::__construct();
        $this->file = $file;
    }

    /**
     * Displays edit settings form
     */
    public function settings()
    {
        if (!$this->isSuperadmin()) {
            $this->outputError(403);
        }

        $this->setSettings();

        if ($this->request->post('save')) {
            $this->submit();
        }

        $this->prepareSettings();

        $this->setTitleSettings();
        $this->setBreadcrumbSettings();
        $this->outputSettings();
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
     * Sets settings values to be send to the template
     */
    protected function setSettings()
    {
        foreach ($this->getDefaultSettings() as $key => $default) {
            $this->data['settings'][$key] = $this->config->get($key, $default);
        }

        $this->data['gapi_certificate'] = '';
        if (!empty($this->data['settings']['gapi_certificate'])) {
            $this->data['gapi_certificate'] = $this->text('Currently using !file', array(
                '!file' => 'file/' . $this->data['settings']['gapi_certificate']));
        }
    }

    /**
     * Prepares settings values before passing them to the template
     */
    protected function prepareSettings()
    {
        if (isset($this->data['settings']['smtp_host'])) {
            $this->data['settings']['smtp_host'] = implode("\n", (array) $this->data['settings']['smtp_host']);
        }
    }

    /**
     * Saves settings
     */
    protected function submit()
    {
        $this->submitted = $this->request->post('settings');

        $this->validate();

        if ($this->formErrors()) {
            $this->data['settings'] = $this->submitted;
            return;
        }

        foreach ($this->submitted as $key => $value) {
            $this->config->set($key, $value);
        }

        $this->redirect(false, $this->text('Settings have been updated'), 'success');
    }

    /**
     * Validates settings
     */
    protected function validate()
    {
        if (empty($this->submitted['cron_key'])) {
            $this->submitted['cron_key'] = Tool::randomString();
        }

        $file = $this->request->file('gapi_certificate');

        if ($file) {
            $this->file->setHandler('p12');
            if ($this->file->upload($file) === true) {
                $destination = $this->file->getUploadedFile();
                $this->submitted['gapi_certificate'] = $this->file->path($destination);
            } else {
                $this->data['form_errors']['gapi_certificate'] = $this->text('Unable to upload the file');
            }
        }

        if (isset($this->submitted['gapi_email']) && !filter_var($this->submitted['gapi_email'], FILTER_VALIDATE_EMAIL)) {
            $this->data['form_errors']['gapi_email'] = $this->text('Invalid E-mail');
        }

        $this->submitted['smtp_host'] = Tool::stringToArray($this->submitted['smtp_host']);
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
    }

    /**
     * Renders settings page
     */
    protected function outputSettings()
    {
        $this->output('settings/settings');
    }

}
