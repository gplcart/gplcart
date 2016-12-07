<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model as Model;
use core\Logger as Logger;
use core\models\Mail as MailModel;
use core\models\Address as AddressModel;
use core\models\UserRole as UserRoleModel;
use core\models\Language as LanguageModel;
use core\helpers\Session as SessionHelper;
use core\exceptions\UserAccessException;

/**
 * Manages basic behaviors and data related to users
 */
class User extends Model
{

    /**
     * Address model instance
     * @var \core\models\Address $address;
     */
    protected $address;

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Mail model instance
     * @var \core\models\Mail $mail
     */
    protected $mail;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Session class instance
     * @var \core\helpers\Session $session
     */
    protected $session;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param AddressModel $address
     * @param UserRoleModel $role
     * @param MailModel $mail
     * @param LanguageModel $language
     * @param SessionHelper $session
     * @param Logger $logger
     */
    public function __construct(AddressModel $address, UserRoleModel $role,
            MailModel $mail, LanguageModel $language, SessionHelper $session,
            Logger $logger)
    {
        parent::__construct();

        $this->mail = $mail;
        $this->role = $role;
        $this->logger = $logger;
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
        $this->hook->fire('add.user.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data += array('hash' => gplcart_string_hash($data['password']));
        $data['user_id'] = $this->db->insert('user', $data);

        $this->setAddress($data);

        $this->hook->fire('add.user.after', $data);
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
        $this->hook->fire('update.user.before', $user_id, $data);

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
        $updated = (int) $this->db->update('user', $data, $options);
        $updated += (int) $this->setAddress($data);

        $result = ($updated > 0);

        $this->hook->fire('update.user.after', $user_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a user
     * @param integer $user_id
     * @return boolean
     */
    public function delete($user_id)
    {
        $this->hook->fire('delete.user.before', $user_id);

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

        $this->hook->fire('delete.user.after', $user_id, $deleted);
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
        if (isset($user_id)) {
            return ($this->superadmin() === (int) $user_id);
        }

        return ($this->superadmin() === $this->id());
    }

    /**
     * Returns superadmin user ID
     * @return integer
     */
    public function superadmin()
    {
        return (int) $this->config->get('user_superadmin', 1);
    }

    /**
     * Returns an ID of the current user
     * @return integer
     */
    public function id()
    {
        return (int) $this->session->get('user', 'user_id');
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

        $permissions = $this->permissions($user);
        return in_array($permission, $permissions);
    }

    /**
     * Returns user permissions
     * @param mixed $user
     * @return array
     */
    public function permissions($user = null)
    {
        $role_id = $this->roleId($user);

        if (empty($role_id)) {
            return array();
        }

        $role = $this->role->get($role_id);

        if (isset($role['permissions'])) {
            return (array) $role['permissions'];
        }

        return array();
    }

    /**
     * Returns a role ID
     * @param mixed $user
     * @return integer
     */
    public function roleId($user = null)
    {
        if (!isset($user)) {
            return (int) $this->session->get('user', 'role_id');
        }

        if (is_numeric($user)) {
            $user = $this->get($user);
        }

        return isset($user['role_id']) ? (int) $user['role_id'] : 0;
    }

    /**
     * Loads a user
     * @param integer $user_id
     * @param integer|null $store_id
     * @return array
     */
    public function get($user_id, $store_id = null)
    {
        $this->hook->fire('get.user.before', $user_id, $store_id);

        $sql = 'SELECT u.*, r.status AS role_status, r.name AS role_name'
                . ' FROM user u'
                . ' LEFT JOIN role r ON (u.role_id = r.role_id)'
                . ' WHERE u.user_id=?';

        $where = array($user_id);

        if (isset($store_id)) {
            $sql .= ' AND u.store_id=?';
            $where[] = $store_id;
        }

        $user = $this->db->fetch($sql, $where, array('unserialize' => 'data'));

        $this->hook->fire('get.user.after', $user);
        return $user;
    }

    /**
     * Logs in a user
     * @param array $data
     * @throws UserAccessException
     */
    public function login(array $data)
    {
        $result = array(
            'redirect' => null,
            'severity' => 'warning',
            'message' => $this->language->text('Invalid E-mail and/or password')
        );

        $this->hook->fire('login.before', $data, $result);

        if (empty($data['email']) || empty($data['password'])) {
            return $result;
        }

        $user = $this->getByEmail($data['email']);

        if (empty($user['status'])) {
            return $result;
        }

        $expected = gplcart_string_hash($data['password'], $user['hash'], false);

        if (!gplcart_string_equals($user['hash'], $expected)) {
            return $result;
        }

        if (!$this->session->regenerate(true)) {
            throw new UserAccessException('Failed to regenerate the current session');
        }

        unset($user['hash']);
        $this->session->set('user', null, $user);

        $this->logLogin($user);

        $result = array(
            'user' => $user,
            'message' => '',
            'severity' => 'success',
            'redirect' => $this->getLoginRedirect($user),
        );

        $this->hook->fire('login.after', $data, $result);
        return $result;
    }

    /**
     * Registers a user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $result = array('redirect' => null, 'severity' => '', 'message' => '');

        $this->hook->fire('register.user.before', $data, $result);

        if (empty($data)) {
            return $result;
        }

        $login = $this->config->get('user_registration_login', true);
        $status = $this->config->get('user_registration_status', true);

        $data += array('status' => $status, 'login' => $login);
        $data['user_id'] = $this->add($data);

        if (empty($data['user_id'])) {
            $result['severity'] = 'warning';
            $result['message'] = $this->language->text('An error occurred');
            return $result;
        }

        $this->logRegistration($data);
        $this->emailRegistration($data);

        $result = array(
            'redirect' => '/',
            'severity' => 'success',
            'message' => $this->language->text('Your account has been created'));

        if (!empty($data['login']) && !empty($data['status'])) {
            $result = $this->login($data);
        }

        $this->hook->fire('register.user.after', $data, $result);
        return $result;
    }

    /**
     * Sends E-mails to various recepients to inform them about the registration
     * @param array $data
     */
    protected function emailRegistration(array $data)
    {
        // Send an e-mail to the customer
        if ($this->config->get('user_registration_email_customer', true)) {
            $this->mail->set('user_registered_customer', array($data));
        }

        // Send an e-mail to admin
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
        $sql = 'SELECT * FROM user WHERE email=?';
        return $this->db->fetch($sql, array($email), array('unserialize' => 'data'));
    }

    /**
     * Loads a user by a name
     * @param string $name
     * @return array
     */
    public function getByName($name)
    {
        $sql = 'SELECT * FROM user WHERE name=?';
        return $this->db->fetch($sql, array($name), array('unserialize' => 'data'));
    }

    /**
     * Returns the current user
     * @return array
     */
    public function current()
    {
        return (array) $this->session->get('user', null, array());
    }

    /**
     * Logs out the current user
     * @return array
     * @throws UserAccessException
     */
    public function logout()
    {
        $user_id = $this->id();
        $result = array('message' => '', 'severity' => '', 'redirect' => '/');

        $this->hook->fire('logout.before', $user_id, $result);

        if (empty($user_id)) {
            return $result;
        }

        if (!$this->session->delete()) {
            throw new UserAccessException('Failed to delete the session on logout');
        }

        $user = $this->get($user_id);

        $this->logLogout($user);

        $result = array(
            'user' => $user,
            'message' => '',
            'severity' => 'success',
            'redirect' => $this->getLogOutRedirect($user),
        );

        $this->hook->fire('logout.after', $user_id, $result);
        return $result;
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

    /**
     * Performs reset password operation
     * @param array $data
     * @return array
     */
    public function resetPassword(array $data)
    {
        $result = array('redirect' => null, 'message' => '', 'severity' => '');

        $this->hook->fire('reset.password.before', $data, $result);

        if (empty($data['user']['user_id'])) {
            return $result;
        }

        if (isset($data['password'])) {
            $result = $this->setNewPassword($data['user'], $data['password']);
        } else {
            $result = $this->setResetPassword($data['user']);
        }

        $this->hook->fire('reset.password.after', $data, $result);
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

        $this->hook->fire('users', $list);
        return $list;
    }

    /**
     * Logs a login event
     * @param array $user
     */
    protected function logLogin(array $user)
    {
        $data = array(
            'message' => 'User %s has logged in',
            'variables' => array('%s' => $user['email'])
        );

        $this->logger->log('login', $data);
    }

    /**
     * Logs a logout event
     * @param array $user
     */
    protected function logLogout(array $user)
    {
        $data = array(
            'message' => 'User %email has logged out',
            'variables' => array('%email' => $user['email'])
        );

        $this->logger->log('logout', $data);
    }

    /**
     * Logs a registration event
     * @param array $user
     */
    protected function logRegistration(array $user)
    {
        $data = array(
            'message' => 'User %email has been registered',
            'variables' => array('%email' => $user['email'])
        );

        $this->logger->log('register', $data);
    }

    /**
     * Retuns a redirect path for logged in user
     * @param array $user
     * @return string
     */
    protected function getLoginRedirect(array $user)
    {
        if ($this->isSuperadmin($user['user_id'])) {
            return $this->config->get('user_login_redirect_superadmin', 'admin');
        }

        return $this->config->get("user_login_redirect_{$user['role_id']}", "account/{$user['user_id']}");
    }

    /**
     * Returns a redirect path for logged out users
     * @param array $user
     * @return string
     */
    protected function getLogOutRedirect(array $user)
    {
        if ($this->isSuperadmin($user['user_id'])) {
            return $this->config->get('user_logout_redirect_superadmin', 'login');
        }

        return $this->config->get("user_logout_redirect_{$user['role_id']}", 'login');
    }

}
