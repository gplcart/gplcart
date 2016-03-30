<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\Logger;
use core\classes\Tool;
use core\classes\Session;
use core\models\Address;
use core\models\UserRole;
use core\exceptions\SystemLogicalUserAccess;

class User
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
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Config class instance
     * @var core\Config $config
     */
    protected $config;
    
    /**
     * logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;
    
    /**
     * Constructor
     * @param Address $address
     * @param Hook $hook
     * @param Session $session
     * @param UserRole $role
     * @param Logger $logger
     * @param Config $config
     */
    public function __construct(Address $address, Hook $hook, Session $session, UserRole $role, Logger $logger, Config $config)
    {
        $this->role = $role;
        $this->hook = $hook;
        $this->logger = $logger;
        $this->config = $config;
        $this->address = $address;
        $this->session = $session;
        $this->db = $this->config->db();
    }

    /**
     * Adds a user
     * @param array $data
     * @return integer A user ID
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
     * Returns a shipping address for a given user
     * @param integer $user_id
     * @return array

    public function getShippingAddress($user_id)
    {
        if ($user_id && is_numeric($user_id)) {
            $addresses = $this->getAddresses($user_id);

            if ($addresses) {
                return end($addresses);
            }

            return array();
        }

        $address = array(
            'country' => Tool::getCookie('country'),
            'state_id' => Tool::getCookie('state_id'),
            'city_id' => Tool::getCookie('city_id'),
            'postcode' => Tool::getCookie('postcode')
        );

        if (isset($address['country']) && isset($address['state_id']) && isset($address['city_id'])) {
            return $address;
        }

        return array();
    }
     * 
     */

    /**
     * Returns an array of saved addresses for a given user
     * @param integer $user_id
     * @return array

    public function getAddresses($user_id)
    {
        //TODO: cache??
        return $this->address->getList(array('user_id' => $user_id));
    }
     * 
     */

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

        $values = array('modified' => isset($data['modified']) ? $data['modified'] : GC_TIME);

        if (isset($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['created'])) {
            $values['created'] = $data['created'];
        }

        if (isset($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (isset($data['email'])) {
            $values['email'] = $data['email'];
        }

        if (isset($data['status'])) {
            $values['status'] = $data['status'];
        }

        if (!empty($data['password'])) { // not isset()!
            $values['hash'] = Tool::hash($data['password']);
        }

        if (isset($data['role_id'])) {
            $values['role_id'] = (int) $data['role_id'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

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
        $this->db->delete('bookmark', array('user_id' => $user_id));
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

        //if (is_numeric($user)) {
            //return $this->get($user);
        //}

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
     * @throws SystemLogicalUserAccess
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

        if(!$this->session->regenerate(true)) {
            throw new SystemLogicalUserAccess('Failed to regenerate the current session');
        }

        unset($user['hash']);
        $this->session->set('user', null, $user);
        
        $this->logLogin($user);
        
        $result = array(
            'user' => $user,
            'message' => null,
            'message_type' => 'success',
            'redirect' => $this->getLoginRedirect($user),
        );

        $this->hook->fire('login.after', $email, $password, $result);
        return $result;
    }
    
    protected function logLogin($user)
    {
        $this->logger->log('login', array(
            'message' => 'User %s has logged in',
            'variables' => array('%s' => $user['email'])
        ));
    }

    /**
     * Loads a user by an email
     * @param string $email
     * @return array
     */
    public function getByEmail($email)
    {
        $this->hook->fire('get.user.before', $email);

        $sth = $this->db->prepare('SELECT * FROM user WHERE email=:email');
        $sth->execute(array(':email' => $email));
        $user = $sth->fetch(PDO::FETCH_ASSOC);

        if (empty($user)) {
            return array();
        }

        $user['data'] = unserialize($user['data']);
        $this->hook->fire('get.user.after', $email, $user);
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
     * @throws SystemLogicalUserAccess
     */
    public function logout()
    {
        $uid = $this->id();
        $this->hook->fire('logout.before', $uid);

        if (empty($uid)) {
            return false;
        }

        if(!$this->session->delete()) {
            throw new SystemLogicalUserAccess('Failed to delete old session on logout');
        }

        $this->hook->fire('logout.after', $uid);
        return $uid;
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
     * Retuns an array of redirect parameters (path, message, severity) for logged in user
     * @param array $user
     * @return array
     */
    protected function getLoginRedirect(array $user)
    {
        if ($this->isSuperadmin($user['user_id'])) {
            return $this->config->get('user_login_redirect_superadmin', 'admin');
        }
        
        return $this->config->get("user_login_redirect_{$user['role_id']}", "account/{$user['user_id']}");
    }

}
