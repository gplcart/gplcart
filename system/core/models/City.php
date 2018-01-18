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
 * Manages basic behaviors and data related to cities
 */
class City
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
     * Loads a city from the database
     * @param int|array|string $condition
     * @return array
     */
    public function get($condition)
    {
        if (!is_array($condition)) {
            $condition = array('city_id' => $condition);
        }

        $result = &gplcart_static(gplcart_array_hash(array('city.get' => $condition)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('city.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('city.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of cities or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('city.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT c.*, s.code AS state_code, s.status AS state_status,
                co.name AS country_name, co.status AS country_status';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(city_id)';
        }

        $sql .= ' FROM city c
                  LEFT JOIN state s ON(c.state_id = s.state_id)
                  LEFT JOIN country co ON(co.code = s.country)';

        $conditions = array();

        if (isset($options['city_id'])) {
            $sql .= ' WHERE c.city_id = ?';
            $conditions[] = $options['city_id'];
        } else {
            $sql .= ' WHERE c.city_id IS NOT NULL';
        }

        if (isset($options['status'])) {
            $sql .= ' AND c.status = ?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['state_status'])) {
            $sql .= ' AND s.status = ?';
            $conditions[] = (int) $options['state_status'];
        }

        if (isset($options['country_status'])) {
            $sql .= ' AND co.status = ?';
            $conditions[] = (int) $options['country_status'];
        }

        if (isset($options['country'])) {
            $sql .= ' AND c.country = ?';
            $conditions[] = $options['country'];
        }

        if (isset($options['state_code'])) {
            $sql .= ' AND s.code = ?';
            $conditions[] = $options['state_code'];
        }

        if (isset($options['state_id'])) {
            $sql .= ' AND c.state_id = ?';
            $conditions[] = $options['state_id'];
        }

        if (isset($options['name'])) {
            $sql .= ' AND c.name LIKE ?';
            $conditions[] = "%{$options['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'city_id', 'status');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY c.{$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY c.name ASC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'city_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('city.list.after', $options, $result, $this);
        return $result;
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
     * Deletes a city
     * @param integer $city_id
     * @param bool $check
     * @return boolean
     */
    public function delete($city_id, $check = true)
    {
        $result = null;
        $this->hook->attach('city.delete.before', $city_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($city_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('city', array('city_id' => $city_id));
        $this->hook->attach('city.delete.after', $city_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a city can be deleted
     * @param integer $city_id
     * @return boolean
     */
    public function canDelete($city_id)
    {
        $result = $this->db->fetchColumn('SELECT address_id FROM address WHERE city_id=?', array($city_id));
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
