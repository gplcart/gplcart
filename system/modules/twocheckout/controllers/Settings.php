<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\twocheckout\controllers;

use core\models\Module as ModelsModule;
use modules\twocheckout\TwoCheckout as ModulesTwoCheckout;
use core\controllers\admin\Controller as BackendController;

class Settings extends BackendController
{

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Module instance
     * @var \modules\twocheckout\TwoCheckout $twocheckout
     */
    protected $twocheckout;
    
    /**
     * Constructor
     * @param ModelsModule $module
     * @param ModulesTwoCheckout $twocheckout
     */
    public function __construct(ModelsModule $module, ModulesTwoCheckout $twocheckout)
    {
        parent::__construct();

        $this->module = $module;
        $this->twocheckout = $twocheckout;
    }

    /**
     * Displays the module settings page
     */
    public function editSettings()
    {
        $this->submitSettings();

        $settings = $this->config->module('twocheckout');
        
        $this->setData('settings', $settings);

        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('save')) {
            $this->setSubmitted('settings');
            $this->updateSettings();
        }
    }

    /**
     * Updates module settings with an array of submitted values
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');

        $settings = $this->getSubmitted();
        $this->module->setSettings('twocheckout', $settings);

        $message = $this->text('Settings have been updated');
        $this->redirect('admin/module/list', $message, 'success');
    }

    /**
     * Sets breadcrumbs on the module settings page
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
     * Renders the module settings page templates
     */
    protected function outputEditSettings()
    {
        $this->output('twocheckout|settings');
    }

    /**
     * Sets titles on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $title = $this->text('Edit %module settings', array(
            '%module' => $this->text('2 checkout')));

        $this->setTitle($title);
    }

}
