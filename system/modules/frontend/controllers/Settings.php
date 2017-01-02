<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend\controllers;

use gplcart\core\models\Image as ImageModel;
use gplcart\core\models\Module as ModuleModel;
use gplcart\modules\frontend\Frontend as FrontendModule;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Frontend module settings
 */
class Settings extends BackendController
{

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Module model instance
     * @var \gplcart\core\models\Module $module
     */
    protected $module;

    /**
     * Module instance
     * @var \gplcart\modules\frontend\Frontend $frontend
     */
    protected $frontend;

    /**
     * Constructor
     * @param ImageModel $image
     * @param ModuleModel $module
     * @param FrontendModule $frontend
     */
    public function __construct(ImageModel $image, ModuleModel $module,
            FrontendModule $frontend)
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
        $imagestyles = $this->image->getStyleNames();
        $settings = $this->config->module('frontend');

        $this->setData('settings', $settings);
        $this->setData('imagestyles', $imagestyles);

        $this->submitSettings();

        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Saves the submitted settings
     * @return null
     */
    protected function submitSettings()
    {
        if ($this->isPosted('reset')) {
            return $this->resetSettings();
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('settings');
        $this->validateSettings();

        if (!$this->hasErrors('settings')) {
            $this->updateSettings();
        }

        return null;
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
        $this->setSubmittedBool('twig.debug');
        $this->setSubmittedBool('twig.auto_reload');
        $this->setSubmittedBool('twig.strict_variables');

        $limit = $this->getSubmitted('catalog_limit');

        if (!is_numeric($limit) || strlen($limit) > 2) {
            $this->setError('catalog_limit', $this->text('Catalog limit must be numeric and no longer than 2 digits'));
        }
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
        $vars = array('%module' => $this->text('Frontend'));
        $title = $this->text('Edit %module settings', $vars);
        $this->setTitle($title);
    }

}
