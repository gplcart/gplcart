<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
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
     * Constructor
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
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('user.add.before', $data);

        if (empty($data)) {
            return false;
        }

        if (empty($data['name'])) {
            $data['name'] = strtok($data['email'], '@');
        }

        $data['created'] = GC_TIME;
        $data += array('hash' => gplcart_string_hash($data['password']));
        $data['user_id'] = $this->db->insert('user', $data);

        $this->setAddress($data);

        $this->hook->fire('user.add.after', $data);
        return $data['user_id'];
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
        $this->hook->fire('user.update.before', $user_id, $data);

        if (empty($user_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;
        $data += array('user_id' => $user_id);

        if (!empty($data['password'])) {
            $data['hash'] = gplcart_string_hash($data['password']);
        }

        if ($this->isSuperadmin($user_id)) {
            $data['status'] = 1;
        }

        $options = array('user_id' => $user_id);
        $updated = $this->db->update('user', $data, $options);
        $updated += (int) $this->setAddress($data);

        $result = ($updated > 0);

        $this->hook->fire('user.update.after', $user_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a user
     * @param integer $user_id
     * @return boolean
     */
    public function delete($user_id)
    {
        $this->hook->fire('user.delete.before', $user_id);

        if (empty($user_id)) {
            return false;
        }

        if (!$this->canDelete($user_id)) {
            return false;
        }

        $conditions = array('user_id' => $user_id);
        $deleted = (bool) $this->db->delete('user', $conditions);

        if ($deleted) {
            $this->db->delete('cart', $conditions);
            $this->db->delete('review', $conditions);
            $this->db->delete('history', $conditions);
            $this->db->delete('address', $conditions);
            $this->db->delete('wishlist', $conditions);
            $this->db->delete('rating_user', $conditions);
        }

        $this->hook->fire('user.delete.after', $user_id, $deleted);
        return (bool) $deleted;
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
        $superadmin_id = (int) $this->config->get('user_superadmin', 1);

        if (isset($user_id)) {
            return ($superadmin_id === (int) $user_id);
        }

        return ($superadmin_id === (int) $this->getSession('user_id'));
    }

    /**
     * Whether the user has an access
     * @param string $permission
     * @param mixed $user
     * @return boolean
     */
    public function access($permission, $user = null)
    {
        if ($this->isSuperadmin($user)) {
            return true;
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
            $user = $this->getSession('user_id');
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
        $user = &Cache::memory(__METHOD__ . $user_id);

        if (isset($user)) {
            return $user;
        }

        $this->hook->fire('user.get.before', $user_id, $store_id);

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
        $user = $this->db->fetch($sql, $where, $options);

        $this->hook->fire('user.get.after', $user);
        return $user;
    }

    /**
     * Logs in a user
     * @param array $data
     * @param bool $check_password
     * @return string
     */
    public function login(array $data, $check_password = true)
    {
        $result = array(
            'redirect' => null,
            'severity' => 'warning',
            'message' => $this->language->text('Failed to log in')
        );

        $this->hook->fire('user.login.before', $data, $check_password, $result);

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

        $this->hook->fire('user.login.after', $data, $check_password, $result);
        return $result;
    }

    /**
     * Registers a user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $result = array(
            'message' => '',
            'severity' => '',
            'redirect' => null
        );

        $this->hook->fire('user.register.before', $data, $result);

        if (empty($data)) {
            return $result;
        }

        $data += array(
            'login' => $this->config->get('user_registration_login', true),
            'status' => $this->config->get('user_registration_status', true)
        );

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
            'message' => $this->language->text('Your account has been created'));

        if (!empty($data['login']) && !empty($data['status'])) {
            $result = $this->login($data);
        }

        $this->hook->fire('user.register.after', $data, $result);
        return $result;
    }

    /**
     * Sends E-mails to various recepients to inform them about the registration
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

        if (!isset($key)) {
            return $user;
        }

        return gplcart_array_get_value($user, $key);
    }

    /**
     * Logs out the current user
     * @return array
     */
    public function logout()
    {
        $user_id = (int) $this->getSession('user_id');

        $result = array(
            'message' => '',
            'severity' => '',
            'redirect' => '/'
        );

        $this->hook->fire('user.logout.before', $user_id, $result);

        if (empty($user_id)) {
            return $result;
        }

        $this->session->delete();

        $user = $this->get($user_id);

        $result = array(
            'user' => $user,
            'message' => '',
            'redirect' => 'login',
            'severity' => 'success'
        );

        $this->hook->fire('user.logout.after', $user_id, $result);
        return $result;
    }

    /**
     * Performs reset password operation
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data)
    {
        $result = array(
            'message' => '',
            'severity' => '',
            'redirect' => null
        );

        $this->hook->fire('user.reset.password.before', $data, $result);

        if (empty($data['user']['user_id'])) {
            return $result;
        }

        if (isset($data['password'])) {
            $result = $this->setNewPassword($data['user'], $data['password']);
        } else {
            $result = $this->setResetPassword($data['user']);
        }

        $this->hook->fire('user.reset.password.after', $data, $result);
        return $result;
    }

    /**
     * Sets reset token and sends reset link
     * @param array $user
     * @return array
     */
    protected function setResetPassword(array $user)
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
     * Sets a new password
     * @param array $user
     * @param string $password
     * @return array
     */
    protected function setNewPassword(array $user, $password)
    {
        $user['password'] = $password;
        unset($user['data']['reset_password']);

        $this->update($user['user_id'], $user);
        $this->mail->set('user_changed_password', array($user));

        $result = array(
            'redirect' => 'login',
            'severity' => 'success',
            'message' => $this->language->text('Your password has been successfully changed')
        );

        return $result;
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
            $sql .= " ORDER BY created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'user_id', 'unserialize' => 'data');
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('user.list', $list);
        return $list;
    }

    /**
     * Returns allowed min and max password length
     * @return array
     */
    public function getPasswordLength()
    {
        $data = array(
            'min' => $this->config->get('user_password_min_length', 8),
            'max' => $this->config->get('user_password_max_length', 255)
        );

        return $data;
    }

    /**
     * Generates a random password
     * @return string
     */
    public function generatePassword()
    {
        $hash = crypt(gplcart_string_random(), gplcart_string_random());
        return str_replace(array('+', '/', '='), '', base64_encode($hash));
    }

}
