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
 * Handles incoming requests and outputs data related to users
 */
class User extends BackendController
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * A n array of user data
     * @var array
     */
    protected $data_user = array();

    /**
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
        $this->actionListUser();

        $this->setTitleListUser();
        $this->setBreadcrumbListUser();

        $this->setFilterListUser();
        $this->setTotalListUser();
        $this->setPagerLimit();

        $this->setData('roles', $this->role->getList());
        $this->setData('users', $this->getListUser());

        $this->outputListUser();
    }

    /**
     * Set filter on the user overview page
     */
    protected function setFilterListUser()
    {
        $allowed = array('name', 'email', 'role_id', 'store_id',
            'status', 'created', 'user_id');

        $this->setFilter($allowed);
    }

    /**
     * Sets a total number of users found for the filter conditions
     */
    protected function setTotalListUser()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->user->getList($query);
    }

    /**
     * Applies an action to the selected users
     */
    protected function actionListUser()
    {
        $value = (string) $this->getPosted('value');
        $action = (string) $this->getPosted('action');
        $selected = (array) $this->getPosted('selected', array());

        if (empty($action)) {
            return null;
        }

        $deleted = $updated = 0;
        foreach ($selected as $uid) {

            if ($this->isSuperadmin($uid)) {
                continue;
            }

            if ($action === 'status' && $this->access('user_edit')) {
                $updated += (int) $this->user->update($uid, array('status' => $value));
            }

            if ($action === 'delete' && $this->access('user_delete')) {
                $deleted += (int) $this->user->delete($uid);
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
     * Returns an array of users
     * @return array
     */
    protected function getListUser()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;

        $users = (array) $this->user->getList($query);
        return $this->prepareListUser($users);
    }

    /**
     * Prepare an array of users
     * @param array $users
     * @return array
     */
    protected function prepareListUser(array $users)
    {
        $stores = $this->store->getList();

        foreach ($users as &$user) {
            $user['url'] = '';
            if (isset($stores[$user['store_id']])) {
                $user['url'] = $this->store->url($stores[$user['store_id']]) . "/account/{$user['user_id']}";
            }
        }

        return $users;
    }

    /**
     * Sets title on the user overview page
     */
    protected function setTitleListUser()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Sets breadcrumbs on the user overview page
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
     * Render and output the user overview page
     */
    protected function outputListUser()
    {
        $this->output('user/list');
    }

    /**
     * Displays the user edit page
     * @param integer|null $user_id
     */
    public function editUser($user_id = null)
    {
        $this->setUser($user_id);

        $this->controlAccessEditUser($user_id);

        $this->setData('user', $this->data_user);
        $this->setData('roles', $this->role->getList());
        $this->setData('can_delete', $this->canDeleteUser());
        $this->setData('is_superadmin', $this->isSuperadminUser());

        $this->submitEditUser();

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
        return isset($this->data_user['user_id'])//
                && $this->access('user_delete')//
                && $this->user->canDelete($this->data_user['user_id']);
    }

    /**
     * Whether the user is superadmin
     * @return bool
     */
    protected function isSuperadminUser()
    {
        return isset($this->data_user['user_id'])//
                && $this->isSuperadmin($this->data_user['user_id']);
    }

    /**
     * Sets a user data
     * @param integer|null $user_id
     */
    protected function setUser($user_id)
    {
        if (is_numeric($user_id)) {
            $this->data_user = $this->user->get($user_id);
            if (empty($this->data_user)) {
                $this->outputHttpStatus(404);
            }
        }
    }

    /**
     * Controls access for the given user ID
     * @param integer $user_id
     */
    protected function controlAccessEditUser($user_id)
    {
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Handles a submitted user data
     */
    protected function submitEditUser()
    {
        if ($this->isPosted('delete')) {
            $this->deleteUser();
            return null;
        }

        if (!$this->isPosted('save') || !$this->validateEditUser()) {
            return null;
        }

        if (isset($this->data_user['user_id'])) {
            $this->updateUser();
        } else {
            $this->addUser();
        }
    }

    /**
     * Validates a submitted user data
     * @return bool
     */
    protected function validateEditUser()
    {
        $this->setSubmitted('user');
        $this->setSubmittedBool('status');
        $this->setSubmitted('update', $this->data_user);

        $this->validateComponent('user', array('admin' => $this->access('user_edit')));

        return !$this->hasErrors();
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
     * Updates a user
     */
    protected function updateUser()
    {
        $this->controlAccess('user_edit');

        $values = $this->getSubmitted();
        $this->user->update($this->data_user['user_id'], $values);

        $message = $this->text('User has been updated');
        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Adds a new user
     */
    protected function addUser()
    {
        $this->controlAccess('user_add');
        $this->user->add($this->getSubmitted());

        $message = $this->text('User has been added');
        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Sets title on the edit user page
     */
    protected function setTitleEditUser()
    {
        $title = $this->text('Add user');

        if (isset($this->data_user['name'])) {
            $title = $this->text('Edit user %name', array('%name' => $this->data_user['name']));
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
     * Render and output the edit user page
     */
    protected function outputEditUser()
    {
        $this->output('user/edit');
    }

}
