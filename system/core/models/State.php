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

/**
 * Manages basic behaviors and data related to country states
 */
class State
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->db();
    }

    /**
     * Adds a state
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.state.before', $data);

        if (empty($data)) {
            return false;
        }

        $state_id = $this->db->insert('state', array(
            'code' => $data['code'],
            'name' => $data['name'],
            'country' => $data['country'],
            'status' => !empty($data['status']),
            'data' => !empty($data['data']) ? serialize((array) $data['data']) : serialize(array())
        ));

        $this->hook->fire('add.state.after', $data, $state_id);
        return $state_id;
    }

    /**
     * Loads a state from the database
     * @param integer $state_id
     * @return array
     */
    public function get($state_id)
    {
        $sth = $this->db->prepare('SELECT * FROM state WHERE state_id=:state_id');
        $sth->execute(array(':state_id' => $state_id));
        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Loads a state by code
     * @param string $code
     * @param string|null $country
     * @return array
     */
    public function getByCode($code, $country = null)
    {
        $state = $this->getList(array('code' => $code, 'country' => $country));
        return $state ? reset($state) : array();
    }

    /**
     * Returns an array of states or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(state_id) ';
        }

        $sql .= 'FROM state WHERE state_id > 0';
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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc')))) {
            switch ($data['sort']) {
                case 'country':
                    $sql .= " ORDER BY country {$data['order']}";
                    break;
                case 'name':
                    $sql .= " ORDER BY name {$data['order']}";
                    break;
                case 'code':
                    $sql .= " ORDER BY code {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
                case 'state_id':
                    $sql .= " ORDER BY state_id {$data['order']}";
            }
        } else {
            $sql .= ' ORDER BY name ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $states = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $state) {
            $state['data'] = unserialize($state['data']);
            $states[$state['state_id']] = $state;
        }

        $this->hook->fire('states', $states);
        return $states;
    }

    /**
     * Deletes a state
     * @param integer $state_id
     * @return boolean
     */
    public function delete($state_id)
    {
        $this->hook->fire('delete.state.before', $state_id);

        if (empty($state_id)) {
            return false;
        }

        if (!$this->canDelete($state_id)) {
            return false;
        }

        $this->db->delete('state', array('state_id' => (int) $state_id));
        $this->db->delete('zone', array('state_id' => (int) $state_id));
        $this->db->delete('city', array('state_id' => (int) $state_id));

        $this->hook->fire('delete.state.after', $state_id);
        return true;
    }

    /**
     * Whether the state can be deleted
     * @param integer $state_id
     * @return boolean
     */
    public function canDelete($state_id)
    {
        $sth = $this->db->prepare('SELECT address_id FROM address WHERE state_id=:state_id');
        $sth->execute(array(':state_id' => (int) $state_id));
        return !$sth->fetchColumn();
    }

    /**
     * Updates a state
     * @param integer $state_id
     * @param array $data
     * @return boolean
     */
    public function update($state_id, array $data)
    {
        $this->hook->fire('update.state.before', $state_id, $data);

        if (empty($state_id)) {
            return false;
        }

        $values = array();

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (!empty($data['code'])) {
            $values['code'] = $data['code'];
        }

        if (!empty($data['name'])) {
            $values['name'] = $data['name'];
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('state', $values, array('state_id' => (int) $state_id));
        }

        $this->hook->fire('update.state.after', $state_id, $data, $result);
        return (bool) $result;
    }
}
