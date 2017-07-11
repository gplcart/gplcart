<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to user roles
 */
class UserRole extends BackendController
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * An array of user role data
     * @var array
     */
    protected $data_role = array();

    /**
     * @param UserRoleModel $role
     */
    public function __construct(UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Displays the user role edit page
     * @param integer|null $role_id
     */
    public function editUserRole($role_id = null)
    {
        $this->setUserRole($role_id);

        $this->setTitleEditUserRole();
        $this->setBreadcrumbEditUserRole();

        $this->setData('role', $this->data_role);
        $this->setData('permissions', $this->getPermissionsUserRole(true));

        $this->submitEditUserRole();
        $this->outputEditUserRole();
    }

    /**
     * Returns an array of permissions
     * @param bool $chunked
     * @return array
     */
    protected function getPermissionsUserRole($chunked = false)
    {
        $permissions = $this->role->getPermissions();

        if ($chunked) {
            $permissions = gplcart_array_split($permissions, 4);
        }

        return $permissions;
    }

    /**
     * Returns a user role data
     * @param integer|null $role_id
     * @return array
     */
    protected function setUserRole($role_id)
    {
        if (is_numeric($role_id)) {
            $this->data_role = $this->role->get($role_id);
            if (empty($this->data_role)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Handles a submitted user role data
     */
    protected function submitEditUserRole()
    {
        if ($this->isPosted('delete')) {
            $this->deleteUserRole();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditUserRole()) {
            return null;
        }

        if (isset($this->data_role['role_id'])) {
            $this->updateUserRole();
        } else {
            $this->addUserRole();
        }
    }

    /**
     * Validates a submitted user role data
     * @return boolean
     */
    protected function validateEditUserRole()
    {
        $this->setSubmitted('role');
        $permissions = $this->getSubmitted('permissions');

        if (empty($permissions)) {
            $this->setSubmitted('permissions', array());
        }

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_role);

        $this->validateComponent('user_role');

        return !$this->hasErrors();
    }

    /**
     * Deletes a user role
     */
    protected function deleteUserRole()
    {
        $this->controlAccess('user_role_delete');

        $deleted = $this->role->delete($this->data_role['role_id']);

        if ($deleted) {
            $message = $this->text('Role has been deleted');
            $this->redirect('admin/user/role', $message, 'success');
        }

        $message = $this->text('Unable to delete this role');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Updates a user role
     */
    protected function updateUserRole()
    {
        $this->controlAccess('user_role_edit');

        $values = $this->getSubmitted();
        $this->role->update($this->data_role['role_id'], $values);

        $message = $this->text('Role has been updated');
        $this->redirect('admin/user/role', $message, 'success');
    }

    /**
     * Adds a new user role
     */
    protected function addUserRole()
    {
        $this->controlAccess('user_role_add');
        $this->role->add($this->getSubmitted());

        $message = $this->text('Role has been added');
        $this->redirect('admin/user/role', $message, 'success');
    }

    /**
     * Sets titles on the user role edit page
     */
    protected function setTitleEditUserRole()
    {
        $title = $this->text('Add role');

        if (isset($this->data_role['role_id'])) {
            $vars = array('%name' => $this->data_role['name']);
            $title = $this->text('Edit role %name', $vars);
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the user role edit page
     */
    protected function setBreadcrumbEditUserRole()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Roles'),
            'url' => $this->url('admin/user/role')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the user role edit page
     */
    protected function outputEditUserRole()
    {
        $this->output('user/role/edit');
    }

    /**
     * Displays the user role overview page
     */
    public function listUserRole()
    {
        $this->actionListUserRole();

        $this->setTitleListUserRole();
        $this->setBreadcrumbListUserRole();

        $this->setFilterListUserRole();
        $this->setTotalListUserRole();
        $this->setPagerLimit();

        $this->setData('roles', $this->getListUserRole());
        $this->outputListUserRole();
    }

    /**
     * Sets filter on the user role overview page
     */
    protected function setFilterListUserRole()
    {
        $this->setFilter(array('name', 'role_id', 'status', 'created'));
    }

    /**
     * Sets a total number of user roles found for the filter conditions
     */
    protected function setTotalListUserRole()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->role->getList($query);
    }

    /**
     * Applies an action to the selected user roles
     */
    protected function actionListUserRole()
    {
        $value = $this->getPosted('value', '', true, 'string');
        $action = $this->getPosted('action', '', true, 'string');
        $selected = $this->getPosted('selected', array(), true, 'array');

        if (empty($action)) {
            return null;
        }

        $deleted = $updated = 0;
        foreach ($selected as $role_id) {

            if ($action === 'status' && $this->access('user_role_edit')) {
                $updated += (int) $this->role->update($role_id, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('user_role_delete')) {
                $deleted += (int) $this->role->delete($role_id);
            }
        }

        if ($updated > 0) {
            $text = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($text, 'success', true);
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($text, 'success', true);
        }
    }

    /**
     * Returns an array of user roles
     * @return array
     */
    protected function getListUserRole()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;

        $roles = (array) $this->role->getList($query);
        return $this->prepareListUserRole($roles);
    }

    /**
     * Prepare an array of user roles
     * @param array $roles
     * @return array
     */
    protected function prepareListUserRole(array $roles)
    {
        $permissions = $this->getPermissionsUserRole();

        foreach ($roles as &$role) {
            if (!empty($role['permissions'])) {
                $list = array_intersect_key($permissions, array_flip($role['permissions']));
                $role['permissions_list'] = array_chunk($list, 20);
            }
        }

        return $roles;
    }

    /**
     * Sets title on the user role overview page
     */
    protected function setTitleListUserRole()
    {
        $this->setTitle($this->text('Roles'));
    }

    /**
     * Sets breadcrumbs on the user role overview page
     */
    protected function setBreadcrumbListUserRole()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the user role overview page
     */
    protected function outputListUserRole()
    {
        $this->output('user/role/list');
    }

}
