<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;

/**
 * Manages basic behaviors and data related to cities
 */
class City extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array of cities
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, s.code AS state_code ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(city_id) ';
        }

        $sql .= ' FROM city c'
            . ' LEFT JOIN state s ON(c.state_id = s.state_id)'
            . ' WHERE c.city_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $where[] = (int)$data['status'];
        }

        if (isset($data['country'])) {
            $sql .= ' AND c.country = ?';
            $where[] = $data['country'];
        }

        if (isset($data['state_code'])) {
            $sql .= ' AND s.code = ?';
            $where[] = $data['state_code'];
        }

        if (isset($data['state_id'])) {
            $sql .= ' AND c.state_id = ?';
            $where[] = $data['state_id'];
        }

        if (isset($data['name'])) {
            $sql .= ' AND c.name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'city_id', 'status');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)
            && isset($data['order']) && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY c.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY c.name ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int)$this->db->fetchColumn($sql, $where);
        }

        $cities = $this->db->fetchAll($sql, $where, array('index' => 'city_id'));

        $this->hook->fire('cities', $cities);
        return $cities;
    }

    /**
     * Adds a city
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.city.before', $data);

        if (empty($data)) {
            return false;
        }

        $city_id = $this->db->insert('city', $data);

        $this->hook->fire('add.city.after', $data, $city_id);
        return $city_id;
    }

    /**
     * Loads a city from the database
     * @param integer $city_id
     * @return array
     */
    public function get($city_id)
    {
        $sql = 'SELECT * FROM city WHERE city_id=?';
        return $this->db->fetch($sql, array($city_id));
    }

    /**
     * Deletes a city
     * @param integer $city_id
     * @return boolean
     */
    public function delete($city_id)
    {
        $this->hook->fire('delete.city.before', $city_id);

        if (empty($city_id)) {
            return false;
        }

        if (!$this->canDelete($city_id)) {
            return false;
        }

        $conditions = array('city_id' => (int)$city_id);
        $result = $this->db->delete('city', $conditions);

        $this->hook->fire('delete.city.after', $city_id, $result);
        return (bool)$result;
    }

    /**
     * Returns true if the city can be deleted
     * @param integer $city_id
     * @return boolean
     */
    public function canDelete($city_id)
    {
        $sql = 'SELECT address_id FROM address WHERE city_id=?';
        $result = $this->db->fetchColumn($sql, array($city_id));
        return empty($result);
    }

    /**
     * Updates a city
     * @param integer $city_id
     * @param array $data
     * @return boolean
     */
    public function update($city_id, array $data)
    {
        $this->hook->fire('update.city.before', $city_id, $data);

        if (empty($city_id)) {
            return false;
        }

        $conditions = array('city_id' => $city_id);
        $result = $this->db->update('city', $data, $conditions);

        $this->hook->fire('update.city.after', $city_id, $data, $result);
        return (bool)$result;
    }

}
