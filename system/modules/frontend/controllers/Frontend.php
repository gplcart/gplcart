<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\frontend\controllers;

use core\Controller;
use core\models\Image;
use core\models\Module;
use modules\frontend\Frontend as F;

class Frontend extends Controller
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
     * @param Image $image
     * @param Module $module
     * @param Frontend $frontend
     */
    public function __construct(Image $image, Module $module, F $frontend)
    {
        parent::__construct();

        $this->image = $image;
        $this->module = $module;
        $this->frontend = $frontend;
    }

    /**
     * Displays the module settings page
     */
    public function settings()
    {
        $this->data['imagestyles'] = $this->image->getStyleNames();
        $this->data['settings'] = $this->config->module('frontend');

        if ($this->request->post('save')) {
            $this->submit();
        }

        $this->setTitleSettings();
        $this->setBreadcrumbSettings();
        $this->outputSettings();
    }

    /**
     * Saves the submitted settings
     * @return null
     */
    protected function submit()
    {
        $this->submitted = $this->request->post('settings');

        $this->validate();

        if ($this->getErrors()) {
            $this->data['settings'] = $this->submitted;
            return;
        }

        $this->controlAccess('module_edit');
        $this->module->setSettings('frontend', $this->submitted);
        $this->redirect('admin/module/list', $this->text('Settings have been updated'), 'success');
    }

    /**
     * Validates an array of submitted settings
     */
    protected function validate()
    {
        $this->validateNumeric();
    }

    /**
     * Validates numeric fields
     * @return boolean
     */
    protected function validateNumeric()
    {
        $has_errors = false;
        foreach (array('catalog_limit') as $name) {
            if (empty($this->submitted[$name])) {
                $this->submitted[$name] = 0;
                continue;
            }

            if (!is_numeric($this->submitted[$name]) || strlen($this->submitted[$name]) > 2) {
                $this->errors[$name] = $this->text('Only numeric value and no more than %s digits', array('%s' => 2));
                $has_errors = true;
            }
        }

        return !$has_errors;
    }

    /**
     * Sets breadcrumbs on the module settings page
     */
    protected function setBreadcrumbSettings()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Modules'), 'url' => $this->url('admin/module/list')));
    }

    /**
     * Renders the module settings page templates
     */
    protected function outputSettings()
    {
        $this->output('frontend|admin/settings');
    }

    /**
     * Sets titles on the module settings page
     */
    protected function setTitleSettings()
    {
        $this->setTitle($this->text('Edit module settings'));
    }
}
