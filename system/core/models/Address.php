<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Config,
    gplcart\core\Hook;
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
     * @param Config $config
     * @param Hook $hook
     * @param CountryModel $country
     */
    public function __construct(Config $config, Hook $hook, CountryModel $country)
    {
        parent::__construct($config, $hook);

        $this->country = $country;
    }

    /**
     * Loads an address from the database
     * @param integer $address_id
     * @return array
     */
    public function get($address_id)
    {
        $result = &gplcart_static("address.get.$address_id");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('address.get.before', $address_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT a.*, c.name AS country_name,'
                . ' c.native_name AS country_native_name, c.template AS country_address_template,'
                . ' c.format AS country_format, c.zone_id AS country_zone_id,'
                . ' s.name AS state_name, s.zone_id AS state_zone_id,'
                . ' ci.name AS city_name, ci.zone_id AS city_zone_id'
                . ' FROM address a'
                . ' LEFT JOIN country c ON(a.country=c.code)'
                . ' LEFT JOIN state s ON(a.state_id=s.state_id)'
                . ' LEFT JOIN city ci ON(a.city_id=ci.city_id)'
                . ' WHERE a.address_id = ?';

        $options = array('unserialize' => array('data', 'country_format'));
        $result = $this->db->fetch($sql, array($address_id), $options);

        if (isset($result['country_format'])) {
            $result['country_format'] += $this->country->getDefaultFormat();
        }

        $this->hook->attach('address.get.after', $address_id, $result, $this);
        return $result;
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
        $sql = 'SELECT a.*, CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name) AS full_name,'
                . ' u.email AS user_email, u.name AS user_name,'
                . ' ci.city_id, COALESCE(ci.name, a.city_id) AS city_name, ci.status AS city_status,'
                . ' c.name AS country_name, c.template AS country_address_template,'
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

        $conditions = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND a.user_id = ?';
            $conditions[] = $data['user_id'];
        }

        if (isset($data['user_email'])) {
            $sql .= ' AND u.email = ?';
            $conditions[] = $data['user_email'];
        }

        if (isset($data['full_name'])) {
            $sql .= ' AND CONCAT_WS(" ", a.first_name, a.middle_name, a.last_name) LIKE ?';
            $conditions[] = "%{$data['full_name']}%";
        }

        if (isset($data['address_1'])) {
            $sql .= ' AND a.address_1 LIKE ?';
            $conditions[] = "%{$data['address_1']}%";
        }

        if (isset($data['city_id'])) {
            $sql .= ' AND a.city_id = ?';
            $conditions[] = $data['city_id'];
        }

        if (isset($data['city_name'])) {
            $sql .= ' AND ci.name LIKE ?';
            $conditions[] = "%{$data['city_name']}%";
        }

        if (isset($data['phone'])) {
            $sql .= ' AND a.phone LIKE ?';
            $conditions[] = "%{$data['phone']}%";
        }

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

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $options = array(
            'index' => 'address_id',
            'unserialize' => array('data', 'country_format')
        );

        $results = $this->db->fetchAll($sql, $conditions, $options);

        $list = $this->prepareList($results, $data);
        $this->hook->attach('address.list', $data, $list, $this);
        return $list;
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
     * Returns formatted address
     * @param integer|array $address
     * @return string
     */
    public function getFormatted($address)
    {
        if (!is_array($address)) {
            $address = $this->get($address);
        }

        if (empty($address)) {
            return '';
        }

        if (empty($address['country_address_template'])) {
            $address['country_address_template'] = $this->country->getDefaultAddressTemplate();
        }

        return gplcart_string_render($address['country_address_template'], $address);
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
        $result = null;
        $this->hook->attach('address.update.before', $address_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('address_id' => $address_id);
        $result = (bool) $this->db->update('address', $data, $conditions);

        $this->hook->attach('address.update.after', $address_id, $data, $result, $this);
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
