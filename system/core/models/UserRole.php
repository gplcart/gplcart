<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to user roles
 */
class UserRole extends Model
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
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

    /**
     * Returns an array of roles or counts them
     * @param array $data
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

        $this->hook->attach('user.role.list', $roles, $this);
        return $roles;
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
        $sql = 'SELECT user_id FROM user WHERE role_id=?';
        $result = $this->db->fetchColumn($sql, array($role_id));

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

        $options = array('unserialize' => 'permissions');
        $result = $this->db->fetch('SELECT * FROM role WHERE role_id=?', array($role_id), $options);

        $this->hook->attach('user.role.get.after', $role_id, $result, $this);
        return $result;
    }

}
