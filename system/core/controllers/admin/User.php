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
 * Handles incoming requests and outputs data related to user management
 */
class User extends Controller
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
    public function users()
    {
        $value = (int) $this->request->post('value');
        $action = (string) $this->request->post('action');
        $selected = (array) $this->request->post('selected', array());

        if (!empty($action)) {
            $this->action($selected, $action, $value);
        }

        $query = $this->getFilterQuery();
        $total = $this->setPager($this->getTotalUsers($query), $query);

        $this->data['users'] = $this->getUsers($total, $query);
        $this->data['superadmin'] = $this->user->superadmin();
        $this->data['stores'] = $this->store->getNames();
        $this->data['roles'] = $this->role->getList();

        $filters = array('name', 'email', 'role_id', 'store_id', 'status', 'created');
        $this->setFilter($filters, $query);

        $this->setTitleUsers();
        $this->setBreadcrumbUsers();
        $this->outputUsers();
    }

    /**
     * Renders the users overview page
     */
    protected function outputUsers()
    {
        $this->output('user/list');
    }

    /**
     * Sets breadcrumbs on the users overview page
     */
    protected function setBreadcrumbUsers()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Sets titles on the users overview page
     */
    protected function setTitleUsers()
    {
        $this->setTitle($this->text('Users'));
    }

    /**
     * Applies an action to users
     * @param array $selected
     * @param string $action
     * @param string $value
     */
    protected function action(array $selected, $action, $value)
    {
        $deleted = $updated = 0;
        foreach ($selected as $uid) {

            if ($this->isSuperadmin($uid)) {
                continue;
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
    public function edit($user_id = null)
    {
        $user = $this->get($user_id);

        // Only superadmin can edit its own account
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputError(403);
        }

        $this->data['user'] = $user;
        $this->data['roles'] = $this->role->getList();
        $this->data['stores'] = $this->store->getNames();
        $this->data['is_superadmin'] = (isset($user['user_id']) && $this->isSuperadmin($user['user_id']));
        $this->data['can_delete'] = (isset($user['user_id']) && $this->user->canDelete($user['user_id']));

        if ($this->request->post('save')) {
            $this->submit($user);
        }

        if ($this->request->post('delete')) {
            $this->delete($user);
        }

        $this->setTitleEdit($user);
        $this->setBreadcrumbEdit();
        $this->outputEdit();
    }

    /**
     * Deletes a user
     * @param array $user
     */
    protected function delete(array $user)
    {
        $this->controlAccess('user_delete');

        if ($this->user->delete($user['user_id'])) {
            $this->redirect('admin/user', $this->text('User has been deleted'), 'success');
        }
    }

    /**
     * Saves submitted user data
     * @param array $user
     * @return null
     */
    protected function submit(array $user)
    {
        $this->submitted = $this->request->post('user', array());
        $this->validate($user);

        $errors = $this->getErrors();

        if (!empty($errors)) {
            $this->data['user'] = $this->submitted;
            return;
        }

        if (isset($user['user_id'])) {
            $this->controlAccess('user_edit');
            $this->user->update($user['user_id'], $this->submitted);
            $this->redirect('admin/user', $this->text('User %name has been updated', array('%name' => $user['name'])), 'success');
        }

        $this->controlAccess('user_add');
        $this->user->add($this->submitted);
        $this->redirect('admin/user', $this->text('User has been added'), 'success');
    }

    /**
     * Validates submitted user data
     * @param array $user
     */
    protected function validate(array $user)
    {
        $this->validateEmail($user);
        $this->validateName($user);
        $this->validatePassword($user);

        if (isset($user['user_id']) && $this->isSuperadmin($user['user_id'])) {
            $this->submitted['status'] = 1; //Superadmin is always enabled
        }
    }

    /**
     * Validates submitted user E-mail
     * @param array $user
     * @return boolean
     */
    protected function validateEmail(array $user)
    {
        if (empty($this->submitted['email'])) {
            $this->errors['email'] = $this->text('Required field');
            return false;
        }

        if (!filter_var($this->submitted['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = $this->text('Invalid E-mail');
            return false;
        }

        $check_email_exists = true;
        if (isset($user['email']) && ($this->submitted['email'] === $user['email'])) {
            $check_email_exists = false;
        }

        if ($check_email_exists && $this->user->getByEmail($this->submitted['email'])) {
            $this->errors['email'] = $this->text('Please provide another E-mail');
            return false;
        }

        return true;
    }

    /**
     * Validates submitted user name
     * @param array $user
     * @return boolean
     */
    protected function validateName(array $user)
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->errors['name'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => 1, '%max' => 255));
            return false;
        }

        $check_name_exists = true;
        if (isset($user['name']) && ($this->submitted['name'] === $user['name'])) {
            $check_name_exists = false;
        }

        if ($check_name_exists && $this->user->getByName($this->submitted['name'])) {
            $this->errors['name'] = $this->text('Please provide another name');
            return false;
        }

        return true;
    }

    /**
     * Validates submitted user password
     * @param array $user
     * @return boolean
     */
    protected function validatePassword(array $user)
    {
        $limits = $this->user->getPasswordLength();

        if (empty($this->submitted['password'])) {
            if (empty($user['user_id'])) {
                // Adding a new user so password is required
                $this->errors['password'] = $this->language->text('Password must be %min - %max characters long', array(
                    '%min' => $limits['min'], '%max' => $limits['max']));
                return false;
            }

            return true;
        }

        $length = mb_strlen($this->submitted['password']);

        if (($limits['min'] <= $length) && ($length <= $limits['max'])) {
            return true;
        }

        $this->errors['password'] = $this->language->text('Password must be %min - %max characters long', array(
            '%min' => $limits['min'], '%max' => $limits['max']));

        return false;
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEdit(array $user)
    {
        if (isset($user['name'])) {
            $this->setTitle($this->text('Edit %user', array('%user' => $user['name'])));
        } else {
            $this->setTitle($this->text('Add user'));
        }
    }

    /**
     * Sets breadcrumbs on the edit user page
     */
    protected function setBreadcrumbEdit()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Users'), 'url' => $this->url('admin/user')));
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEdit()
    {
        $this->output('user/edit');
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function get($user_id)
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
     * Returns total numbers of users
     * @param array $query
     * @return integer
     */
    protected function getTotalUsers(array $query)
    {
        return $this->user->getList(array('count' => true) + $query);
    }

    /**
     * Returns an array of users
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getUsers(array $limit, array $query)
    {
        $stores = $this->store->getList();
        $users = $this->user->getList(array('limit' => $limit) + $query);

        foreach ($users as &$user) {
            $user['url'] = '';
            if (isset($stores[$user['store_id']])) {
                $store = $stores[$user['store_id']];
                $user['url'] = rtrim("{$this->scheme}{$store['domain']}/{$store['basepath']}", "/") . "/account/{$user['user_id']}";
            }
        }

        return $users;
    }

}
