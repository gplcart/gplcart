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

        $this->data['language'] = $language;
        $this->data['default_language'] = $this->language->getDefault();

        if ($this->request->post('delete')) {
            $this->delete($language);
        }

        if ($this->request->post('save')) {
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
        $code = (string) $this->request->get('refresh');

        if (!empty($code)) {
            $this->refresh($code);
        }

        $this->data['languages'] = $this->language->getList();

        $this->setTitleLanguages();
        $this->setBreadcrumbLanguages();
        $this->outputLanguages();
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
        $this->setBreadcrumb(array('url' => $this->url('admin/settings/language'), 'text' => $this->text('Languages')));
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
        $this->submitted = $this->request->post('language', array());
        $this->validate();

        $errors = $this->getErrors();

        if (!empty($errors)) {
            $this->data['language'] = $this->submitted;
            return;
        }

        if (isset($language['code'])) {
            $this->controlAccess('language_edit');
            $this->language->update($language['code'], $this->submitted);
            $this->redirect('admin/settings/language', $this->text('Language has been updated'), 'success');
        }

        $this->controlAccess('language_add');
        $this->language->add($this->submitted);
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
        $this->setBreadcrumb(array('url' => $this->url('admin'), 'text' => $this->text('Dashboard')));
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
        // TODO: exit() issue. Cannot use redirect message. The current page translation file will only have the message
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
        if (empty($language['code'])) {
            return;
        }

        $this->controlAccess('language_delete');

        if ($this->language->delete($language['code'])) {
            $this->redirect('admin/settings/language', $this->text('Language has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this language. The most probable reason - it is default language or blocked by modules'), 'danger');
    }

    /**
     * Validates a language
     * @param boolean $data
     */
    protected function validate()
    {
        $this->validateCode();
        $this->validateName();
        $this->validateNativeName();
        $this->validateWeight();
    }

    /**
     * Validates ISO 639-1 compliance
     * @return boolean
     */
    protected function validateCode()
    {
        if (preg_match('/^[a-z]{2}$/', $this->submitted['code'])) {
            return true;
        }

        $this->errors['code'] = $this->text('Invalid language code. Use only ISO 639-1 codes');
        return false;
    }

    /**
     * Validates name field
     * @return boolean
     */
    protected function validateName()
    {
        if (preg_match('/^[A-Za-z]{1,50}$/', $this->submitted['name'])) {
            return true;
        }

        $this->errors['name'] = $this->text('Invalid language name. It must be 1 - 50 long and contain only latin characters');
        return false;
    }

    /**
     * Validates native name field
     * @return boolean
     */
    protected function validateNativeName()
    {
        if (empty($this->submitted['native_name']) || mb_strlen($this->submitted['native_name']) > 255) {
            $this->errors['native_name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }
        return true;
    }

    /**
     * Validates weight field
     * @return boolean
     */
    protected function validateWeight()
    {
        if ($this->submitted['weight']) {
            if (!is_numeric($this->submitted['weight']) || strlen($this->submitted['weight']) > 2) {
                $this->errors['weight'] = $this->text('Only numeric value and no more than %s digits', array('%s' => 2));
                return false;
            }
            return true;
        }

        $this->submitted['weight'] = 0;
        return true;
    }

}
