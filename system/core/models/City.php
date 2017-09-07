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
        $sql = 'SELECT c.*, s.code AS state_code, s.status AS state_status,'
                . 'co.name AS country_name, co.status AS country_status';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(city_id)';
        }

        $sql .= ' FROM city c'
                . ' LEFT JOIN state s ON(c.state_id = s.state_id)'
                . ' LEFT JOIN country co ON(co.code = s.country)'
                . ' WHERE c.city_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['state_status'])) {
            $sql .= ' AND s.status = ?';
            $where[] = (int) $data['state_status'];
        }

        if (isset($data['country_status'])) {
            $sql .= ' AND co.status = ?';
            $where[] = (int) $data['country_status'];
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

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
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
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $cities = $this->db->fetchAll($sql, $where, array('index' => 'city_id'));

        $this->hook->attach('city.list', $cities, $this);
        return $cities;
    }

    /**
     * Adds a city
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('city.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('city', $data);

        $this->hook->attach('city.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a city from the database
     * @param integer $city_id
     * @return array
     */
    public function get($city_id)
    {
        $result = &gplcart_static(__METHOD__ . "$city_id");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('city.get.before', $city_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->db->fetch('SELECT * FROM city WHERE city_id=?', array($city_id));

        $this->hook->attach('city.get.after', $city_id, $result, $this);
        return $result;
    }

    /**
     * Deletes a city
     * @param integer $city_id
     * @return boolean
     */
    public function delete($city_id)
    {
        $result = null;
        $this->hook->attach('city.delete.before', $city_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($city_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('city', array('city_id' => $city_id));

        $this->hook->attach('city.delete.after', $city_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a city can be deleted
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
        $result = null;
        $this->hook->attach('city.update.before', $city_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('city', $data, array('city_id' => $city_id));

        $this->hook->attach('city.update.after', $city_id, $data, $result, $this);
        return (bool) $result;
    }

}
