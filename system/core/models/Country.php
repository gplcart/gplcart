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

/**
 * Manages basic behaviors and data related to countries
 */
class Country implements CrudInterface
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
     * Loads a country from the database
     * @param string $code
     * @return array
     */
    public function get($code)
    {
        $result = &gplcart_static("country.get.$code");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('country.get.before', $code, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM country WHERE code=?';
        $result = $this->db->fetch($sql, array($code), array('unserialize' => 'format'));

        if (isset($result['format'])) {
            $default_format = $this->getDefaultFormat();
            $result['format'] = gplcart_array_merge($default_format, $result['format']);
            gplcart_array_sort($result['format']);
        }

        $this->hook->attach('country.get.after', $code, $result, $this);
        return $result;
    }

    /**
     * Returns an array of countries
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = &gplcart_static(gplcart_array_hash(array('country.list' => $options)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('country.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * ';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(code)';
        }

        $sql .= ' FROM country WHERE LENGTH(code) > 0';

        $conditions = array();

        if (isset($options['name'])) {
            $sql .= ' AND name LIKE ?';
            $conditions[] = "%{$options['name']}%";
        }

        if (isset($options['native_name'])) {
            $sql .= ' AND native_name LIKE ?';
            $conditions[] = "%{$options['native_name']}%";
        }

        if (isset($options['code'])) {
            $sql .= ' AND code LIKE ?';
            $conditions[] = "%{$options['code']}%";
        }

        if (isset($options['status'])) {
            $sql .= ' AND status = ?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'native_name', 'code', 'status', 'weight');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $list = $this->db->fetchAll($sql, $conditions, array('unserialize' => 'format', 'index' => 'code'));
            $result = $this->prepareList($list);
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('country.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns an array of country format
     * @param string|array $country
     * @param bool $only_enabled
     * @return array
     */
    public function getFormat($country, $only_enabled = false)
    {
        $data = is_string($country) ? $this->get($country) : (array) $country;

        if (empty($data['format'])) {
            $format = $this->getDefaultFormat();
            gplcart_array_sort($format);
        } else {
            $format = $data['format'];
        }

        if ($only_enabled) {
            return array_filter($format, function ($item) {
                return !empty($item['status']);
            });
        }

        return $format;
    }

    /**
     * Adds a country
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('country.add.before', $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = true;
        $this->db->insert('country', $data);
        $this->hook->attach('country.add.after', $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a country
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $result = null;
        $this->hook->attach('country.update.before', $code, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        unset($data['code']); // Cannot update primary key

        $result = (bool) $this->db->update('country', $data, array('code' => $code));

        $this->hook->attach('country.update.after', $code, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a country
     * @param string $code
     * @param bool $check
     * @return boolean
     */
    public function delete($code, $check = true)
    {
        $result = null;
        $this->hook->attach('country.delete.before', $code, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($code)) {
            return false;
        }

        $result = (bool) $this->db->delete('country', array('code' => $code));

        if ($result) {
            $this->deleteLinked($code);
        }

        $this->hook->attach('country.delete.after', $code, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the country can be deleted
     * @param string $code
     * @return boolean
     */
    public function canDelete($code)
    {
        $result = $this->db->fetchColumn('SELECT address_id FROM address WHERE country=?', array($code));
        return empty($result);
    }

    /**
     * Returns an array of country names or a country name if the code parameter is set
     * @param null|string $code
     * @return array|string
     */
    public function getIso($code = null)
    {
        $data = (array) gplcart_config_get(GC_FILE_CONFIG_COUNTRY);

        if (isset($code)) {
            return isset($data[$code]) ? $data[$code] : '';
        }

        return $data;
    }

    /**
     * Returns the default country format
     * @return array
     */
    public function getDefaultFormat()
    {
        return (array) gplcart_config_get(GC_FILE_CONFIG_COUNTRY_FORMAT);
    }

    /**
     * Deletes all database records related to the country
     * @param string $code
     */
    protected function deleteLinked($code)
    {
        $this->db->delete('city', array('country' => $code));
        $this->db->delete('state', array('country' => $code));
    }

    /**
     * Prepare an array of countries
     * @param array $list
     * @return array
     */
    protected function prepareList(array $list)
    {
        foreach ($list as &$item) {
            $item['format'] += $this->getDefaultFormat();
        }

        return $list;
    }

}
