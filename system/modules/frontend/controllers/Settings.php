<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend\controllers;

use gplcart\core\controllers\backend\Controller;
use gplcart\core\models\ImageStyle;

/**
 * Handles incoming requests and outputs data related to Frontend module settings
 */
class Settings extends Controller
{

    /**
     * Image style model instance
     * @var \gplcart\core\models\ImageStyle $image_style
     */
    protected $image_style;

    /**
     * Settings constructor.
     * @param ImageStyle $image_style
     */
    public function __construct(ImageStyle $image_style)
    {
        parent::__construct();

        $this->image_style = $image_style;
    }

    /**
     * Displays the module settings page
     */
    public function editSettings()
    {
        $this->setTitleEditSettings();
        $this->setBreadcrumbEditSettings();

        $this->setData('imagestyles', $this->image_style->getList());
        $this->setData('settings', $this->module->getSettings('frontend'));
        $this->setData('imagestyle_fields', $this->getImageStyleFieldsSettings());

        $this->submitSettings();
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
        $this->redirect('', $this->text('Settings have been reset to default values'), 'success');
    }

    /**
     * Updates module settings with an array of submitted values
     */
    protected function updateSettings()
    {
        $this->controlAccess('module_edit');
        $this->module->setSettings('frontend', array_filter($this->getSubmitted()));
        $this->redirect('admin/module/list', $this->text('Settings have been updated'), 'success');
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
        $this->setTitle($this->text('Edit %name settings', array('%name' => $this->text('Frontend'))));
    }

}
