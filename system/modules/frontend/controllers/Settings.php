<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\frontend\controllers;

use core\models\Image as ModelsImage;
use core\models\Module as ModelsModule;
use modules\frontend\Frontend as ModulesFrontend;
use core\controllers\admin\Controller as BackendController;

class Settings extends BackendController
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Module instance
     * @var \modules\frontend\Frontend $frontend
     */
    protected $frontend;

    /**
     * Constructor
     * @param ModelsImage $image
     * @param ModelsModule $module
     * @param ModulesFrontend $frontend
     */
    public function __construct(ModelsImage $image, ModelsModule $module,
            ModulesFrontend $frontend)
    {
        parent::__construct();

        $this->image = $image;
        $this->module = $module;
        $this->frontend = $frontend;
    }

    /**
     * Displays the module settings page
     */
    public function editSettings()
    {
        $this->submitSettings();

        $imagestyles = $this->image->getStyleNames();
        $settings = $this->config->module('frontend');

        $this->setData('settings', $settings);
        $this->setData('imagestyles', $imagestyles);

        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('reset')) {
            return $this->resetSettings();
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('settings');
        $this->validateSettings();

        if (!$this->hasErrors('settings')) {
            $this->updateSettings();
        }
    }

    /**
     * Resets module settings to default values
     */
    protected function resetSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('frontend', array());

        $message = $this->text('Settings have been reset to default values');
        $this->redirect('', $message, 'success');
    }

    /**
     * Updates module settings with an array of submitted values
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');

        $settings = $this->getSubmitted();
        $this->module->setSettings('frontend', $settings);

        $message = $this->text('Settings have been updated');
        $this->redirect('admin/module/list', $message, 'success');
    }

    /**
     * Validates an array of submitted settings
     */
    protected function validateSettings()
    {
        $this->addValidator('catalog_limit', array(
            'numeric' => array(),
            'length' => array('min' => 1, 'max' => 2)
        ));

        $this->setValidators();
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
        $this->output('frontend|settings');
    }

    /**
     * Sets titles on the module settings page
     */
    protected function setTitleEditSettings()
    {
        $title = $this->text('Edit %module settings', array(
            '%module' => $this->text('Frontend')));

        $this->setTitle($title);
    }

}
