<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Mail as MailModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\UserRole as UserRoleModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\helpers\Session as SessionHelper;

/**
 * Manages basic behaviors and data related to users
 */
class User extends Model
{

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address;
     */
    protected $address;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Mail model instance
     * @var \gplcart\core\models\Mail $mail
     */
    protected $mail;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param AddressModel $address
     * @param UserRoleModel $role
     * @param MailModel $mail
     * @param LanguageModel $language
     * @param SessionHelper $session
     */
    public function __construct(AddressModel $address, UserRoleModel $role,
            MailModel $mail, LanguageModel $language, SessionHelper $session)
    {
        parent::__construct();

        $this->mail = $mail;
        $this->role = $role;
        $this->address = $address;
        $this->session = $session;
        $this->language = $language;
    }

    /**
     * Adds a user
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('user.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        if (empty($data['name'])) {
            $data['name'] = strtok($data['email'], '@');
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $data['hash'] = gplcart_string_hash($data['password']);
        $result = $data['user_id'] = $this->db->insert('user', $data);

        $this->setAddress($data);

        $this->hook->attach('user.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Adds/updates addresses for the user
     * @param array $data
     * @return boolean
     */
    protected function setAddress(array $data)
    {
        if (empty($data['addresses'])) {
            return false;
        }

        foreach ($data['addresses'] as $address) {

            if (empty($address['address_id'])) {
                $address['user_id'] = $data['user_id'];
                $this->address->add($address);
                continue;
            }

            $this->address->update($address['address_id'], $address);
        }

        return true;
    }

