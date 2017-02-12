<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;

/**
 * Manages basic behaviors and data related to country states
 */
class State extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a state
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('state.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['state_id'] = $this->db->insert('state', $data);

        $this->hook->fire('state.add.after', $data);
        return $data['state_id'];
    }

    /**
     * Loads a state from the database
     * @param integer $state_id
     * @return array
     */
    public function get($state_id)
    {
        $this->hook->fire('state.get.before', $state_id);

        $sql = 'SELECT * FROM state WHERE state_id=?';
        $state = $this->db->fetch($sql, array($state_id));

        $this->hook->fire('state.get.after', $state_id, $state);
        return $state;
    }

    /**
     * Loads a state by code
     * @param string $code
     * @param string|null $country
     * @return array
     */
    public function getByCode($code, $country = null)
    {
        $conditions = array('code' => $code, 'country' => $country);
        $state = $this->getList($conditions);
        return $state ? reset($state) : array();
    }

    /**
     * Returns an array of states or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(state_id)';
        }

        $sql .= ' FROM state WHERE state_id > 0';
        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['country'])) {
            $sql .= ' AND country = ?';
            $where[] = $data['country'];
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('country', 'name', 'code', 'status', 'state_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY name ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $states = $this->db->fetchAll($sql, $where, array('index' => 'state_id'));
        $this->hook->fire('state.list', $states);
        return $states;
    }

    /**
     * Deletes a state
     * @param integer $state_id
     * @return boolean
     */
    public function delete($state_id)
    {
        $this->hook->fire('state.delete.before', $state_id);

        if (empty($state_id)) {
            return false;
        }

        if (!$this->canDelete($state_id)) {
            return false;
        }

        $conditions = array('state_id' => (int) $state_id);
        $deleted = (bool) $this->db->delete('state', $conditions);

        if ($deleted) {
            $this->db->delete('city', $conditions);
        }

        $this->hook->fire('state.delete.after', $state_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Whether the state can be deleted
     * @param integer $state_id
     * @return boolean
     */
    public function canDelete($state_id)
    {
        $sql = 'SELECT address_id FROM address WHERE state_id=?';
        $result = $this->db->fetchColumn($sql, array($state_id));

        return empty($result);
    }

    /**
     * Updates a state
     * @param integer $state_id
     * @param array $data
     * @return boolean
     */
    public function update($state_id, array $data)
    {
        $this->hook->fire('state.update.before', $state_id, $data);

        if (empty($state_id)) {
            return false;
        }

        $conditions = array('state_id' => $state_id);
        $result = $this->db->update('state', $data, $conditions);

        $this->hook->fire('state.update.after', $state_id, $data, $result);
        return (bool) $result;
    }

}
