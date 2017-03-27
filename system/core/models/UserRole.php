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
use gplcart\core\helpers\Session as SessionHelper;

/**
 * Manages basic behaviors and data related to user roles
 */
class UserRole extends Model
{

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * Constructor
     * @param SessionHelper $session
     */
    public function __construct(SessionHelper $session)
    {
        parent::__construct();

        $this->session = $session;
    }

    /**
     * Returns an array of all permissions
     * @return array
     */
    public function getPermissions()
    {
        $permissions = &Cache::memory(__METHOD__);

        if (isset($permissions)) {
            return $permissions;
        }

        $permissions = $this->getDefaultPermissions();
        $this->hook->fire('user.role.permissions', $permissions);
        return $permissions;
    }

    /**
     * Returns an array of roles or counts them
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(role_id)';
        }

        $sql .= ' FROM role WHERE role_id > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'status', 'role_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY role_id ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array(
            'index' => 'role_id',
            'unserialize' => 'permissions'
        );

        $roles = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('user.role.list', $roles);
        return $roles;
    }

    /**
     * Deletes a role
     * @param integer $role_id
     * @return boolean
     */
    public function delete($role_id)
    {
        $this->hook->fire('user.role.delete.before', $role_id);

        if (empty($role_id)) {
            return false;
        }

        if (!$this->canDelete($role_id)) {
            return false;
        }

        $result = $this->db->delete('role', array('role_id' => $role_id));
        $this->hook->fire('user.role.delete.after', $role_id, $result);
        return (bool) $result;
    }

    /**
     * Whether the role canbe deleted
     * @param integer $role_id
     * @return boolean
     */
    public function canDelete($role_id)
    {
        $sql = 'SELECT user_id FROM user WHERE role_id=?';
        $result = $this->db->fetchColumn($sql, array($role_id));
        return empty($result);
    }

    /**
     * Adds a role to the database
     * @param array $data
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('user.role.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['role_id'] = $this->db->insert('role', $data);

        $this->hook->fire('user.role.add.after', $data);
        return $data['role_id'];
    }

    /**
     * Updates a role
     * @param integer $role_id
     * @param array $data
     * @return boolean
     */
    public function update($role_id, array $data)
    {
        $this->hook->fire('user.role.update.before', $role_id, $data);

        if (empty($role_id)) {
            return false;
        }

        $result = $this->db->update('role', $data, array('role_id' => $role_id));
        $this->hook->fire('user.role.update.after', $role_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Loads a role from the database
     * @param integer $role_id
     * @return array
     */
    public function get($role_id)
    {
        $role = &Cache::memory(__METHOD__ . $role_id);

        if (isset($role)) {
            return $role;
        }

        $this->hook->fire('user.role.get.before', $role_id);

        $sql = 'SELECT * FROM role WHERE role_id=?';
        $options = array('unserialize' => 'permissions');

        $role = $this->db->fetch($sql, array($role_id), $options);

        $this->hook->fire('user.role.get.after', $role);
        return $role;
    }

    /**
     * Returns an array of default permissions
     * @return array
     */
    protected function getDefaultPermissions()
    {
        $permissions = include GC_CONFIG_PERMISSION;
        asort($permissions);
        return $permissions;
    }

}
