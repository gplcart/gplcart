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
     * An array of language data
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

        $this->submitEditLanguage();
        $this->outputEditLanguage();
    }

    /**
     * Whether the language can be deleted
     * @return bool
     */
    protected function canDeleteLanguage()
    {
        return isset($this->data_language['code'])//
                && $this->access('language_delete')//
                && $this->language->canDelete($this->data_language['code']);
    }

    /**
     * Set a language data
     * @param string $code
     */
    protected function setLanguage($code)
    {
        if (!empty($code)) {
            $this->data_language = $this->language->get($code);
            if (empty($this->data_language)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted language data
     */
    protected function submitEditLanguage()
    {
        if ($this->isPosted('delete')) {
            $this->deleteLanguage();
        } else if ($this->isPosted('save') && $this->validateLanguage()) {
            if (isset($this->data_language['code'])) {
                $this->updateLanguage();
            } else {
                $this->addLanguage();
            }
        }
    }

    /**
     * Deletes a language
     */
    protected function deleteLanguage()
    {
        $this->controlAccess('language_delete');
        if ($this->language->delete($this->data_language['code'])) {
            $this->redirect('admin/settings/language', $this->text('Language has been deleted'), 'success');
        }
        $this->redirect('', $this->text('Unable to delete'), 'danger');
    }

    /**
     * Validates a language
     * @return boolean
     */
    protected function validateLanguage()
    {
        $this->setSubmitted('language');
        $this->setSubmittedBool('status');
        $this->setSubmittedBool('default');
        $this->setSubmitted('update', $this->data_language);

        $this->validateComponent('language');

        return !$this->hasErrors();
    }

    /**
     * Updates a language
     */
    protected function updateLanguage()
    {
        $this->controlAccess('language_edit');
        $this->language->update($this->data_language['code'], $this->getSubmitted());
        // Redirect to a path without language code to avoid "Page not found" if the current language has been disabled
        $this->redirect('admin/settings/language', $this->text('Language has been updated'), 'success', true);
    }

    /**
     * Adds a new language
     */
    protected function addLanguage()
    {
        $this->controlAccess('language_add');
        $this->language->add($this->getSubmitted());
        $this->redirect('admin/settings/language', $this->text('Language has been added'), 'success');
    }

    /**
     * Sets titles on the edit language page
     */
    protected function setTitleEditLanguage()
    {
        if (isset($this->data_language['code'])) {
            $vars = array('%name' => $this->data_language['native_name']);
            $title = $this->text('Edit %name', $vars);
        } else {
            $title = $this->text('Add language');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit language page
     */
    protected function setBreadcrumbEditLanguage()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'url' => $this->url('admin/settings/language'),
            'text' => $this->text('Languages')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the edit language page
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
        $this->refreshLanguage();

        $this->setTitleListLanguage();
        $this->setBreadcrumbListLanguage();

        $this->setData('languages', $this->getListLanguage());
        $this->outputListLanguage();
    }

    /**
     * Returns an array of prepared languages
     * @return array
     */
    protected function getListLanguage()
    {
        $languages = $this->language->getList();

        $in_database = $codes = $statuses = array();
        foreach ($languages as $code => &$language) {
            $codes[$code] = $code;
            $statuses[$code] = !empty($language['status']);
            $in_database[$code] = !empty($language['in_database']);
            $language['file_exists'] = is_file($this->language->getFile($code));
        }

        array_multisort($in_database, SORT_DESC, $statuses, SORT_DESC, $codes, SORT_ASC, $languages);
        return $languages;
    }

    /**
     * Removes a cached translation
     */
    protected function refreshLanguage()
    {
        $this->controlToken('refresh');
        $code = $this->getQuery('refresh');
        if (!empty($code) && $this->access('language_edit') && $this->language->refresh($code)) {
            $this->redirect('', $this->text('Language has been refreshed'), 'success');
        }
    }

    /**
     * Sets title on the language overview page
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
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the language overview page
     */
    protected function outputListLanguage()
    {
        $this->output('settings/language/list');
    }

}
