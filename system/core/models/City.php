<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;

/**
 * Manages basic behaviors and data related to cities
 */
class City
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
        $this->db = $config->getDb();
    }

    /**
     * Returns an array of cities for a given state
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, s.code AS state_code ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(city_id) ';
        }

        $sql .= 'FROM city c LEFT JOIN state s ON(c.state_id = s.state_id) WHERE c.city_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $where[] = (int) $data['status'];
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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc')))) {
            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY c.name {$data['order']}";
                    break;
                case 'city_id':
                    $sql .= " ORDER BY c.city_id {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY c.status {$data['order']}";
                    break;
            }
        } else {
            $sql .= ' ORDER BY c.name ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $cities = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $city) {
            $city['data'] = unserialize($city['data']);
            $cities[$city['city_id']] = $city;
        }

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

        $city_id = $this->db->insert('city', array(
            'status' => !empty($data['status']),
            'data' => !empty($data['data']) ? serialize((array) $data['data']) : serialize(array()),
            'state_id' => $data['state_id'],
            'country' => $data['country'],
            'name' => $data['name'],
        ));

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
        $sth = $this->db->prepare('SELECT * FROM city WHERE city_id=:city_id');
        $sth->execute(array(':city_id' => $city_id));
        return $sth->fetch(PDO::FETCH_ASSOC);
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

        $result = $this->db->delete('city', array('city_id' => (int) $city_id));

        $this->hook->fire('delete.city.after', $city_id, $result);
        return (bool) $result;
    }

    /**
     * Returns true if the city can be deleted
     * @param integer $city_id
     * @return boolean
     */
    public function canDelete($city_id)
    {
        $sth = $this->db->prepare('SELECT address_id FROM address WHERE city_id=:city_id');
        $sth->execute(array(':city_id' => (int) $city_id));
        return !$sth->fetchColumn();
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

        $values = array();

        if (!empty($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (!empty($data['state_id'])) {
            $values['state_id'] = (int) $data['state_id'];
        }

        if (!empty($data['country'])) {
            $values['country'] = $data['country'];
        }

        if (isset($data['status'])) {
            $values['status'] = (bool) $data['status'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        $result = false;

        if ($values) {
            $result = $this->db->update('city', $values, array('city_id' => (int) $city_id));
        }

        $this->hook->fire('update.city.after', $city_id, $data, $result);
        return (bool) $result;
    }
}
