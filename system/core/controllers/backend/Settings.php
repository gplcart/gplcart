<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;

class Settings extends BackendController
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
    public function editSettings()
    {
        $this->controlAccessEditSettings();

        $settings = $this->getSettings();
        $this->setData('settings', $settings);

        $this->submitSettings();
        $this->setDataEditSettings();

        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Controls access to edit settings
     */
    protected function controlAccessEditSettings()
    {
        if (!$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
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
            'email_method' => 'mail',
            'smtp_auth' => 1,
            'smtp_secure' => 'tls',
            'smtp_host' => array('smtp.gmail.com'),
            'smtp_username' => '',
            'smtp_password' => '',
            'smtp_port' => 587,
            'gapi_browser_key' => ''
        );
    }

    /**
     * Returns an array of settings
     * @return array
     */
    protected function getSettings()
    {
        $default = $this->getDefaultSettings();
        $saved = $this->config();

        return gplcart_array_merge($default, $saved);
    }

    /**
     * Saves submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('delete_cached_assets')) {
            $this->deleteCachedAssets();
            return null;
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('settings');
        $this->validateSettings();

        if ($this->hasErrors('settings')) {
            return;
        }

        $this->updateSettings();
    }

    /**
     * Deletes all aggregated assets
     */
    protected function deleteCachedAssets()
    {
        $result = gplcart_file_delete_recursive(GC_COMPRESSED_ASSET_DIR);

        if ($result) {
            $this->redirect('', $this->text('Cache has been cleared'), 'success');
        }

        $this->redirect('');
    }

    /**
     * Validates submitted settings
     */
    protected function validateSettings()
    {
        $this->setSubmittedBool('smtp_auth');
        $this->validate('settings');
    }

    /**
     * Updates common setting with submitted values
     */
    protected function updateSettings()
    {
        $this->controlAccess('settings_edit');

        $submitted = $this->getSubmitted();

        foreach ($submitted as $key => $value) {
            $this->config->set($key, $value);
        }

        $message = $this->text('Settings have been updated');
        $this->redirect('', $message, 'success');
    }

    /**
     * Prepares settings values before passing them to template
     */
    protected function setDataEditSettings()
    {
        $smtp_host = $this->getData('settings.smtp_host');
        $this->setData('settings.smtp_host', implode("\n", (array) $smtp_host));
    }

    /**
     * Sets titles on the settings form page
     */
    protected function setTitleEditSettings()
    {
        $this->setTitle($this->text('Settings'));
    }

    /**
     * Sets breadcrumbs on the settings form page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders settings page
     */
    protected function outputEditSettings()
    {
        $this->output('settings/settings');
    }

}
