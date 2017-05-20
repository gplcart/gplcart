<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Cache,
    gplcart\core\Model;
use gplcart\core\models\Country as CountryModel;

/**
 * Manages basic behaviors and data related to user addresses
 */
class Address extends Model
{

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * @param CountryModel $country
     */
    public function __construct(CountryModel $country)
    {
        parent::__construct();

        $this->country = $country;
    }

    /**
     * Loads an address from the database
     * @param integer $address_id
     * @return array
     */
    public function get($address_id)
    {
        $address = &Cache::memory(__METHOD__ . $address_id);

        if (isset($address)) {
            return $address;
        }

        $this->hook->fire('address.get.before', $address_id);

        if (empty($address_id)) {
            return $address = array();
        }

        $sql = 'SELECT a.*, c.name AS country_name,'
                . ' c.native_name AS country_native_name,'
                . ' c.format AS country_format, c.zone_id AS country_zone_id,'
                . ' s.name AS state_name, s.zone_id AS state_zone_id,'
                . ' ci.name AS city_name, ci.zone_id AS city_zone_id'
                . ' FROM address a'
                . ' LEFT JOIN country c ON(a.country=c.code)'
                . ' LEFT JOIN state s ON(a.state_id=s.state_id)'
                . ' LEFT JOIN city ci ON(a.city_id=ci.city_id)'
                . ' WHERE a.address_id = ?';

        $options = array('unserialize' => array('data', 'country_format'));
        $address = $this->db->fetch($sql, array($address_id), $options);

        $this->hook->fire('address.get.after', $address_id, $address);
        return $address;
    }

    /**
     * Adds a new address
     * @param array $data
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('address.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data['address_id'] = $this->db->insert('address', $data);

        $this->hook->fire('address.add.after', $data);
        return $data['address_id'];
    }

    /**
     * Returns an array of addresses with translated address fields
     * @param integer $user_id
     * @param boolean $status
     * @return array
     */
    public function getTranslatedList($user_id, $status = true)
    {
        $conditions = array('user_id' => $user_id, 'status' => $status);
        $list = (array) $this->getList($conditions);

        $addresses = array();
        foreach ($list as $address_id => $address) {
            $addresses[$address_id] = $this->getTranslated($address, true);
        }

        return $addresses;
    }

    /**
     * Returns a list of addresses or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        list($sql, $replacements) = $this->getListSql($data);

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $replacements);
        }

        $options = array(
            'index' => 'address_id',
            'unserialize' => array('data', 'country_format')
        );

        $results = $this->db->fetchAll($sql, $replacements, $options);

        $list = $this->prepareList($results, $data);
        $this->hook->fire('address.list', $data, $list);
        return $list;
    }

    /**
     * Returns an array containing SQL and its replacement values for getList() method
     * @param array $data
     * @return array
     */
    protected function getListSql(array $data)
    {
        $sql = 'SELECT a.*, CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name) AS full_name,'
                . ' u.email AS user_email, u.name AS user_name,'
                . ' ci.city_id, COALESCE(ci.name, a.city_id) AS city_name,'
                . ' c.name AS country_name, ci.status AS city_status,'
                . ' c.native_name AS country_native_name, c.format AS country_format, c.status AS country_status,'
                . ' s.name AS state_name, s.status AS state_status';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(a.address_id)';
        }

        $sql .= ' FROM address a'
                . ' LEFT JOIN country c ON(a.country=c.code)'
                . ' LEFT JOIN state s ON(a.state_id=s.state_id)'
                . ' LEFT JOIN city ci ON(a.city_id=ci.city_id)'
                . ' LEFT JOIN user u ON(a.user_id=u.user_id)'
                . ' WHERE a.address_id > 0';

        $replacements = array();
        $this->setGetListSqlConditions($replacements, $sql, $data);
        $this->setGetListSqlSort($sql, $data);
        $this->setSqlLimit($sql, $data);

