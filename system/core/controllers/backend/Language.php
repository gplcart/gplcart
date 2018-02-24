<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to languages
 */
class Language extends Controller
{

    use ListingTrait;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of language data
     * @var array
     */
    protected $data_language = array();

    /**
     * Displays the language edit form
     * @param string|null $code
     */
    public function editLanguage($code = null)
    {
        $this->setLanguage($code);
        $this->setTitleEditLanguage();
        $this->setBreadcrumbEditLanguage();

        $this->setData('edit', isset($code));
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
        return isset($this->data_language['code'])
            && $this->access('language_delete')
            && $this->language->canDelete($this->data_language['code']);
    }

    /**
     * Set a language data
     * @param string $code
     */
    protected function setLanguage($code)
    {
        $this->data_language = array();

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

        $this->redirect('', $this->text('Language has not been deleted'), 'warning');
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

        if ($this->language->update($this->data_language['code'], $this->getSubmitted())) {
            $this->redirect('admin/settings/language', $this->text('Language has been updated'), 'success', true);
        }

        $this->redirect('', $this->text('Language has not been updated'), 'warning', true);
    }

    /**
     * Adds a new language
     */
    protected function addLanguage()
    {
        $this->controlAccess('language_add');

        if ($this->language->add($this->getSubmitted())) {
            $this->redirect('admin/settings/language', $this->text('Language has been added'), 'success');
        }

        $this->redirect('', $this->text('Language has not been added'), 'warning');
    }

    /**
     * Sets titles on the edit language page
     */
    protected function setTitleEditLanguage()
    {
        if (isset($this->data_language['code'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_language['native_name']));
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
        $this->setFilterListLanguage();
        $this->setPagerListLanguage();

        $this->setData('languages', (array) $this->getListLanguage());
        $this->outputListLanguage();
    }

    /**
     * Sets the filter on the language overview page
     */
    protected function setFilterListLanguage()
    {
        $this->setFilter($this->getAllowedFiltersLanguage());
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListLanguage()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListLanguage(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of allowed fields for sorting and filtering
     * @return array
     */
    protected function getAllowedFiltersLanguage()
    {
        return array('name', 'native_name', 'code', 'rtl', 'default', 'in_database', 'status');
    }

    /**
     * Returns an array of prepared languages
     * @param bool $count
     * @return array|int
     */
    protected function getListLanguage($count = false)
    {
        $list = $this->language->getList();

        $allowed = $this->getAllowedFiltersLanguage();
        $this->filterList($list, $allowed, $this->query_filter);
        $this->sortList($list, $allowed, $this->query_filter, array('code' => 'asc'));

        if ($count) {
            return count($list);
        }

        $this->limitList($list, $this->data_limit);
        $this->prepareListLanguage($list);
        return $list;
    }

    /**
     * Prepare an array of languages
     * @param array $list
     */
    protected function prepareListLanguage(array &$list)
    {
        foreach ($list as $id => &$item) {
            $item['file_exists'] = is_file($this->translation->getFile($id));
        }
    }

    /**
     * Removes a cached translation
     */
    protected function refreshLanguage()
    {
        $this->controlToken('refresh');

        $code = $this->getQuery('refresh');
        if (!empty($code) && $this->access('language_edit') && $this->translation->refresh($code)) {
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
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the language overview page
     */
    protected function outputListLanguage()
    {
        $this->output('settings/language/list');
    }

}
