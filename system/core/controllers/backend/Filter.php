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
        $roles = $this->getUserRoleFilter();
        $filter = $this->getFilterFilter($filter_id);

        $this->setData('roles', $roles);
        $this->setData('filter', $filter);

        $this->submitFilter($filter);

        $this->setTitleEditFilter($filter);
        $this->setBreadcrumbEditFilter();
        $this->outputEditFilter();
    }

    /**
     * Handles a submitted filter data
     * @param array $filter
     * @return null
     */
    protected function submitFilter(array $filter)
    {
        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('filter');
        $this->validateFilter($filter);

        if ($this->hasErrors('filter')) {
            return null;
        }

        $this->updateFilter($filter);
        return null;
    }

    /**
     * Validates a filter
     * @param array $filter
     */
    protected function validateFilter(array $filter)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $filter);
        $this->validate('filter');
    }

    /**
     * Updates a filter
     * @param array $filter
     * @return void
     */
    protected function updateFilter(array $filter)
    {
        $submitted = $this->getSubmitted();
        $result = $this->filter->update($filter['filter_id'], $submitted);

        if (empty($result)) {
            $message = $this->text('An error occurred');
            return $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Filter has been updated');
        return $this->redirect('admin/settings/filter', $message, 'success');
    }

    /**
     * Sets title on the edit filter page
     * @param array $filter
     */
    protected function setTitleEditFilter(array $filter)
    {
        $vars = array('%name' => $filter['name']);
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
    protected function getFilterFilter($filter_id)
    {
        $filter = $this->filter->get($filter_id);

        if (empty($filter)) {
            $this->outputHttpStatus(403);
        }

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
        $filters = $this->getListFilter();

        $this->setData('filters', $filters);

        $this->setTitleListFilter();
        $this->setBreadcrumbListFilter();
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
