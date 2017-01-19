<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Filter as FilterModel;
use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to HTML filters
 */
class Filter extends BackendController
{

    /**
     * Filter model instance
     * @var \gplcart\core\models\Filter $filter
     */
    protected $filter;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * The current filter data to be edited
     * @var array
     */
    protected $data_filter = array();

    /**
     * Controller
     * @param FilterModel $filter
     * @param UserRoleModel $role
     */
    public function __construct(FilterModel $filter, UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
        $this->filter = $filter;
    }

    /**
     * Displays the edit filter page
     * @param integer $filter_id
     */
    public function editFilter($filter_id)
    {
        $this->setFilterFilter($filter_id);

        $this->setTitleEditFilter();
        $this->setBreadcrumbEditFilter();

        $this->setData('filter', $this->data_filter);
        $this->setData('roles', $this->getUserRoleFilter());

        $this->submitFilter();
        $this->outputEditFilter();
    }

    /**
     * Handles a submitted filter data
     */
    protected function submitFilter()
    {
        if ($this->isPosted('save') && $this->validateFilter()) {
            $this->updateFilter();
        }
    }

    /**
     * Validates a filter
     * @return bool
     */
    protected function validateFilter()
    {
        $this->setSubmitted('filter');

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_filter);
        $this->validate('filter');

        return !$this->hasErrors('filter');
    }

    /**
     * Updates a filter
     */
    protected function updateFilter()
    {
        $this->controlAccess('filter_edit');

        $submitted = $this->getSubmitted();
        $result = $this->filter->update($this->data_filter['filter_id'], $submitted);

        if (empty($result)) {
            $message = $this->text('An error occurred');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Filter has been updated');
        $this->redirect('admin/settings/filter', $message, 'success');
    }

    /**
     * Sets title on the edit filter page
     */
    protected function setTitleEditFilter()
    {
        $vars = array('%name' => $this->data_filter['name']);
        $text = $this->text('Edit filter %name', $vars);
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the edit filter page
     */
    protected function setBreadcrumbEditFilter()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $breadcrumbs[] = array(
            'url' => $this->url('admin/settings/filter'),
            'text' => $this->text('Filters')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders templates on the edit filter page
     */
    protected function outputEditFilter()
    {
        $this->output('settings/filter/edit');
    }

    /**
     * Returns a filter data
     * @param integer $filter_id
     * @return array
     */
    protected function setFilterFilter($filter_id)
    {
        $filter = $this->filter->get($filter_id);

        if (empty($filter)) {
            $this->outputHttpStatus(403);
        }

        $this->data_filter = $filter;
        return $filter;
    }

    /**
     * Returns an array of user roles
     * @param bool $enabled
     * @return array
     */
    protected function getUserRoleFilter($enabled = true)
    {
        $conditions = array();

        if ($enabled) {
            $conditions['status'] = true;
        }

        return (array) $this->role->getList($conditions);
    }

    /**
     * Displays the filter list page
     */
    public function listFilter()
    {
        $this->setTitleListFilter();
        $this->setBreadcrumbListFilter();

        $this->setData('filters', $this->getListFilter());
        $this->outputListFilter();
    }

    /**
     * Returns an array of filters
     * @return array
     */
    protected function getListFilter()
    {
        $filters = $this->filter->getList();
        $roles = $this->getUserRoleFilter(false);

        foreach ($filters as &$filter) {
            $filter['config_formatted'] = print_r($filter['config'], true);
            if (isset($filter['role_id']) && isset($roles[$filter['role_id']]['name'])) {
                $filter['role_name'] = $roles[$filter['role_id']]['name'];
            }
        }

        return $filters;
    }

    /**
     * Sets title on the filter list page
     */
    protected function setTitleListFilter()
    {
        $this->setTitle($this->text('Filters'));
    }

    /**
     * Sets breadcrumbs on the filter list page
     */
    protected function setBreadcrumbListFilter()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the filter list page
     */
    protected function outputListFilter()
    {
        $this->output('settings/filter/list');
    }

}
