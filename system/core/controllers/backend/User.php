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
 * Handles incoming requests and outputs data related to user management
 */
class User extends BackendController
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * The current user
     * @var array
     */
    protected $data_user = array();

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
     * Displays the users overview page
     */
    public function listUser()
    {
        $this->actionUser();

        $this->setTitleListUser();
        $this->setBreadcrumbListUser();

        $query = $this->getFilterQuery();

        $filters = array('name', 'email', 'role_id', 'store_id',
            'status', 'created', 'user_id');

        $this->setFilter($filters, $query);

        $total = $this->getTotalUser($query);
        $limit = $this->setPager($total, $query);

        $this->setData('roles', $this->role->getList());
        $this->setData('stores', $this->store->getNames());
        $this->setData('users', $this->getListUser($limit, $query));

        $this->outputListUser();
    }

    /**
     * Applies an action to users
     */
    protected function actionUser()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $uid) {

            if ($this->isSuperadmin($uid)) {
                continue; // Exclude super admin
            }

            if ($action == 'status' && $this->access('user_edit')) {
                $updated += (int) $this->user->update($uid, array('status' => $value));
            }

            if ($action == 'delete' && $this->access('user_delete')) {
                $deleted += (int) $this->user->delete($uid);
            }
        }

        if ($updated > 0) {
            $text = $this->text('Updated %num users', array('%num' => $updated));
            $this->setMessage($text, 'success', true);
        }

        if ($deleted > 0) {
            $text = $this->text('Deleted %num users', array('%num' => $deleted));
            $this->setMessage($text, 'success', true);
        }
    }

    /**
     * Returns total number of users
     * @param array $query
     * @return integer
     */
    protected function getTotalUser(array $query)
    {
        $query['count'] = true;
        return (int) $this->user->getList($query);
    }

    /**
     * Returns an array of users
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListUser(array $limit, array $query)
    {
        $query['limit'] = $limit;

        $stores = $this->store->getList();
        $users = (array) $this->user->getList($query);

        foreach ($users as &$user) {
            $user['url'] = '';
            if (isset($stores[$user['store_id']])) {
                $user['url'] = $this->store->url($stores[$user['store_id']]) . "/account/{$user['user_id']}";
            }
        }

        return $users;
    }

    /**
     * Sets titles on the users overview page
     */
    protected function setTitleListUser()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Sets breadcrumbs on the users overview page
     */
    protected function setBreadcrumbListUser()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the users overview page
     */
    protected function outputListUser()
    {
        $this->output('user/list');
    }

    /**
     * Displays the user edit page
     * @param integer $user_id
     */
    public function editUser($user_id = null)
    {
        $this->setUser($user_id);

        $this->controlAccessEditUser($user_id);

        $this->setData('user', $this->data_user);
        $this->setData('roles', $this->role->getList());
        $this->setData('stores', $this->store->getNames());
        $this->setData('can_delete', $this->canDeleteUser());
        $this->setData('is_superadmin', $this->isSuperadminUser());

        $this->submitUser();

        $this->setTitleEditUser();
        $this->setBreadcrumbEditUser();
        $this->outputEditUser();
    }

    /**
     * Whether the user can be deleted
     * @return boolean
     */
    protected function canDeleteUser()
    {
        return isset($this->data_user['user_id']) && $this->access('user_delete') && $this->user->canDelete($this->data_user['user_id']);
    }

    /**
     * Whether the user is superadmin
     * @return bool
     */
    protected function isSuperadminUser()
    {
        return isset($this->data_user['user_id']) && $this->isSuperadmin($this->data_user['user_id']);
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function setUser($user_id)
    {
        if (!is_numeric($user_id)) {
            return array();
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->outputHttpStatus(404);
        }

        $this->data_user = $user;
        return $user;
    }

    /**
     * 
     * @param type $user_id
     */
    protected function controlAccessEditUser($user_id)
    {
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Saves submitted user data
     * @return null
     */
    protected function submitUser()
    {
        if ($this->isPosted('delete')) {
            $this->deleteUser();
            return null;
        }

        if (!$this->isPosted('save')) {
            return null;
        }

        $this->setSubmitted('user');
        $this->validateUser();

        if ($this->hasErrors('user')) {
            return null;
        }

        if (isset($this->data_user['user_id'])) {
            $this->updateUser();
            return null;
        }

        $this->addUser();
    }

    /**
     * Deletes a user
     */
    protected function deleteUser()
    {
        $this->controlAccess('user_delete');

        $deleted = $this->user->delete($this->data_user['user_id']);

        if ($deleted) {
            $message = $this->text('User has been deleted');
            $this->redirect('admin/user/list', $message, 'success');
        }

        $message = $this->text('Unable to delete this user');
        $this->redirect('admin/user/list', $message, 'danger');
    }

    /**
     * Validates submitted user data
     */
    protected function validateUser()
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_user);
        $this->validate('user', array('admin' => $this->access('user_edit')));
    }

    /**
     * Updates a user with submitted values
     */
    protected function updateUser()
    {
        $this->controlAccess('user_edit');

        $values = $this->getSubmitted();
        $this->user->update($this->data_user['user_id'], $values);

        $vars = array('%name' => $this->data_user['name']);
        $message = $this->text('User %name has been updated', $vars);

        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Adds a new user using an array of submitted values
     */
    protected function addUser()
    {
        $this->controlAccess('user_add');
        $this->user->add($this->getSubmitted());

        $message = $this->text('User has been added');
        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEditUser()
    {
        $title = $this->text('Add user');

        if (isset($this->data_user['name'])) {
            $title = $this->text('Edit %user', array('%user' => $this->data_user['name']));
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit user page
     */
    protected function setBreadcrumbEditUser()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Users'),
            'url' => $this->url('admin/user/list')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEditUser()
    {
        $this->output('user/edit');
    }

}
