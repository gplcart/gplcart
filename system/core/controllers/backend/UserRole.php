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
     * Constructor
     * @param UserRoleModel $role
     */
    public function __construct(UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Displays the role edit form
     * @param integer|null $role_id
     */
    public function editUserRole($role_id = null)
    {
        $role = $this->getUserRole($role_id);
        $permissions = $this->role->getPermissions();
        $permissions_chunked = array_chunk($permissions, 30, true);

        $this->setData('role', $role);
        $this->setData('permissions', $permissions_chunked);

        $this->submitUserRole($role);

        $this->setTitleEditUserRole($role);
        $this->setBreadcrumbEditUserRole();
        $this->outputEditUserRole();
    }

    /**
     * Returns a role
     * @param integer $role_id
     * @return array
     */
    protected function getUserRole($role_id)
    {
        if (!is_numeric($role_id)) {
            return array();
        }

        $role = $this->role->get($role_id);

        if (empty($role)) {
            $this->outputError(404);
        }

        return $role;
    }

    /**
     * Saves a submitted user role
     * @param array $role
     * @return null|void
     */
    protected function submitUserRole(array $role)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteUserRole($role);
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('role');
        $this->validateUserRole($role);

        if ($this->hasErrors('role')) {
            return null;
        }

        if (isset($role['role_id'])) {
            return $this->updateUserRole($role);
        }

        return $this->addUserRole();
    }

    /**
     * Deletes a role
     * @param array $role
     */
    protected function deleteUserRole(array $role)
    {
        $this->controlAccess('user_role_delete');

        $deleted = $this->role->delete($role['role_id']);

        if ($deleted) {
            $message = $this->text('Role has been deleted');
            $this->redirect('admin/user/role', $message, 'success');
        }

        $message = $this->text('Unable to delete this role');
        $this->redirect('', $message, 'danger');
    }

    /**
     * Validates a submitted user role
     * @param array $role
     */
    protected function validateUserRole(array $role)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $role);
        $this->validate('user_role');
    }

    /**
     * Updates a user role with submitted values
     * @param array $role
     */
    protected function updateUserRole(array $role)
    {
        $this->controlAccess('user_role_edit');

        $values = $this->getSubmitted();
        $this->role->update($role['role_id'], $values);

        $message = $this->text('Role has been updated');
        $this->redirect('admin/user/role', $message, 'success');
    }

    /**
     * Adds a new user role using submitted values
     */
    protected function addUserRole()
    {
        $this->controlAccess('user_role_add');

        $submitted = $this->getSubmitted();
        $this->role->add($submitted);

        $message = $this->text('Role has been added');
        $this->redirect('admin/user/role', $message, 'success');
    }

    /**
     * Sets titles on the role edit form
     * @param array $role
     */
    protected function setTitleEditUserRole(array $role)
    {
        $title = $this->text('Add role');

        if (isset($role['role_id'])) {
            $title = $this->text('Edit role %name', array('%name' => $role['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the role edit form
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
     * Renders the role edit page
     */
    protected function outputEditUserRole()
    {
        $this->output('user/role/edit');
    }

    /**
     * Displays the roles overview page
     */
    public function listUserRole()
    {
        $this->actionUserRole();

        $query = $this->getFilterQuery();
        $total = $this->getTotalUserRole($query);
        $limit = $this->setPager($total, $query);
        $roles = $this->getListUserRole($limit, $query);

        $this->setData('roles', $roles);

        $filters = array('name', 'role_id', 'status', 'created');
        $this->setFilter($filters, $query);

        $this->setTitleListUserRole();
        $this->setBreadcrumbListUserRole();
        $this->outputListUserRole();
    }

    /**
     * Applies an action to user roles
     * @return null
     */
    protected function actionUserRole()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $role_id) {

            if ($action == 'status' && $this->access('user_role_edit')) {
                $updated += (int) $this->role->update($role_id, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('user_role_delete')) {
                $deleted += (int) $this->role->delete($role_id);
            }
        }

        if ($updated > 0) {
            $text = $this->text('Updated %num user roles', array('%num' => $updated));
            $this->setMessage($text, 'success', true);
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num user roles', array('%num' => $deleted));
            $this->setMessage($text, 'success', true);
        }

        return null;
    }

    /**
     * Returns total number of user roles
     * @param array $query
     * @return integer
     */
    protected function getTotalUserRole(array $query)
    {
        $query['count'] = true;
        return (int) $this->role->getList($query);
    }

    /**
     * Returns an array of roles
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListUserRole(array $limit, array $query)
    {
        $query['limit'] = $limit;
        $roles = (array) $this->role->getList($query);
        $permissions = $this->role->getPermissions();

        foreach ($roles as &$role) {

            if (empty($role['permissions'])) {
                continue;
            }

            $list = array_intersect_key($permissions, array_flip($role['permissions']));
            $role['permissions_list'] = array_chunk($list, 20);
        }

        return $roles;
    }

    /**
     * Sets titles on the roles overview page
     */
    protected function setTitleListUserRole()
    {
        $this->setTitle($this->text('Roles'));
    }

    /**
     * Sets breadcrumbs on the roles overview page
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
     * Renders the roles overview page
     */
    protected function outputListUserRole()
    {
        $this->output('user/role/list');
    }

}
