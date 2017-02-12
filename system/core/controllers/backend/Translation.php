<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to translations
 */
class Translation extends BackendController
{

    /**
     * The current language
     * @var array
     */
    protected $data_language = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the translation upload form
     * @param string $code
     */
    public function uploadTranslation($code)
    {
        $this->setLanguageTranslation($code);

        $this->setTitleUploadTranslation();
        $this->setBreadcrumbUploadTranslation();

        $this->submitTranslation();
        $this->outputUploadTranslation();
    }

    /**
     * Sets the current language
     * @param string $code
     * @return array
     */
    protected function setLanguageTranslation($code)
    {
        if (empty($code)) {
            return array();
        }

        $language = $this->language->get($code);

        if (empty($language)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_language = $language;
    }

    /**
     * Handles a submitted translation
     */
    protected function submitTranslation()
    {
        if ($this->isPosted('save') && $this->validateTranslation()) {
            $this->addTranslation();
        }
    }

    /**
     * Validates a translation
     * @return bool
     */
    protected function validateTranslation()
    {
        $this->setSubmitted('language', $this->data_language['code']);
        $this->validate('translation_upload');
        return !$this->hasErrors();
    }

    /**
     * Adds a translation
     */
    protected function addTranslation()
    {
        $this->controlAccess('translation_add');

        $uploaded = $this->getSubmitted('destination');

        // Replace with existing common.csv
        $result = rename($uploaded, GC_LOCALE_DIR . "/{$this->data_language['code']}/common.csv");

        if ($result) {
            // Delete all cached translations
            $this->language->refresh($this->data_language['code']);
            $message = $this->text('Translation has been uploaded');
            $this->redirect('admin/settings/language', $message, 'success');
        }
    }

    /**
     * Sets titles on the upload translation page
     */
    protected function setTitleUploadTranslation()
    {
        $vars = array('%name' => $this->data_language['native_name']);
        $title = $this->text('Upload translation for language %name', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the upload translation page
     */
    protected function setBreadcrumbUploadTranslation()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/language'),
            'text' => $this->text('Languages')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the upload translation page templates
     */
    protected function outputUploadTranslation()
    {
        $this->output('settings/translation/upload');
    }

}
