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
use core\classes\Tool;
use core\models\Country;

class Address
{
    
    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * Hook class inctance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Database class instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;
    
    /**
     * Constructor
     * @param Country $country
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Country $country, Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->country = $country;
        $this->db = $this->config->db();
    }

    /**
     * Loads an address from the database
     * @param integer $address_id
     * @return array
     */
    public function get($address_id)
    {
        $this->hook->fire('get.address.before', $address_id);

        $sql = '
                SELECT a.*,
                    c.name AS country_name,
                    c.native_name AS country_native_name,
                    c.format AS country_format,
                    s.name AS state_name,
                    ci.name AS city_name
                FROM address a
                LEFT JOIN country c ON(a.country=c.code)
                LEFT JOIN state s ON(a.state_id=s.state_id)
                LEFT JOIN city ci ON(a.city_id=ci.city_id)
                WHERE a.address_id = :address_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':address_id' => (int) $address_id));
        $address = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($address)) {
            $address['data'] = unserialize($address['data']);
            $address['country_format'] = unserialize($address['country_format']);
        }

        $this->hook->fire('get.address.after', $address_id, $address);
        return $address;
    }

    /**
     * Adds a new address
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.address.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'created' => GC_TIME,
            'user_id' => $data['user_id'],
            'data' => empty($data['data']) ? serialize(array()) : serialize((array) $data['data']),
            'type' => empty($data['type']) ? 'shipping' : $data['type'],
            'state_id' => empty($data['state_id']) ? 0 : $data['state_id'],
            'country' => empty($data['country']) ? '' : $data['country'],
            'city_id' => empty($data['city_id']) ? '' : $data['city_id'],
            'address_1' => empty($data['address_1']) ? '' : $data['address_1'],
            'address_2' => empty($data['address_2']) ? '' : $data['address_2'],
            'phone' => empty($data['phone']) ? '' : $data['phone'],
            'fax' => empty($data['fax']) ? '' : $data['fax'],
            'postcode' => empty($data['postcode']) ? '' : $data['postcode'],
            'company' => empty($data['company']) ? '' : $data['company'],
            'first_name' => empty($data['first_name']) ? '' : $data['first_name'],
            'middle_name' => empty($data['middle_name']) ? '' : $data['middle_name'],
            'last_name' => empty($data['last_name']) ? '' : $data['last_name'],
        );

        $address_id = $this->db->insert('address', $values);
        $this->hook->fire('add.address.after', $values, $address_id);
        return $address_id;
    }

    /**
     *
     * @param type $user_id
     * @param type $status
     * @return type
     */
    public function getTranslatedList($user_id, $status = true)
    {
        $list = $this->getList(array('user_id' => $user_id, 'status' => $status));

        $addresses = array();
        foreach ($list as $address_id => $address) {
            $addresses[$address_id] = $this->getTranslated($address, true);
        }

        return $addresses;
    }
    
    /**
     *
     * @param type $address
     * @param type $both
     * @return type
     */
    public function getTranslated($address, $both = false)
    {
        $default = $this->country->defaultFormat();
        $format = Tool::merge($default, $address['country_format']);

        $results = array();
        foreach ($address as $key => $value) {
            if (empty($format[$key]) || empty($value)) {
                continue;
            }

            if ($key === 'country') {
                $value = $address['country_native_name'];
            }

            if ($key === 'state_id') {
                $value = $address['state_name'];
            }

            if ($both) {
                $results[$format[$key]['name']] = $value;
                continue;
            }

            $results[$key] = $value;
        }

        return $results;
    }

    /**
     * Returns a list of addresses for a given user
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = '
                SELECT a.*,
                    COALESCE(ci.name, ci.city_id) AS city_name,
                    c.name AS country_name,
                    c.native_name AS country_native_name,
                    c.format AS country_format,
                    s.name AS state_name
                FROM address a
                LEFT JOIN country c ON(a.country=c.code)
                LEFT JOIN state s ON(a.state_id=s.state_id)
                LEFT JOIN city ci ON(a.city_id=ci.city_id)
                WHERE a.address_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $sql .= ' AND s.status = ?';
            $sql .= ' AND ci.status = ?';
            $where[] = (int) $data['status'];
            $where[] = (int) $data['status'];
            $where[] = (int) $data['status'];
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND a.user_id = ?';
            $where[] = $data['user_id'];
        }

        $sql .= ' ORDER BY a.created ASC';

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $address) {
            $address['data'] = unserialize($address['data']);
            $address['country_format'] = unserialize($address['country_format']);
            $list[$address['address_id']] = $address;
        }

        $this->hook->fire('address.list', $data, $list);
        return $list;
    }

    /**
     *
     * @param type $data
     * @return string
     */
    public function getGeocodeQuery($data)
    {
        $fields = $this->getGeocodeFields();

        $components = array();
        foreach ($fields as $field) {
            if (!empty($data[$field])) {
                $components[] = $data[$field];
            }
        }

        $this->hook->fire('geocode.query', $data, $fields, $components);
        return implode(',', $components);
    }

    /**
     * Deletes an address
     * @param integer $address_id An address ID
     * @return boolean Returns true on success, false on failure
     */
    public function delete($address_id)
    {
        $this->hook->fire('delete.address.before', $address_id);

        if (empty($address_id)) {
            return false;
        }

        if (!$this->canDelete($address_id)) {
            return false;
        }

        $result = $this->db->delete('address', array('address_id' => (int) $address_id));
        $this->hook->fire('delete.address.after', $address_id, $result);

        return (bool) $result;
    }

    /**
     * Whether the address can be deleted
     * @param integer $address_id
     * @return boolean
     */
    public function canDelete($address_id)
    {
        return !$this->isReferenced($address_id);
    }

    /**
     * Returns true if the address has no references
     * @param type $address_id
     * @return boolean
     */
    public function isReferenced($address_id)
    {
        $sql = 'SELECT order_id
                FROM orders
                WHERE shipping_address=? AND status NOT LIKE ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array((int) $address_id, 'checkout_%'));

        return (bool) $sth->fetchColumn();
    }

    /**
     * Updates an address
     * @param integer $address_id
     * @param array $data
     * @return boolean
     */
    public function update($address_id, array $data)
    {
        $this->hook->fire('update.address.before', $address_id, $data);

        if (empty($address_id)) {
            return false;
        }

        $values = array();

        if (isset($data['state_id'])) {
            $values['state_id'] = (int) $data['state_id'];
        }

        if (isset($data['user_id'])) {
            $values['user_id'] = $data['user_id'];
        }

        if (isset($data['country'])) {
            $values['country'] = $data['country'];
        }

        if (isset($data['city_id'])) {
            $values['city_id'] = $data['city_id'];
        }

        if (isset($data['address_1'])) {
            $values['address_1'] = $data['address_1'];
        }

        if (isset($data['address_2'])) {
            $values['address_2'] = $data['address_2'];
        }

        if (isset($data['phone'])) {
            $values['phone'] = $data['phone'];
        }

        if (isset($data['fax'])) {
            $values['fax'] = $data['fax'];
        }

        if (isset($data['postcode'])) {
            $values['postcode'] = $data['postcode'];
        }

        if (isset($data['company'])) {
            $values['company'] = $data['company'];
        }

        if (isset($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['type'])) {
            $values['type'] = $data['type'];
        }

        if (isset($data['first_name'])) {
            $values['first_name'] = $data['first_name'];
        }

        if (isset($data['middle_name'])) {
            $values['middle_name'] = $data['middle_name'];
        }

        if (isset($data['last_name'])) {
            $values['last_name'] = $data['last_name'];
        }

        if (empty($values)) {
            return false;
        }

        $result = $this->db->update('address', $values, array('address_id' => (int) $address_id));
        $this->hook->fire('update.address.after', $address_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of address fields to be used to format geocoding query
     * @return array
     */
    protected function getGeocodeFields()
    {
        return array('address_1', 'state_id', 'city_id', 'country');
    }
}
