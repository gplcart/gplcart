<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend\controllers;

use gplcart\core\models\Module as ModuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to Frontend module settings
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
     * Displays the module settings page
     */
    public function editSettings()
    {
        $this->setData('imagestyles', $this->image->getStyleList());
        $this->setData('settings', $this->config->getFromModule('frontend'));
        $this->setData('imagestyle_fields', $this->getImageStyleFieldsSettings());

        $this->submitSettings();

        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();
        $this->outputEditSettings();
    }

    /**
     * Returns an array of image style settings keys and their corresponding field labels
     * @return array
     */
    protected function getImageStyleFieldsSettings()
    {
        return array(
            'image_style_category' => $this->text('Category page'),
            'image_style_category_child' => $this->text('Category page (child)'),
            'image_style_product' => $this->text('Product page'),
            'image_style_page' => $this->text('Page'),
            'image_style_product_grid' => $this->text('Product catalog (grid)'),
            'image_style_product_list' => $this->text('Product catalog (list)'),
            'image_style_cart' => $this->text('Cart'),
            'image_style_option' => $this->text('Product option'),
            'image_style_collection_file' => $this->text('File collection (banners)'),
            'image_style_collection_page' => $this->text('Page collection (news/articles)'),
            'image_style_collection_product' => $this->text('Product collection (featured products)'),
        );
    }

    /**
     * Saves the submitted settings
     */
    protected function submitSettings()
    {
        if ($this->isPosted('reset')) {
            $this->resetSettings();
        } else if ($this->isPosted('save') && $this->validateSettings()) {
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
        $this->module->setSettings('frontend', array_filter($this->getSubmitted()));

        $message = $this->text('Settings have been updated');
        $this->redirect('admin/module/list', $message, 'success');
    }

    /**
     * Validates an array of submitted settings
     * @return bool
     */
    protected function validateSettings()
    {
        $this->setSubmitted('settings');
        $this->validateElement('catalog_limit', 'regexp', '/^[\d]{1,2}$/');

        return !$this->hasErrors();
    }

    /**
     * Sets bread crumbs on the module settings page
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
        $vars = array('%name' => $this->text('Frontend'));
        $this->setTitle($this->text('Edit %name settings', $vars));
    }

}
