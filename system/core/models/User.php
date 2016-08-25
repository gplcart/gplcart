<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\Logger;
use core\classes\Tool;
use core\classes\Session;
use core\models\Mail as ModelsMail;
use core\models\Address as ModelsAddress;
use core\models\UserRole as ModelsUserRole;
use core\models\Language as ModelsLanguage;

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
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param ModelsAddress $address
     * @param ModelsUserRole $role
     * @param ModelsMail $mail
     * @param ModelsLanguage $language
     * @param Session $session
     * @param Logger $logger
     */
    public function __construct(ModelsAddress $address, ModelsUserRole $role,
            ModelsMail $mail, ModelsLanguage $language, Session $session,
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

        $values = array(
            'created' => empty($data['created']) ? GC_TIME : (int) $data['created'],
            'modified' => 0,
            'email' => $data['email'],
            'name' => $data['name'],
            'hash' => Tool::hash($data['password']),
            'data' => empty($data['data']) ? serialize(array()) : serialize((array) $data['data']),
            'status' => !empty($data['status']),
            'role_id' => isset($data['role_id']) ? (int) $data['role_id'] : 0,
            'store_id' => isset($data['store_id']) ? (int) $data['store_id'] : $this->config->get('store', 1),
        );

        $user_id = $this->db->insert('user', $values);

        if (!empty($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                $address['user_id'] = $user_id;
                $this->address->add($address);
            }
        }

        $this->hook->fire('add.user.after', $data, $user_id);
        return $user_id;
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
        
        if (!empty($data['password'])) { // not isset()!
            $data['hash'] = Tool::hash($data['password']);
        }
        
        $data += array('modified' => GC_TIME);
        $values = $this->getDbSchemeValues('user', $data);

        if (isset($data['addresses'])) {
            foreach ((array) $data['addresses'] as $address) {
                $this->setAddress($user_id, $address);
            }
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('user', $values, array('user_id' => $user_id));
            $this->hook->fire('update.user.after', $user_id, $values, $result);
        }

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

        $this->db->delete('user', array('user_id' => (int) $user_id));
        $this->db->delete('cart', array('user_id' => $user_id));
        $this->db->delete('wishlist', array('user_id' => $user_id));
        $this->db->delete('review', array('user_id' => $user_id));
        $this->db->delete('address', array('user_id' => $user_id));
        $this->db->delete('rating_user', array('user_id' => $user_id));

        $this->hook->fire('delete.user.after', $user_id);
        return true;
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

        $sql = 'SELECT * FROM orders WHERE user_id=:user_id';
        $sth = $this->db->prepare($sql);
        $sth->execute(array(':user_id' => (int) $user_id));

        return !$sth->fetchColumn();
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

        $sql = 'SELECT u.*, r.status AS role_status, r.name AS role_name
                FROM user u
                LEFT JOIN role r ON (u.role_id = r.role_id)
                WHERE u.user_id=:user_id';

        $where = array(':user_id' => (int) $user_id);

        if (isset($store_id)) {
            $sql .= ' AND u.store_id=:store_id';
            $where[':store_id'] = (int) $store_id;
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        $user = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($user)) {
            $user['data'] = unserialize($user['data']);
        }

        $this->hook->fire('get.user.after', $user_id, $user);
        return $user;
    }

    /**
     * Logs in a user
     * @param string $email
     * @param string $password
     * @return mixed
     * @throws \core\exceptions\SystemLogicalUserAccess
     */
    public function login($email, $password)
    {
        $this->hook->fire('login.before', $email, $password);

        if (empty($email)) {
            return false;
        }

        $user = $this->getByEmail($email);

        if (empty($user['status'])) {
            return false;
        }

        if (!Tool::hashEquals($user['hash'], Tool::hash($password, $user['hash'], false))) {
            return false;
        }

        if (!$this->session->regenerate(true)) {
            throw new \core\exceptions\SystemLogicalUserAccess('Failed to regenerate the current session');
        }

        unset($user['hash']);
        $this->session->set('user', null, $user);

        $this->logLogin($user);

        $result = array(
            'user' => $user,
            'message' => '',
            'message_type' => 'success',
            'redirect' => $this->getLoginRedirect($user),
        );

        $this->hook->fire('login.after', $email, $password, $result);
        return $result;
    }

    /**
     * Registers a user
     * @param array $data
     * @return array
     */
    public function register(array $data)
    {
        $this->hook->fire('register.user.before', $data);

        $data['user_id'] = $this->add($data);

        if (!empty($data['admin'])) {

            if (!empty($data['notify'])) {
                $this->mail->set('user_registered_customer', array($data));
            }

            $result = array(
                'redirect' => 'admin/user',
                'message_type' => 'success',
                'message' => $this->language->text('User has been added')
            );

            $this->hook->fire('register.user.after', $data, $result);
            return $result;
        }

        $this->logRegistration($data);

        // Send an e-mail to the customer
        if ($this->config->get('user_registration_email_customer', true)) {
            $this->mail->set('user_registered_customer', array($data));
        }

        // Send an e-mail to admin
        if ($this->config->get('user_registration_email_admin', true)) {
            $this->mail->set('user_registered_admin', array($data));
        }

        if (!$this->config->get('user_registration_login', true) || !$this->config->get('user_registration_status', true)) {
            $result = array(
                'redirect' => '/',
                'message_type' => 'success',
                'message' => $this->language->text('Your account has been created'));


            $this->hook->fire('register.user.after', $data, $result);
            return $result;
        }

        $result = $this->login($data['email'], $data['password']);
        $this->hook->fire('register.user.after', $data, $result);
        return $result;
    }

    /**
     * Loads a user by an email
     * @param string $email
     * @return array
     */
    public function getByEmail($email)
    {

        $sth = $this->db->prepare('SELECT * FROM user WHERE email=:email');
        $sth->execute(array(':email' => $email));
        $user = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($user)) {
            return array();
        }

        $user['data'] = unserialize($user['data']);
        return $user;
    }

    /**
     * Loads a user by a name
     * @param string $name
     * @return array
     */
    public function getByName($name)
    {
        $sth = $this->db->prepare('SELECT * FROM user WHERE name=:name');
        $sth->execute(array(':name' => $name));
        $user = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($user)) {
            return array();
        }

        $user['data'] = unserialize($user['data']);
        return $user;
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
     * @return mixed
     * @throws \core\exceptions\SystemLogicalUserAccess
     */
    public function logout()
    {
        $user_id = $this->id();
        $this->hook->fire('logout.before', $user_id);

        if (empty($user_id)) {
            return false;
        }

        if (!$this->session->delete()) {
            throw new \core\exceptions\SystemLogicalUserAccess('Failed to delete the session on logout');
        }

        $user = $this->get($user_id);

        $this->logLogout($user);

        $result = array(
            'user' => $user,
            'message' => '',
            'message_type' => 'success',
            'redirect' => $this->getLogOutRedirect($user),
        );

        $this->hook->fire('logout.after', $result);
        return $result;
    }

    /**
     * Generates a random password
     * @return string
     */
    public function generatePassword()
    {
        $hash = crypt(Tool::randomString(), Tool::randomString());
        return str_replace(array('+', '/', '='), '', base64_encode($hash));
    }

    /**
     * Performs reset password operation
     * @param array $user
     * @param string $password
     * @return array
     */
    public function resetPassword(array $user, $password = null)
    {
        $this->hook->fire('reset.password.before', $user, $password);

        if (isset($password)) {
            $result = $this->setNewPassword($user, $password);
        } else {
            $result = $this->setResetPassword($user);
        }

        $this->hook->fire('reset.password.after', $user, $password, $result);
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
            'token' => Tool::randomString(),
            'expires' => GC_TIME + $lifetime,
        );

        $this->update($user['user_id'], array('data' => $user['data']));
        $this->mail->set('user_reset_password', array($user));

        return array(
            'redirect' => 'forgot',
            'message_type' => 'success',
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

        return array(
            'redirect' => 'login',
            'message_type' => 'success',
            'message' => $this->language->text('Your password has been successfully changed')
        );
    }

    /**
     * Returns allowed min and max password length
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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $order = $data['order'];

            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY name $order";
                    break;
                case 'email':
                    $sql .= " ORDER BY email $order";
                    break;
                case 'role_id':
                    $sql .= " ORDER BY role_id $order";
                    break;
                case 'store_id':
                    $sql .= " ORDER BY store_id $order";
                    break;
                case 'status':
                    $sql .= " ORDER BY status $order";
                    break;
                case 'created':
                    $sql .= " ORDER BY created $order";
                    break;
            }
        } else {
            $sql .= " ORDER BY created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $user) {
            $list[$user['user_id']] = $user;
        }

        $this->hook->fire('users', $list);
        return $list;
    }

    /**
     * Adds/updates an address for a given user
     * @param integer $user_id
     * @param array $address
     * @return bool
     */
    protected function setAddress($user_id, array $address)
    {
        if (empty($address['address_id'])) {
            $address['user_id'] = $user_id;
            return (bool) $this->address->add($address);
        }

        return (bool) $this->address->update($address['address_id'], $address);
    }

    /**
     * Logs a login event
     * @param array $user
     */
    protected function logLogin(array $user)
    {
        $this->logger->log('login', array(
            'message' => 'User %s has logged in',
            'variables' => array('%s' => $user['email'])
        ));
    }

    /**
     * Logs a logout event
     * @param array $user
     */
    protected function logLogout(array $user)
    {
        $this->logger->log('logout', array(
            'message' => 'User %email has logged out',
            'variables' => array('%email' => $user['email'])
        ));
    }

    /**
     * Logs a registration event
     * @param array $user
     */
    protected function logRegistration(array $user)
    {
        $this->logger->log('register', array(
            'message' => 'User %email has been registered',
            'variables' => array('%email' => $user['email'])
        ));
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
