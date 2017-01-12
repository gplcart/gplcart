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
 * Handles incoming requests and outputs data related to languages
 */
class Language extends BackendController
{

    /**
     * The current updating language
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
     * Displays the language edit form
     * @param string|null $code
     */
    public function editLanguage($code = null)
    {
        $this->setLanguage($code);

        $this->setTitleEditLanguage();
        $this->setBreadcrumbEditLanguage();

        $this->setData('language', $this->data_language);
        $this->setData('can_delete', $this->canDeleteLanguage());
        $this->setData('default_language', $this->language->getDefault());

        $this->submitLanguage();
        $this->outputEditLanguage();
    }

    /**
     * Whether the language can be deleted
     */
    protected function canDeleteLanguage()
    {
        return (isset($this->data_language['code']) && $this->access('language_delete'));
    }

    /**
     * Returns a language
     * @param string $code
     * @return array
     */
    protected function setLanguage($code)
    {
        if (empty($code)) {
            return array();
        }

        $language = $this->language->get($code);

        if (empty($language)) {
            $this->outputHttpStatus(404);
        }

        $this->data_language = $language;
        return $language;
    }

    /**
     * Saves a submitted language
     * @return null
     */
    protected function submitLanguage()
    {
        if ($this->isPosted('delete')) {
            return $this->deleteLanguage();
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('language');
        $this->validateLanguage();

        if ($this->hasErrors('language')) {
            return null;
        }

        if (isset($this->data_language['code'])) {
            return $this->updateLanguage();
        }

        $this->addLanguage();
        return null;
    }

    /**
     * Deletes a language
     * @return null
     */
    protected function deleteLanguage()
    {
        $this->controlAccess('language_delete');

        $deleted = $this->language->delete($this->data_language['code']);

        if ($deleted) {
            $message = $this->text('Language has been deleted');
            $this->redirect('admin/settings/language', $message, 'success');
        }

        $message = $this->text('Unable to delete this language');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a language
     */
    protected function validateLanguage()
    {
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');
        $this->setSubmitted('update', $this->data_language);
        $this->validate('language');
    }

    /**
     * Updates a language
     */
    protected function updateLanguage()
    {
        $this->controlAccess('language_edit');

        $submitted = $this->getSubmitted();
        $this->language->update($this->data_language['code'], $submitted);

        $message = $this->text('Language has been updated');
        $this->redirect('admin/settings/language', $message, 'success');
    }

    /**
     * Adds a new language
     */
    protected function addLanguage()
    {
        $this->controlAccess('language_add');

        $values = $this->getSubmitted();
        $this->language->add($values);

        $message = $this->text('Language has been added');
        $this->redirect('admin/settings/language', $message, 'success');
    }

    /**
     * Sets titles on the edit language page
     */
    protected function setTitleEditLanguage()
    {
        $title = $this->text('Add language');

        if (isset($this->data_language['code'])) {
            $vars = array('%name' => $this->data_language['native_name']);
            $title = $this->text('Edit language %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit language page
     */
    protected function setBreadcrumbEditLanguage()
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
     * Renders the edit language page templates
     */
    protected function outputEditLanguage()
    {
        $this->output('settings/language/edit');
    }

    /**
     * Displays the language overview page
     */
    public function listLanguage()
    {
        $this->setTitleListLanguage();
        $this->setBreadcrumbListLanguage();

        $this->refreshLanguage();

        $this->setData('languages', $this->language->getList());
        $this->outputListLanguage();
    }

    /**
     * Removes cached translations for the given language
     * @return null|void
     */
    protected function refreshLanguage()
    {
        $code = (string) $this->request->get('refresh');

        if (empty($code)) {
            return null;
        }

        $this->controlAccess('language_edit');

        $this->language->refresh($code);

        $vars = array('@code' => $code);
        $message = $this->text('Cache for language @code has been deleted', $vars);
        $this->redirect('', $message, 'success');
    }

    /**
     * Sets titles on the language overview page
     */
    protected function setTitleListLanguage()
    {
        $this->setTitle($this->text('Languages'));
    }

    /**
     * Sets breadcrumbs on the language overview page
     */
    protected function setBreadcrumbListLanguage()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the language overview page templates
     */
    protected function outputListLanguage()
    {
        $this->output('settings/language/list');
    }

}
