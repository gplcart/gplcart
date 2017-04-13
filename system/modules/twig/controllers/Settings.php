<?php

/**
 * @package Twig
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\twig\controllers;

use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Twig module
 */
class Settings extends BackendController
{

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * @param ModuleModel $module
     */
    public function __construct(ModuleModel $module)
    {
        parent::__construct();

        $this->module = $module;
    }

    /**
     * Route page callback to display the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('settings', $this->config->module('twig'));

        $this->submitSettings();
        $this->outputEditSettings();
    }

    /**
     * Set title on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $vars = array('%name' => $this->text('Twig'));
        $title = $this->text('Edit %name settings', $vars);
        $this->setTitle($title);
    }

    /**
     * Set breadcrumbs on the module settings page
     */
    protected function setBreadcrumbEditSettings()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Modules'),
            'url' => $this->url('admin/module/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save') && $this->validateSettings()) {
            $this->updateSettings();
        }
    }

    /**
     * Validate submitted module settings
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');

        $this->setSubmittedBool('cache');
        $this->setSubmittedBool('debug');
        $this->setSubmittedBool('auto_reload');
        $this->setSubmittedBool('strict_variables');

        return !$this->hasErrors();
    }

    /**
     * Update module settings
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('twig', $this->getSubmitted());
        $this->redirect('', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Render and output the module settings page
     */
    protected function outputEditSettings()
    {
        $this->output('twig|settings');
    }

}
