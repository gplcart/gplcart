<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Install as InstallModel;
use gplcart\core\Controller;

/**
 * Handles incoming requests and outputs data related to installation process
 */
class Install extends Controller
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
     * @param InstallModel $install
     */
    public function __construct(InstallModel $install)
    {
        parent::__construct();

        $this->install = $install;
        $this->install_language = $this->getQuery('lang', '');
    }

    /**
     * Displays the installation page
     */
    public function editInstall()
    {
        $this->controlAccessInstall();
        $this->translation->set($this->install_language, null);
        $this->setTitleEditInstall();

        $requirements = $this->getRequirementsInstall();
        $issues = $this->getRequirementErrorsInstall($requirements);

        $this->setData('issues', $issues);
        $this->setData('requirements', $requirements);
        $this->setData('settings.installer', 'default');
        $this->setData('timezones', gplcart_timezones());
        $this->setData('language', $this->install_language);
        $this->setData('handlers', $this->install->getHandlers());
        $this->setData('languages', $this->getLanguagesInstall());
        $this->setData('severity', $this->getSeverityInstall($issues));
        $this->setData('settings.store.language', $this->install_language);

        $this->submitEditInstall();
        $this->outputEditInstall();
    }

    /**
     * Returns an array of existing languages
     * @return array
     */
    protected function getLanguagesInstall()
    {
        $languages = array();

        foreach ($this->language->getList() as $code => $language) {
            if ($code === 'en' || is_file($this->translation->getFile($code))) {
                $languages[$code] = $language;
            }
        }

        return $languages;
    }

    /**
     * Controls access to the installer
     */
    protected function controlAccessInstall()
    {
        if ($this->config->isInitialized()) {
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
    protected function submitEditInstall()
    {
        if ($this->isPosted('install') && $this->validateEditInstall()) {
            $this->processInstall();
        }
    }

    /**
     * Performs all needed operations to install the system
     */
    protected function processInstall()
    {
        $result = $this->install->process($this->getSubmitted());
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

    /**
     * Sets titles on the installation page
     */
    protected function setTitleEditInstall()
    {
        $this->setTitle($this->text('Installing GPLCart'));
    }

    /**
     * Renders installation page
     */
    protected function outputEditInstall()
    {
        $this->output(array('body' => 'install/body'));
    }

    /**
     * Validates an array of submitted form values
     * @return bool
     */
    protected function validateEditInstall()
    {
        $this->setSubmitted('settings');
        $this->setSubmitted('store.host', $this->server->httpHost());
        $this->setSubmitted('store.language', $this->install_language);
        $this->setSubmitted('store.basepath', trim($this->request->base(true), '/'));

        $this->validateComponent('install');

        return !$this->hasErrors(false);
    }

}