        return array($sql, $replacements);
    }

    /**
     * Set SQL query conditions for getList() method
     * @param array $where
     * @param string $sql
     * @param array $data
     */
    protected function setGetListSqlConditions(&$where, &$sql, $data)
    {
        if (isset($data['user_id'])) {
            $sql .= ' AND a.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['user_email'])) {
            $sql .= ' AND u.email = ?';
            $where[] = $data['user_email'];
        }

        if (isset($data['full_name'])) {
            $sql .= ' AND CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name) LIKE ?';
            $where[] = "%{$data['full_name']}%";
        }

        if (isset($data['address_1'])) {
            $sql .= ' AND a.address_1 LIKE ?';
            $where[] = "%{$data['address_1']}%";
        }

        if (isset($data['city_id'])) {
            $sql .= ' AND a.city_id = ?';
            $where[] = $data['city_id'];
        }

        if (isset($data['city_name'])) {
            $sql .= ' AND ci.name LIKE ?';
            $where[] = "%{$data['city_name']}%";
        }

        if (isset($data['phone'])) {
            $sql .= ' AND a.phone LIKE ?';
            $where[] = "%{$data['phone']}%";
        }
    }

    /**
     * Set SQL query sort and order clauses for getList() method
     * @param string $sql
     * @param array $data
     */
    protected function setGetListSqlSort(&$sql, array $data)
    {
        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'phone' => 'a.phone',
            'country' => 'a.country',
            'city_id' => 'a.city_id',
            'user_id' => 'a.user_id',
            'address_1' => 'a.address_1',
            'address_id' => 'a.address_id',
            'full_name' => 'CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name)'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= ' ORDER BY a.created ASC';
        }
    }

    /**
     * Returns an array of filtered addresses
     * @param array $addresses
     * @param array $data
     * @return array
     */
    protected function prepareList(array $addresses, array $data)
    {
        $countries = $this->country->getList();

        $list = array();
        foreach ($addresses as $address_id => $address) {

            $list[$address_id] = $address;

            if (empty($data['status'])) {
                continue; // Do not check enabled country, state and city
            }

            if (empty($countries)) {
                continue; // No countries defined in the system
            }

            // Remove addresses with disabled countries
            if ($address['country'] !== '' && $address['country_status'] == 0) {
                unset($list[$address_id]);
                continue;
            }

            // Remove addresses with disabled states
            if (!empty($address['state_id']) && $address['state_status'] == 0) {
                unset($list[$address_id]);
                continue;
            }

            // City ID can also be not numeric (user input)
            if (is_numeric($address['city_id']) && $address['city_status'] == 0) {
                unset($list[$address_id]);
            }
        }

        return $list;
    }

    /**
     * Returns an array of translated address fields
     * @param array $address
     * @param boolean $both
     * @return array
     */
    public function getTranslated(array $address, $both = false)
    {
        $default = $this->country->getDefaultFormat();
        $format = gplcart_array_merge($default, $address['country_format']);

        gplcart_array_sort($format);

        $results = array();
        foreach ($format as $key => $data) {

            if (!array_key_exists($key, $address) || empty($data['status'])) {
                continue;
            }

            if ($key === 'country') {
                $address[$key] = $address['country_native_name'];
            }

            if ($key === 'state_id') {
                $address[$key] = $address['state_name'];
            }

            if ($key === 'city_id' && !empty($address['city_name'])) {
                $address[$key] = $address['city_name'];
            }

            if ($both) {
                $results[$data['name']] = $address[$key];
                continue;
            }

            $results[$key] = $address[$key];
        }

        return array_filter($results);
    }

    /**
     * Returns a string containing formatted geocode query
     * @param array $data
     * @return string
     */
    public function getGeocodeQuery(array $data)
    {
        $fields = $this->getGeocodeFields();

        $components = array();
        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $components[] = $data[$field];
            }
        }

        $this->hook->fire('address.geocode', $data, $fields, $components);
        return implode(',', $components);
    }

    /**
     * Returns an array of address fields to be used to format geocoding query
     * @return array
     */
    protected function getGeocodeFields()
    {
        return array('address_1', 'state_id', 'city_id', 'country');
    }

    /**
     * Reduces max number of addresses that a user can have
     * @param integer $user_id
     */
    public function controlLimit($user_id)
    {
        foreach ($this->getExcess($user_id) as $address) {
            $this->delete($address['address_id']);
        }
    }

    /**
     * Returns an array of excess address items for the user ID
     * @param string|integer $user_id
     * @param null|array $existing
     * @return array
     */
    public function getExcess($user_id, $existing = null)
    {
        $limit = $this->getLimit($user_id);

        if (empty($limit)) {
            return array();
        }

        if (!isset($existing)) {
            $existing = $this->getList(array('user_id' => $user_id));
        }

        $count = count($existing);

        if (empty($count) || $count <= $limit) {
            return array();
        }

        return array_slice((array) $existing, 0, ($count - $limit));
    }

    /**
     * Returns a number of addresses that a user can have
     * @param string|integer
     * @return integer
     */
    public function getLimit($user_id)
    {
        if (empty($user_id) || !is_numeric($user_id)) {
            return (int) $this->config->get('user_address_limit_anonymous', 1);
        }

        return (int) $this->config->get('user_address_limit', 4);
    }

    /**
     * Deletes an address
     * @param integer $address_id
     * @return boolean
     */
    public function delete($address_id)
    {
        $this->hook->fire('address.delete.before', $address_id);

        if (empty($address_id)) {
            return false;
        }

        if (!$this->canDelete($address_id)) {
            return false;
        }

        $conditions = array('address_id' => $address_id);
        $result = $this->db->delete('address', $conditions);
        $this->hook->fire('address.delete.after', $address_id, $result);

        return (bool) $result;
    }

    /**
     * Whether the address can be deleted
     * @param integer $address_id
     * @return boolean
     */
    public function canDelete($address_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT order_id FROM orders WHERE shipping_address=:id)'
                . ' AND NOT EXISTS (SELECT order_id FROM orders WHERE payment_address=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $address_id));
    }

    /**
     * Updates an address
     * @param integer $address_id
     * @param array $data
     * @return boolean
     */
    public function update($address_id, array $data)
    {
        $this->hook->fire('address.update.before', $address_id, $data);

        if (empty($address_id)) {
            return false;
        }

        $conditions = array('address_id' => $address_id);
        $result = $this->db->update('address', $data, $conditions);

        $this->hook->fire('address.update.after', $address_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of address types
     * @return array
     */
    public function getTypes()
    {
        return array('shipping', 'payment');
    }

}
