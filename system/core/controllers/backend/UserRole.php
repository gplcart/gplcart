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
     * The current user role
     * @var array
     */
    protected $data_role = array();

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
        $this->setUserRole($role_id);

        $this->setTitleEditUserRole();
        $this->setBreadcrumbEditUserRole();

        $this->setData('role', $this->data_role);
        $this->setData('permissions', $this->getPermissionsUserRole(true));

        $this->submitUserRole();
        $this->outputEditUserRole();
    }

    /**
     * Returns an array of prepared permissions
     * @param bool $chunked
     * @return array
     */
    protected function getPermissionsUserRole($chunked = false)
    {
        $permissions = $this->role->getPermissions();
        $translated = array_map(array($this, 'text'), $permissions);

        if ($chunked) {
            return gplcart_array_split($translated, 4);
        }

        return $translated;
    }

    /**
     * Returns a role
     * @param integer $role_id
     * @return array
     */
    protected function setUserRole($role_id)
    {
        if (!is_numeric($role_id)) {
            return array();
        }

        $role = $this->role->get($role_id);

        if (empty($role)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_role = $role;
    }

    /**
     * Saves a submitted user role
     * @return null
     */
    protected function submitUserRole()
    {
        if ($this->isPosted('delete')) {
            $this->deleteUserRole();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateUserRole()) {
            return null;
        }

        if (isset($this->data_role['role_id'])) {
            $this->updateUserRole();
        } else {
            $this->addUserRole();
        }
    }

    /**
     * Validates a submitted user role
     * @return bool
     */
    protected function validateUserRole()
    {
        $this->setSubmitted('role');

        $permissions = $this->getSubmitted('permissions');

        if (empty($permissions)) {
            $this->setSubmitted('permissions', array());
        }

        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_role);

        $this->validateComponent('user_role');

        return !$this->hasErrors('role');
    }

    /**
     * Deletes a role
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
     * Updates a user role with submitted values
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
     * Adds a new user role using submitted values
     */
    protected function addUserRole()
    {
        $this->controlAccess('user_role_add');

        $this->role->add($this->getSubmitted());

        $message = $this->text('Role has been added');
        $this->redirect('admin/user/role', $message, 'success');
    }

    /**
     * Sets titles on the role edit form
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

        $this->setTitleListUserRole();
        $this->setBreadcrumbListUserRole();

        $query = $this->getFilterQuery();

        $filters = array('name', 'role_id', 'status', 'created');
        $this->setFilter($filters, $query);

        $total = $this->getTotalUserRole($query);
        $limit = $this->setPager($total, $query);

        $this->setData('roles', $this->getListUserRole($limit, $query));
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
        $permissions = $this->getPermissionsUserRole();

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