    /**
     * Updates a user
     * @param integer $user_id
     * @param array $data
     * @return boolean     *
     */
    public function update($user_id, array $data)
    {
        $result = null;
        $this->hook->attach('user.update.before', $user_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;

        if (!empty($data['password'])) {
            $data['hash'] = gplcart_string_hash($data['password']);
        }

        if ($this->isSuperadmin($user_id)) {
            $data['status'] = 1;
        }

        $updated = $this->db->update('user', $data, array('user_id' => $user_id));

        $data['user_id'] = $user_id;

        $updated += (int) $this->setAddress($data);

        $result = $updated > 0;

        $this->hook->attach('user.update.after', $user_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a user
     * @param integer $user_id
     * @return boolean
     */
    public function delete($user_id)
    {
        $result = null;
        $this->hook->attach('user.delete.before', $user_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($user_id)) {
            return false;
        }

        $conditions = array('user_id' => $user_id);
        $result = (bool) $this->db->delete('user', $conditions);

        if ($result) {
            $this->db->delete('cart', $conditions);
            $this->db->delete('review', $conditions);
            $this->db->delete('history', $conditions);
            $this->db->delete('address', $conditions);
            $this->db->delete('wishlist', $conditions);
            $this->db->delete('rating_user', $conditions);
            $this->db->delete('dashboard', $conditions);
        }

        $this->hook->attach('user.delete.after', $user_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the user can be deleted
     * @param integer $user_id
     * @return boolean
     */
    public function canDelete($user_id)
    {
        if ($this->isSuperadmin($user_id)) {
            return false;
        }

        $sql = 'SELECT * FROM orders WHERE user_id=?';
        $result = $this->db->fetchColumn($sql, array($user_id));

        return empty($result);
    }

    /**
     * Whether the user is superadmin
     * @param integer|null $user_id
     * @return boolean
     */
    public function isSuperadmin($user_id = null)
    {
        $superadmin_id = $this->config->get('user_superadmin', 1);

        if (isset($user_id)) {
            return $superadmin_id == $user_id;
        }

        return $superadmin_id == $this->getId();
    }

    /**
     * Returns the current user ID from the session
     * @return integer
     */
    public function getId()
    {
        return (int) $this->getSession('user_id');
    }

    /**
     * Returns the current user role ID from the session
     * @return integer
     */
    public function getRoleId()
    {
        return (int) $this->getSession('role_id');
    }

    /**
     * Whether a user has an access to do something
     * @param string $permission
     * @param mixed $user
     * @return boolean
     */
    public function access($permission, $user = null)
    {
        if ($this->isSuperadmin($user)) {
            return true;
        }

        if ($permission === '__superadmin') {
            return false;
        }

        $permissions = $this->getPermissions($user);
        return in_array($permission, $permissions);
    }

    /**
     * Returns user permissions
     * @param mixed $user
     * @return array
     */
    public function getPermissions($user = null)
    {
        if (!isset($user)) {
            $user = $this->getId();
        }

        if (is_numeric($user)) {
            // User is already loaded and cached in memory
            // so no additional database query needed
            $user = $this->get($user);
        }

        if (empty($user['role_id'])) {
            return array();
        }

        $role = array();
        if (isset($user['role_permissions'])) {
            $role['permissions'] = $user['role_permissions'];
        } else {
            $role = $this->role->get($user['role_id']);
        }

        if (empty($role['permissions'])) {
            return array();
        }

        return (array) $role['permissions'];
    }

    /**
     * Loads a user
     * @param integer $user_id
     * @param integer|null $store_id
     * @return array
     */
    public function get($user_id, $store_id = null)
    {
        $result = &gplcart_static(__METHOD__ . $user_id);

        if (isset($result)) {
            return (array) $result;
        }

        $this->hook->attach('user.get.before', $user_id, $store_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (empty($user_id)) {
            // This is also prevents fatal errors when db is unavailable
            return $result = array();
        }

        $sql = 'SELECT u.*, r.status AS role_status, r.name AS role_name, r.permissions AS role_permissions'
                . ' FROM user u'
                . ' LEFT JOIN role r ON (u.role_id = r.role_id)'
                . ' WHERE u.user_id=?';

        $where = array($user_id);

        if (isset($store_id)) {
            $sql .= ' AND u.store_id=?';
            $where[] = $store_id;
        }

        $options = array('unserialize' => array('data', 'role_permissions'));
        $result = $this->db->fetch($sql, $where, $options);

        $this->hook->attach('user.get.after', $user_id, $store_id, $result, $this);
        return $result;
    }

    /**
     * Logs in a user
     * @param array $data
     * @param bool $check_password
     * @return array
     */
    public function login(array $data, $check_password = true)
    {
        $result = array();
        $this->hook->attach('user.login.before', $data, $check_password, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => null,
            'severity' => 'warning',
            'message' => $this->language->text('Failed to log in')
        );

        if (empty($data['email'])) {
            return $result;
        }

        $user = $this->getByEmail($data['email']);

        if (empty($user['status'])) {
            return $result;
        }

        if ($check_password) {
            $expected = gplcart_string_hash($data['password'], $user['hash'], 0);
            if (!gplcart_string_equals($user['hash'], $expected)) {
                return $result;
            }
        }

        $this->session->regenerate(true);
        $this->session->set('user', $user);

        $redirect = "account/{$user['user_id']}";

        if (!empty($user['role_redirect'])) {
            $redirect = $user['role_redirect'];
        }

        if ($this->isSuperadmin($user['user_id'])) {
            $redirect = 'admin';
        }

        $result = array(
            'user' => $user,
            'message' => '',
            'severity' => 'success',
            'redirect' => $redirect,
        );

        $this->hook->attach('user.login.after', $data, $check_password, $result, $this);
        return (array) $result;
    }

    /**
     * Registers a user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $result = array();
        $this->hook->attach('user.register.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'message' => '',
            'severity' => '',
            'redirect' => null
        );

        // Extra security. Remove all but allowed keys
        $allowed = array('name', 'email', 'password', 'store_id');
        $data = array_intersect_key($data, array_flip($allowed));

        $data['login'] = $this->config->get('user_registration_login', true);
        $data['status'] = $this->config->get('user_registration_status', true);
        $data['user_id'] = $this->add($data);

        if (empty($data['user_id'])) {
            $result['severity'] = 'warning';
            $result['message'] = $this->language->text('An error occurred');
            return $result;
        }

        $this->emailRegistration($data);

        $result = array(
            'redirect' => '/',
            'severity' => 'success',
            'user_id' => $data['user_id'],
            'message' => $this->language->text('Your account has been created'));

        $this->session->regenerate(true);

        if (!empty($data['login']) && !empty($data['status'])) {
            $result = $this->login($data);
        }

        $this->hook->attach('user.register.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Sends e-mails on registration event
     * @param array $data
     */
    protected function emailRegistration(array $data)
    {
        if ($this->config->get('user_registration_email_customer', true)) {
            $this->mail->set('user_registered_customer', array($data));
        }

        if ($this->config->get('user_registration_email_admin', true)) {
            $this->mail->set('user_registered_admin', array($data));
        }
    }

    /**
     * Loads a user by an email
     * @param string $email
     * @return array
     */
    public function getByEmail($email)
    {
        $sql = 'SELECT u.*, r.redirect AS role_redirect, r.status AS role_status'
                . ' FROM user u'
                . ' LEFT JOIN role r ON(u.role_id=r.role_id)'
                . ' WHERE u.email=?';

        return $this->db->fetch($sql, array($email), array('unserialize' => 'data'));
    }

    /**
     * Returns the current user from the session
     * @return mixed
     */
    public function getSession($key = null)
    {
        $user = $this->session->get('user', array());

        if (isset($key)) {
            return gplcart_array_get($user, $key);
        }

        return $user;
    }

    /**
     * Logs out the current user
     * @return array
     */
    public function logout()
    {
        $user_id = $this->getId();

        $result = array();
        $this->hook->attach('user.logout.before', $user_id, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (empty($user_id)) {
            return array('message' => '', 'severity' => '', 'redirect' => '/');
        }

        $this->session->delete();

        $user = $this->get($user_id);

        $result = array(
            'user' => $user,
            'message' => '',
            'redirect' => 'login',
            'severity' => 'success'
        );

        $this->hook->attach('user.logout.after', $user_id, $result, $this);
        return (array) $result;
    }

    /**
     * Performs reset password operation
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data)
    {
        $result = array();
        $this->hook->attach('user.reset.password.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        if (empty($data['user']['user_id'])) {
            return array('message' => '', 'severity' => '', 'redirect' => null);
        }

        if (isset($data['password'])) {
            $result = $this->resetPasswordFinish($data['user'], $data['password']);
        } else {
            $result = $this->resetPasswordStart($data['user']);
        }

        $this->session->regenerate(true);

        $this->hook->attach('user.reset.password.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Start password reset operation
     * @param array $user
     * @return array
     */
    protected function resetPasswordStart(array $user)
    {
        $lifetime = (int) $this->config->get('user_reset_password_lifespan', 86400);

        $user['data']['reset_password'] = array(
            'token' => gplcart_string_random(),
            'expires' => GC_TIME + $lifetime,
        );

        $this->update($user['user_id'], array('data' => $user['data']));
        $this->mail->set('user_reset_password', array($user));

        return array(
            'redirect' => 'forgot',
            'severity' => 'success',
            'message' => $this->language->text('Password reset link has been sent to your E-mail')
        );
    }

    /**
     * Finish password reset operation
     * @param array $user
     * @param string $password
     * @return array
     */
    protected function resetPasswordFinish(array $user, $password)
    {
        $user['password'] = $password;
        unset($user['data']['reset_password']);

        $this->update($user['user_id'], $user);
        $this->mail->set('user_changed_password', array($user));

        return array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->language->text('Your password has been successfully changed')
        );
    }

    /**
     * Returns an array of users or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(user_id)';
        }

        $sql .= ' FROM user WHERE user_id > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['email'])) {
            $sql .= ' AND email LIKE ?';
            $where[] = "%{$data['email']}%";
        }

        if (isset($data['role_id'])) {
            $sql .= ' AND role_id = ?';
            $where[] = (int) $data['role_id'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'email', 'role_id',
            'store_id', 'status', 'created', 'user_id');

        if (isset($data['sort'])//
                && in_array($data['sort'], $allowed_sort)//
                && isset($data['order'])//
                && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'user_id', 'unserialize' => 'data');
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->attach('user.list', $list, $this);
        return $list;
    }

    /**
     * Returns min and max allowed password length
     * @return array
     */
    public function getPasswordLength()
    {
        return array(
            'min' => $this->config->get('user_password_min_length', 8),
            'max' => $this->config->get('user_password_max_length', 255)
        );
    }

    /**
     * Generates a random password
     * @return string
     */
    public function generatePassword()
    {
        $hash = crypt(gplcart_string_random(), gplcart_string_random());
        return gplcart_string_encode($hash);
    }

}
