<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Language as ModelsLanguage;

/**
 * Handles incoming requests and outputs data related to languages
 */
class Language extends Controller
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Displays the language edit form
     * @param string|null $code
     */
    public function edit($code = null)
    {
        $language = $this->get($code);
        $default = $this->language->getDefault();

        $this->setData('language', $language);
        $this->setData('default_language', $default);

        if ($this->isPosted('delete')) {
            $this->delete($language);
        }

        if ($this->isPosted('save')) {
            $this->submit($language);
        }

        $this->setTitleEdit($language);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Displays the language overview page
     */
    public function languages()
    {
        $this->setRefresh();

        $languages = $this->language->getList();
        $this->setData('languages', $languages);

        $this->setTitleLanguages();
        $this->setBreadcrumbLanguages();
        $this->outputLanguages();
    }

    /**
     * Controls the current URL and refreshes a language if needed
     */
    protected function setRefresh()
    {
        $code = (string) $this->request->get('refresh');

        if (!empty($code)) {
            $this->refresh($code);
        }
    }

    /**
     * Sets titles on the edit language page
     * @param array $language
     */
    protected function setTitleEdit(array $language)
    {
        if (isset($language['code'])) {
            $title = $this->text('Edit language %name', array('%name' => $language['native_name']));
        } else {
            $title = $this->text('Add language');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit language page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));

        $this->setBreadcrumb(array(
            'url' => $this->url('admin/settings/language'),
            'text' => $this->text('Languages')));
    }

    /**
     * Renders the edit language page templates
     */
    protected function outputEdit()
    {
        $this->output('settings/language/edit');
    }

    /**
     * Saves a submitted language
     * @param array $language
     * @return null
     */
    protected function submit(array $language)
    {
        $this->setSubmitted('language');
        $this->validate($language);

        if ($this->hasErrors('language')) {
            return;
        }

        if (isset($language['code'])) {
            $this->controlAccess('language_edit');
            $this->language->update($language['code'], $this->getSubmitted());
            $this->redirect('admin/settings/language', $this->text('Language has been updated'), 'success');
        }

        $this->controlAccess('language_add');
        $this->language->add($this->getSubmitted());
        $this->redirect('admin/settings/language', $this->text('Language has been added'), 'success');
    }

    /**
     * Sets titles on the language overview page
     */
    protected function setTitleLanguages()
    {
        $this->setTitle($this->text('Languages'));
    }

    /**
     * Sets breadcrumbs on the language overview page
     */
    protected function setBreadcrumbLanguages()
    {
        $this->setBreadcrumb(array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')));
    }

    /**
     * Renders the language overview page templates
     */
    protected function outputLanguages()
    {
        $this->output('settings/language/list');
    }

    /**
     * Refreshes translation files for a given language
     * @param string $code
     */
    protected function refresh($code)
    {
        $this->language->refresh($code);
        $this->redirect();
    }

    /**
     * Returns a language
     * @param string $code
     * @return array
     */
    protected function get($code)
    {
        if (empty($code)) {
            return array();
        }

        $language = $this->language->get($code);

        if (empty($language)) {
            $this->outputError(404);
        }

        return $language;
    }

    /**
     * Deletes a language
     * @param array $language
     * @return null
     */
    protected function delete(array $language)
    {
        $this->controlAccess('language_delete');

        $deleted = $this->language->delete($language['code']);

        if ($deleted) {
            $this->redirect('admin/settings/language', $this->text('Language has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this language. The most probable reason - it is default language or blocked by modules'), 'danger');
    }

    /**
     * Validates a language
     * @param array $language
     */
    protected function validate(array $language)
    {

        $this->addValidator('code', array(
            'regexp' => array('pattern' => '/^[a-z]{2}$/', 'required' => true)));

        $this->addValidator('name', array(
            'regexp' => array('pattern' => '/^[A-Za-z]{1,50}$/', 'required' => true)));

        $this->addValidator('native_name', array(
            'length' => array('min' => 1, 'max' => 255)));

        $this->addValidator('weight', array(
            'numeric' => array(),
            'length' => array('max' => 2, 'required' => true)));

        $this->setValidators($language);
    }

}
