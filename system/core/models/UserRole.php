<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;

/**
 * Manages basic behaviors and data related to user roles
 */
class UserRole
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
    }

    /**
     * Loads a role from the database
     * @param integer $role_id
     * @return array
     */
    public function get($role_id)
    {
        $result = &gplcart_static("user.role.get.$role_id");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('user.role.get.before', $role_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM role WHERE role_id=?';
        $result = $this->db->fetch($sql, array($role_id), array('unserialize' => 'permissions'));
        $this->hook->attach('user.role.get.after', $role_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of roles or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('user.role.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(role_id)';
        }

        $sql .= ' FROM role WHERE role_id IS NOT NULL';

        $conditions = array();

        if (isset($options['name'])) {
            $sql .= ' AND name LIKE ?';
            $conditions[] = "%{$options['name']}%";
        }

        if (isset($options['redirect'])) {
            $sql .= ' AND redirect = ?';
            $conditions[] = $options['redirect'];
        }

        if (isset($options['redirect_like'])) {
            $sql .= ' AND redirect LIKE ?';
            $conditions[] = "%{$options['redirect_like']}%";
        }

        if (isset($options['status'])) {
            $sql .= ' AND status = ?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'status', 'role_id', 'redirect');

        if (isset($options['sort']) && in_array($options['sort'], $allowed_sort)//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY role_id ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'role_id', 'unserialize' => 'permissions'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('user.role.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Deletes a role
     * @param integer $role_id
     * @param bool $check
     * @return boolean
     */
    public function delete($role_id, $check = true)
    {
        $result = null;
        $this->hook->attach('user.role.delete.before', $role_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($role_id)) {
            return false;
        }

        $result = $this->db->delete('role', array('role_id' => $role_id));
        $this->hook->attach('user.role.delete.after', $role_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the role can be deleted
     * @param integer $role_id
     * @return boolean
     */
    public function canDelete($role_id)
    {
        $result = $this->db->fetchColumn('SELECT user_id FROM user WHERE role_id=?', array($role_id));
        return empty($result);
    }

    /**
     * Adds a role to the database
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('user.role.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('role', $data);
        $this->hook->attach('user.role.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a role
     * @param integer $role_id
     * @param array $data
     * @return boolean
     */
    public function update($role_id, array $data)
    {
        $result = null;
        $this->hook->attach('user.role.update.before', $role_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('role', $data, array('role_id' => $role_id));
        $this->hook->attach('user.role.update.after', $role_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of permissions
     * @return array
     */
    public function getPermissions()
    {
        $permissions = &gplcart_static('user.role.permissions');

        if (isset($permissions)) {
            return $permissions;
        }

        $permissions = (array) gplcart_config_get(GC_FILE_CONFIG_PERMISSION);
        asort($permissions);

        $this->hook->attach('user.role.permissions', $permissions, $this);
        return $permissions;
    }

}
