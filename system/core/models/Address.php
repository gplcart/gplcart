<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Country as CountryModel;

/**
 * Manages basic behaviors and data related to user addresses
 */
class Address implements CrudInterface
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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param CountryModel $country
     */
    public function __construct(Hook $hook, Config $config, CountryModel $country)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->country = $country;
        $this->db = $this->config->getDb();
    }

    /**
     * Adds a new address
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('address.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = GC_TIME;
        $result = $this->db->insert('address', $data);
        $this->hook->attach('address.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates an address
     * @param integer $address_id
     * @param array $data
     * @return boolean
     */
    public function update($address_id, array $data)
    {
        $result = null;
        $this->hook->attach('address.update.before', $address_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('address', $data, array('address_id' => $address_id));
        $this->hook->attach('address.update.after', $address_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes an address
     * @param integer $address_id
     * @param bool $check
     * @return boolean
     */
    public function delete($address_id, $check = true)
    {
        $result = null;
        $this->hook->attach('address.delete.before', $address_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($address_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('address', array('address_id' => $address_id));
        $this->hook->attach('address.delete.after', $address_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Loads an address from the database
     * @param int|array|string $condition
     * @return array
     */
    public function get($condition)
    {
        if (!is_array($condition)) {
            $condition = array('address_id' => $condition);
        }

        $result = &gplcart_static(gplcart_array_hash(array('address.get' => $condition)));

        if (isset($result)) {
            return $result;
        }

        $result = null;
        $this->hook->attach('address.get.before', $condition, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $condition['prepare'] = false;
        $condition['limit'] = array(0, 1);

        $list = $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        if (isset($result['country_format'])) {
            $result['country_format'] += $this->country->getDefaultFormat();
        }

        $this->hook->attach('address.get.after', $condition, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of addresses or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('prepare' => true);

        $result = null;
        $this->hook->attach('address.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT a.*, TRIM(a.first_name || " " || a.middle_name || " " || a.last_name) AS full_name,
                u.email AS user_email, u.name AS user_name,
                ci.city_id, COALESCE(ci.name, a.city_id) AS city_name, ci.status AS city_status, ci.zone_id AS city_zone_id,
                c.name AS country_name, c.zone_id AS country_zone_id,
                c.native_name AS country_native_name, c.format AS country_format, c.status AS country_status,
                s.name AS state_name, s.status AS state_status, s.zone_id AS state_zone_id';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(a.address_id)';
        }

        $sql .= ' FROM address a
                  LEFT JOIN country c ON(a.country=c.code)
                  LEFT JOIN state s ON(a.state_id=s.state_id)
                  LEFT JOIN city ci ON(a.city_id=ci.city_id)
                  LEFT JOIN user u ON(a.user_id=u.user_id)';

        $conditions = array();

        if (isset($options['address_id'])) {
            $sql .= ' WHERE a.address_id = ?';
            $conditions[] = $options['address_id'];
        } else {
            $sql .= ' WHERE a.address_id IS NOT NULL';
        }

        if (isset($options['user_id'])) {
            $sql .= ' AND a.user_id = ?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['user_email_like'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$options['user_email_like']}%";
        }

        if (isset($options['full_name'])) {
            $sql .= ' AND TRIM(a.first_name || " " || a.middle_name || " " || a.last_name) LIKE ?';
            $conditions[] = "%{$options['full_name']}%";
        }

        if (isset($options['address_1'])) {
            $sql .= ' AND a.address_1 LIKE ?';
            $conditions[] = "%{$options['address_1']}%";
        }

        if (isset($options['city_id'])) {
            $sql .= ' AND a.city_id = ?';
            $conditions[] = $options['city_id'];
        }

        if (isset($options['city_name'])) {
            $sql .= ' AND (ci.name LIKE ? OR a.city_id LIKE ?)';
            $conditions[] = "%{$options['city_name']}%";
            $conditions[] = "%{$options['city_name']}%";
        }

        if (isset($options['phone'])) {
            $sql .= ' AND a.phone LIKE ?';
            $conditions[] = "%{$options['phone']}%";
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array('phone' => 'a.phone', 'country' => 'a.country', 'city_id' => 'a.city_id',
            'user_id' => 'a.user_id', 'user_email' => 'u.email', 'address_1' => 'a.address_1',
            'address_id' => 'a.address_id',
            'full_name' => 'TRIM(a.first_name || " " || a.middle_name || " " || a.last_name)'
        );

        if (isset($options['sort'])
            && isset($allowed_sort[$options['sort']])
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= ' ORDER BY a.created ASC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {

            $fetch_options = array(
                'index' => 'address_id',
                'unserialize' => array('data', 'country_format')
            );

            $result = $this->db->fetchAll($sql, $conditions, $fetch_options);

            if (!empty($options['prepare'])) {
                $result = $this->prepareList($result, $options);
            }
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('address.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns an array of addresses with translated address fields for the user
     * @param integer $user_id
     * @param boolean $status
     * @return array
     */
    public function getTranslatedList($user_id, $status = true)
    {
        $conditions = array(
            'status' => $status,
            'user_id' => $user_id
        );

        $addresses = array();
        foreach ((array) $this->getList($conditions) as $address_id => $address) {
            $addresses[$address_id] = $this->getTranslated($address, true);
        }

        return $addresses;
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
     * Returns an array of exceeded addresses for the user ID
     * @param string|integer $user_id
     * @param null|array $existing
     * @return array
     */
    public function getExceeded($user_id, $existing = null)
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
     * Returns a number of addresses the user can have
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
     * Whether the address can be deleted
     * @param integer $address_id
     * @return boolean
     */
    public function canDelete($address_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT order_id FROM orders WHERE shipping_address=:id)
                AND NOT EXISTS (SELECT order_id FROM orders WHERE payment_address=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $address_id));
    }

    /**
     * Returns an array of address types
     * @return array
     */
    public function getTypes()
    {
        return array('shipping', 'payment');
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

            if ($address['country'] !== '' && $address['country_status'] == 0) {
                unset($list[$address_id]);
                continue;
            }

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

}
