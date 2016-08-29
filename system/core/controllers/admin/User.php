<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\models\UserRole as ModelsUserRole;
use core\controllers\admin\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to user management
 */
class User extends BackendController
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
     * Displays the users overview page
     */
    public function listUser()
    {
        $this->actionUser();

        $query = $this->getFilterQuery();
        $total = $this->getTotalUser($query);
        $limit = $this->setPager($total, $query);

        $roles = $this->role->getList();
        $stores = $this->store->getNames();
        $users = $this->getListUser($limit, $query);

        $this->setData('users', $users);
        $this->setData('roles', $roles);
        $this->setData('stores', $stores);

        $filters = array('name', 'email', 'role_id',
            'store_id', 'status', 'created');

        $this->setFilter($filters, $query);

        $this->setTitleListUser();
        $this->setBreadcrumbListUser();
        $this->outputListUser();
    }

    /**
     * Renders the users overview page
     */
    protected function outputListUser()
    {
        $this->output('user/list');
    }

    /**
     * Sets breadcrumbs on the users overview page
     */
    protected function setBreadcrumbListUser()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the users overview page
     */
    protected function setTitleListUser()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Applies an action to users
     */
    protected function actionUser()
    {
        $action = (string) $this->request->post('action');

        if ($action) {
            return;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $uid) {

            if ($this->isSuperadmin($uid)) {
                continue; // Exclude superadmin
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
     * Displays the user edit page
     * @param integer $user_id
     */
    public function editUser($user_id = null)
    {
        $user = $this->getUser($user_id);

        $this->controlAccessEditUser($user_id);

        $roles = $this->role->getList();
        $stores = $this->store->getNames();
        $is_superadmin = (isset($user['user_id']) && $this->isSuperadmin($user['user_id']));
        $can_delete = (isset($user['user_id']) && $this->user->canDelete($user['user_id']));

        $this->setData('user', $user);
        $this->setData('roles', $roles);
        $this->setData('stores', $stores);
        $this->setData('can_delete', $can_delete);
        $this->setData('is_superadmin', $is_superadmin);

        $this->submitUser($user);

        $this->setTitleEditUser($user);
        $this->setBreadcrumbEditUser();
        $this->outputEditUser();
    }

    protected function controlAccessEditUser($user_id)
    {
        // Only superadmin can edit its own account
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputError(403);
        }
    }

    /**
     * Deletes a user
     * @param array $user
     */
    protected function deleteUser(array $user)
    {
        $this->controlAccess('user_delete');

        $deleted = $this->user->delete($user['user_id']);

        if ($deleted) {
            $message = $this->text('User has been deleted');
            $this->redirect('admin/user/list', $message, 'success');
        }

        $message = $this->text('Unable to delete this user');
        $this->redirect('admin/user/list', $message, 'danger');
    }

    /**
     * Saves submitted user data
     * @param array $user
     */
    protected function submitUser(array $user)
    {
        if ($this->isPosted('delete')) {
            return $this->deleteUser($user);
        }

        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('user');
        $this->validateUser($user);

        if ($this->hasErrors('user')) {
            return;
        }

        if (isset($user['user_id'])) {
            return $this->updateUser($user);
        }

        $this->addUser();
    }

    /**
     * Updates a user with submitted values
     * @param array $user
     */
    protected function updateUser(array $user)
    {
        $this->controlAccess('user_edit');

        $values = $this->getSubmitted();
        $this->user->update($user['user_id'], $values);

        $message = $this->text('User %name has been updated', array(
            '%name' => $user['name']));

        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Adds a new user using an array of submitted values
     */
    protected function addUser()
    {
        $this->controlAccess('user_add');

        $values = $this->getSubmitted();
        $this->user->add($values);

        $message = $this->text('User has been added');
        $this->redirect('admin/user/list', $message, 'success');
    }

    /**
     * Validates submitted user data
     * @param array $user
     */
    protected function validateUser(array $user)
    {
        $this->addValidator('email', array(
            'required' => array(),
            'email' => array(),
            'user_email_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255),
            'user_name_unique' => array()
        ));

        $length = $this->user->getPasswordLength();
        $length += array('required' => empty($user['user_id']));

        $this->addValidator('password', array(
            'length' => $length
        ));

        $this->setValidators($user);

        if (isset($user['user_id']) && $this->isSuperadmin($user['user_id'])) {
            $this->setSubmitted('status', 1); //Superadmin is always enabled
        }
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEditUser(array $user)
    {
        if (isset($user['name'])) {
            $title = $this->text('Edit %user', array('%user' => $user['name']));
        } else {
            $title = $this->text('Add user');
        }

        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the edit user page
     */
    protected function setBreadcrumbEditUser()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin'));

        $breadcrumbs[] = array(
            'text' => $this->text('Users'),
            'url' => $this->url('admin/user/list'));

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEditUser()
    {
        $this->output('user/edit');
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function getUser($user_id)
    {
        if (!is_numeric($user_id)) {
            return array();
        }

        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->outputError(404);
        }

        return $user;
    }

    /**
     * Returns total number of users
     * @param array $query
     * @return integer
     */
    protected function getTotalUser(array $query)
    {
        $query['count'] = true;
        return $this->user->getList($query);
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
        $users = $this->user->getList($query);
        $stores = $this->store->getList();

        foreach ($users as &$user) {
            $user['url'] = '';
            if (isset($stores[$user['store_id']])) {
                $store = $stores[$user['store_id']];
                $user['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/")
                        . "/account/{$user['user_id']}";
            }
        }

        return $users;
    }

}
