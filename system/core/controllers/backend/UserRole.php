<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\UserRole as UserRoleModel;

/**
 * Handles incoming requests and outputs data related to user roles
 */
class UserRole extends Controller
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
        return $chunked ? gplcart_array_split($permissions, 3) : $permissions;
    }

    /**
     * Returns a user role data
     * @param integer|null $role_id
     */
    protected function setUserRole($role_id)
    {
        $this->data_role = array();

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
        } else if ($this->isPosted('save') && $this->validateEditUserRole()) {
            if (isset($this->data_role['role_id'])) {
                $this->updateUserRole();
            } else {
                $this->addUserRole();
            }
        }
    }

    /**
     * Validates a submitted user role data
     * @return boolean
     */
    protected function validateEditUserRole()
    {
        $this->setSubmitted('role');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_role);

        if (!$this->getSubmitted('permissions')) {
            $this->setSubmitted('permissions', array());
        }

        $this->validateComponent('user_role');

        return !$this->hasErrors();
    }

    /**
     * Deletes a user role
     */
    protected function deleteUserRole()
    {
        $this->controlAccess('user_role_delete');

        if ($this->role->delete($this->data_role['role_id'])) {
            $this->redirect('admin/user/role', $this->text('Role has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Role has not been deleted'), 'warning');
    }

    /**
     * Updates a user role
     */
    protected function updateUserRole()
    {
        $this->controlAccess('user_role_edit');

        if ($this->role->update($this->data_role['role_id'], $this->getSubmitted())) {
            $this->redirect('admin/user/role', $this->text('Role has been updated'), 'success');
        }

        $this->redirect('', $this->text('Role has not been updated'), 'warning');
    }

    /**
     * Adds a new user role
     */
    protected function addUserRole()
    {
        $this->controlAccess('user_role_add');

        if ($this->role->add($this->getSubmitted())) {
            $this->redirect('admin/user/role', $this->text('Role has been added'), 'success');
        }

        $this->redirect('', $this->text('Role has not been added'), 'warning');
    }

    /**
     * Sets titles on the user role edit page
     */
    protected function setTitleEditUserRole()
    {
        if (isset($this->data_role['role_id'])) {
            $title = $this->text('Edit %name', array('%name' => $this->data_role['name']));
        } else {
            $title = $this->text('Add role');
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
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
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
        $this->setPagerListUserRole();

        $this->setData('user_roles', $this->getListUserRole());
        $this->outputListUserRole();
    }

    /**
     * Sets filter on the user role overview page
     */
    protected function setFilterListUserRole()
    {
        $allowed = array('name', 'role_id', 'status', 'created', 'redirect', 'redirect_like');
        $this->setFilter($allowed);
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListUserRole()
    {
        $options = $this->query_filter;
        $options['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->role->getList($options)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Applies an action to the selected user roles
     */
    protected function actionListUserRole()
    {
        list($selected, $action, $value) = $this->getPostedAction();

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
            $text = $this->text('Updated %num item(s)', array('%num' => $updated));
            $this->setMessage($text, 'success');
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num item(s)', array('%num' => $deleted));
            $this->setMessage($text, 'success');
        }
    }

    /**
     * Returns an array of user roles
     * @return array
     */
    protected function getListUserRole()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $list = (array) $this->role->getList($conditions);
        $this->prepareListUserRole($list);
        return $list;
    }

    /**
     * Prepare an array of user roles
     * @param array $roles
     */
    protected function prepareListUserRole(array &$roles)
    {
        $permissions = $this->getPermissionsUserRole();

        foreach ($roles as &$role) {
            if (!empty($role['permissions'])) {
                $list = array_intersect_key($permissions, array_flip($role['permissions']));
                $role['permissions_list'] = array_chunk($list, 20);
            }
        }
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
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
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
