<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\UserRole as ModelsUserRole;

/**
 * Handles incoming requests and outputs data related to user role administration
 */
class UserRole extends Controller
{

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
     * @param ModelsUserRole $role
     */
    public function __construct(ModelsUserRole $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Displays the role edit form
     * @param integer|null $role_id
     */
    public function edit($role_id = null)
    {
        $role = $this->get($role_id);

        $this->data['role'] = $role;
        $this->data['permissions'] = array_chunk($this->role->getPermissions(), 30, true);

        if ($this->request->post('delete')) {
            $this->delete($role);
        }

        if ($this->request->post('save')) {
            $this->submit($role);
        }

        $this->setTitleEdit($role);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Displays the roles overview page
     */
    public function roles()
    { 
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalRoles($query), $query);

        $this->data['roles'] = $this->getRoles($total, $query);

        $filters = array('name', 'role_id', 'status', 'created');
        $this->setFilter($filters, $query);

        $this->setTitleRoles();
        $this->setBreadcrumbRoles();
        $this->outputRoles();
    }

    /**
     * Renders the role edit page
     */
    protected function outputEdit()
    {
        $this->output('user/role/edit');
    }

    /**
     * Sets breadcrumbs on the role edit form
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Roles'),
            'url' => $this->url('admin/user/role')));
    }

    /**
     * Sets titles on the role edit form
     * @param array $role
     */
    protected function setTitleEdit(array $role)
    {
        if (isset($role['role_id'])) {
            $title = $this->text('Edit role %name', array('%name' => $role['name']));
        } else {
            $title = $this->text('Add role');
        }

        $this->setTitle($title);
    }

    /**
     * Saves a role
     * @param array $role
     * @return null
     */
    protected function submit(array $role)
    {
        $this->submitted = $this->request->post('role', array());

        $this->validate();

        if ($this->hasError()) {
            $this->data['role'] = $this->submitted;
            return;
        }

        if (isset($role['role_id'])) {
            $this->controlAccess('user_role_edit');
            $this->role->update($role['role_id'], $this->submitted);
            $this->redirect('admin/user/role', $this->text('Role has been updated'), 'success');
        }

        $this->controlAccess('user_role_add');
        $this->role->add($this->submitted);
        $this->redirect('admin/user/role', $this->text('Role has been added'), 'success');
    }

    /**
     * Deletes a role
     * @param array $role
     */
    protected function delete(array $role)
    {
        $this->controlAccess('user_role_delete');

        if ($this->role->delete($role['role_id'])) {
            $this->redirect('admin/user/role', $this->text('Role has been deleted'), 'success');
        }

        $this->redirect('', $this->text('Unable to delete this role. The most probable reason - it is used by users'), 'danger');
    }

    /**
     * Returns a role
     * @param integer $role_id
     * @return array
     */
    protected function get($role_id)
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
     * Validates a role
     */
    protected function validate()
    {
        $this->validatePermissions();
        $this->validateName();
    }

    /**
     * Validates role name
     * @return boolean
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->errors['name'] = $this->text('Content must be %min - %max characters long', array('%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates role permission
     * @return boolean
     */
    protected function validatePermissions()
    {
        if (empty($this->submitted['permissions'])) {
            $this->submitted['permissions'] = array();
        }

        return true;
    }

    /**
     * Returns total number of user roles
     * @param array $query
     * @return integer
     */
    protected function getTotalRoles(array $query)
    {
        return $this->role->getList(array('count' => true) + $query);
    }

    /**
     * Renders the roles overview page
     */
    protected function outputRoles()
    {
        $this->output('user/role/list');
    }

    /**
     * Sets breadcrumbs on the roles overview page
     */
    protected function setBreadcrumbRoles()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Sets titles on the roles overview page
     */
    protected function setTitleRoles()
    {
        $this->setTitle($this->text('Roles'));
    }

    /**
     * Returns an array of roles
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getRoles(array $limit, array $query)
    {
        $roles = $this->role->getList(array('limit' => $limit) + $query);

        foreach ($roles as &$role) {
            if (!empty($role['permissions'])) {
                $list = array_intersect_key($this->role->getPermissions(), array_flip($role['permissions']));
                $role['permissions_list'] = array_chunk($list, 20);
            }
        }

        return $roles;
    }

    /**
     * Applies an action to user roles
     * @param array $selected
     * @param string $action
     * @param string $value
     */
    protected function action(array $selected, $action, $value)
    {
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
            $this->session->setMessage($this->text('Updated %num user roles', array('%num' => $updated)), 'success');
            return true;
        }

        if ($deleted > 0) {
            $this->session->setMessage($this->text('Deleted %num user roles', array('%num' => $deleted)), 'success');
            return true;
        }

        return false;
    }

}
