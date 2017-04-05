<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Install as InstallModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to installation process
 */
class Install extends FrontendController
{

    /**
     * Install model instance
     * @var \gplcart\core\models\Install $install
     */
    protected $install;

    /**
     * Language selected during installation
     * @var string
     */
    protected $install_language = '';

    /**
     * Constructor
     * @param InstallModel $install
     */
    public function __construct(InstallModel $install)
    {
        parent::__construct();

        $this->install = $install;
        $this->install_language = $this->getQuery('lang', 'en');
    }

    /**
     * Dispays the installation page
     */
    public function install()
    {
        $this->controlAccessInstall();

        $this->setTitleInstall();
        $this->submitInstall();

        $requirements = $this->getRequirementsInstall();
        $issues = $this->getRequirementErrorsInstall($requirements);

        $this->setData('issues', $issues);
        $this->setData('requirements', $requirements);
        $this->setData('timezones', gplcart_timezones());
        $this->setData('language', $this->install_language);
        $this->setData('languages', $this->getLanguagesInstall());
        $this->setData('severity', $this->getSeverityInstall($issues));
        $this->setData('settings.store.language', $this->install_language);

        $this->outputInstall();
    }

    /**
     * Returns an array of ISO languages
     * @return array
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
        if ($this->config->exists() && !$this->session->get('install.processing')) {
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
     * Returns a string with the current highest issue severity
     * @param array $issues
     * @return string
     */
    protected function getSeverityInstall(array $issues)
    {
        if (isset($issues['danger'])) {
            return 'danger';
        }

        if (isset($issues['warning'])) {
            return 'warning';
        }

        return '';
    }

    /**
     * Starts installing the system
     */
    protected function submitInstall()
    {
        if ($this->isPosted('install') && $this->validateInstall()) {
            $this->processInstall();
        }
    }

    /**
     * Performs all needed operations to install the system
     */
    protected function processInstall()
    {
        $submitted = $this->getSubmitted();
        $this->processStartInstall($submitted);

        $result = $this->install->full($submitted);

        if ($result === true) {
            $this->processFinishInstall();
        }

        if (empty($result)) {
            $result = $this->text('An error occurred');
        }

        $url = $this->url('', $this->query);
        $this->redirect($url, (string) $result, 'danger');
    }

    /**
     * Prepares installation
     * @param array $submitted
     */
    protected function processStartInstall(array $submitted)
    {
        ini_set('max_execution_time', 0);

        $this->session->delete('user');
        $this->session->delete('install');

        $this->session->set('install.processing', true);
        $this->session->set('install.settings', $submitted);
    }

    /**
     * Finishes the installation process
     */
    protected function processFinishInstall()
    {
        $this->session->delete('install');
        $this->request->deleteCookie();

        $args = array('@url' => $this->url('/'));
        $message = $this->text('You <a href="@url">store</a> has been installed. Now you can log in as superadmin', $args);

        $this->redirect('login', $message, 'success');
    }

    /**
     * Sets titles on the installation page
     */
    protected function setTitleInstall()
    {
        $this->setTitle($this->text('Install GPL Cart'));
    }

    /**
     * Renders installation page
     */
    protected function outputInstall()
    {
        $this->output(array('region_body' => 'install/body'));
    }

    /**
     * Validates an array of submitted form values
     * @return bool
     */
    protected function validateInstall()
    {
        $language = array(
            $this->install_language => $this->language->getIso($this->install_language)
        );

        $this->setSubmitted('settings');
        $this->setSubmitted('store.language', $language);
        $this->setSubmitted('store.host', $this->request->host());
        $this->setSubmitted('store.basepath', trim($this->request->base(true), '/'));

        $this->validateComponent('install');

        return !$this->hasErrors();
    }

}
