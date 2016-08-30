<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to countries
 */
class Country extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of country format
     * @param string|array $country
     * @param bool $only_enabled
     * @return array
     */
    public function getFormat($country, $only_enabled = false)
    {
        $country_data = is_string($country) ? $this->get($country) : (array) $country;

        if (empty($country_data['format'])) {
            $format = $this->defaultFormat();
            Tool::sortWeight($format);
        } else {
            $format = $country_data['format'];
        }

        if ($only_enabled) {
            return array_filter($format, function ($item) {
                return !empty($item['status']);
            });
        }

        return $format;
    }

    /**
     * Loads a country from the database
     * @param string $country_code
     * @return array
     */
    public function get($country_code)
    {
        $country = &Cache::memory("country.$country_code");

        if (isset($country)) {
            return $country;
        }

        $this->hook->fire('get.country.before', $country_code);

        $sth = $this->db->prepare('SELECT * FROM country WHERE code=:code');
        $sth->execute(array(':code' => $country_code));

        $country = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($country)) {
            $country['format'] = unserialize($country['format']);
            $default_format = $this->defaultFormat();
            $country['format'] = Tool::merge($default_format, $country['format']);
            $country['default'] = $this->isDefault($country_code);
            Tool::sortWeight($country['format']);
        }

        $this->hook->fire('get.country.after', $country_code, $country);
        return $country;
    }

    /**
     * Returns the default country format
     * @return array
     */
    public function defaultFormat()
    {
        return array(
            'country' => array(
                'name' => $this->language->text('Country'),
                'required' => 0, 'weight' => 0,
                'status' => 1
            ),
            'state_id' => array(
                'name' => $this->language->text('State'),
                'required' => 0, 'weight' => 1,
                'status' => 1
            ),
            'city_id' => array(
                'name' => $this->language->text('City'),
                'required' => 1,
                'weight' => 2,
                'status' => 1
            ),
            'address_1' => array(
                'name' => $this->language->text('Address'),
                'required' => 1,
                'weight' => 3,
                'status' => 1
            ),
            'address_2' => array(
                'name' => $this->language->text('Additional address'),
                'required' => 0,
                'weight' => 4,
                'status' => 0
            ),
            'phone' => array(
                'name' => $this->language->text('Phone'),
                'required' => 1,
                'weight' => 5,
                'status' => 1
            ),
            'postcode' => array(
                'name' => $this->language->text('Post code'),
                'required' => 1,
                'weight' => 6,
                'status' => 1
            ),
            'first_name' => array(
                'name' => $this->language->text('First name'),
                'required' => 1,
                'weight' => 7,
                'status' => 1
            ),
            'middle_name' => array(
                'name' => $this->language->text('Middle name'),
                'required' => 1,
                'weight' => 8,
                'status' => 1
            ),
            'last_name' => array(
                'name' => $this->language->text('Last name'),
                'required' => 1,
                'weight' => 9,
                'status' => 1
            ),
            'company' => array(
                'name' => $this->language->text('Company'),
                'required' => 0,
                'weight' => 10,
                'status' => 0
            ),
            'fax' => array(
                'name' => $this->language->text('Fax'),
                'required' => 0,
                'weight' => 11,
                'status' => 0
            ),
        );
    }

    /**
     * Returns a default country code
     * @return string
     */
    public function getDefault()
    {
        return $this->config->get('country', '');
    }

    /**
     * Sets a default country code
     * @param string $code
     * @return boolean
     */
    public function setDefault($code)
    {
        return $this->config->set('country', $code);
    }

    /**
     * Removes a country from being default
     * @param string $code
     * @return boolean
     */
    public function unsetDefault($code)
    {
        if ($this->getDefault() === $code) {
            return $this->setDefault('');
        }

        return false;
    }

    /**
     * Whether a country is default
     * @param string $country_code
     * @return boolean
     */
    public function isDefault($country_code)
    {
        return ($country_code === $this->getDefault());
    }

    /**
     * Adds a country
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.country.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'format' => empty($data['format']) ? serialize(array()) : serialize($data['format']),
            'status' => !empty($data['status']),
            'weight' => empty($data['weight']) ? 0 : (int) $data['weight'],
            'name' => $data['name'],
            'native_name' => $data['native_name'],
            'code' => $data['code'],
        );

        if (!empty($data['default'])) {
            $this->setDefault($data['code']);
        }

        $country_id = $this->db->insert('country', $values);
        $this->hook->fire('add.country.after', $data, $country_id);
        return $country_id;
    }

    /**
     * Updates a country
     * @param string $code
     * @param array $data
     * @return boolean
     */
    public function update($code, array $data)
    {
        $this->hook->fire('update.country.before', $code, $data);

        if (empty($code)) {
            return false;
        }

        $values = array();

        if (!empty($data['format'])) {
            $values['format'] = serialize((array) $data['format']);
        }

        if (!empty($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (!empty($data['native_name'])) {
            $values['native_name'] = $data['native_name'];
        }

        if (isset($data['status'])) {
            if ($this->isDefault($code)) {
                $data['status'] = 1;
            }

            $values['status'] = (int) $data['status'];
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (!empty($data['default'])) {
            $this->setDefault($code);
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('country', $values, array('code' => $code));
            $this->hook->fire('update.country.after', $code, $data, $result);
        }

        return (bool) $result;
    }

    /**
     * Deletes a country
     * @param string $code
     * @return boolean
     */
    public function delete($code)
    {
        $this->hook->fire('delete.country.before', $code);

        if (empty($code)) {
            return false;
        }

        if (!$this->canDelete($code)) {
            return false;
        }

        $this->db->delete('country', array('code' => $code));
        $this->db->delete('zone', array('country' => $code));
        $this->db->delete('state', array('country' => $code));
        $this->db->delete('city', array('country' => $code));

        $this->hook->fire('delete.country.after', $code);

        return true;
    }

    /**
     * Returns true if a country can be deleted
     * @param string $code
     * @return boolean
     */
    public function canDelete($code)
    {
        $sth = $this->db->prepare('SELECT address_id FROM address WHERE country=:country');
        $sth->execute(array(':country' => $code));
        return !$sth->fetchColumn();
    }

    /**
     * Returns an array of country names
     * @param boolean $enabled
     * @return array
     */
    public function getNames($enabled = false)
    {
        $names = array();
        $countries = $this->getList(array('status' => $enabled));

        foreach ($countries as $code => $country) {
            $names[$code] = $country['native_name'];
        }

        return $names;
    }

    /**
     * Returns an array of countries
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $list = &Cache::memory('countries.' . md5(serialize($data)));

        if (isset($list)) {
            return $list;
        }

        $list = array();

        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(code) ';
        }

        $sql .= 'FROM country WHERE LENGTH(code) > 0';

        $where = array();

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['native_name'])) {
            $sql .= ' AND native_name LIKE ?';
            $where[] = "%{$data['native_name']}%";
        }

        if (isset($data['code'])) {
            $sql .= ' AND code LIKE ?';
            $where[] = "%{$data['code']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc')))) {
            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY name {$data['order']}";
                    break;
                case 'native_name':
                    $sql .= " ORDER BY native_name {$data['order']}";
                    break;
                case 'code':
                    $sql .= " ORDER BY code {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
                case 'weight':
                    $sql .= " ORDER BY weight {$data['order']}";
                    break;
            }
        } else {
            $sql .= ' ORDER BY weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $country) {
            $country['format'] = unserialize($country['format']);
            $list[$country['code']] = $country;
            $list[$country['code']]['format'] += $this->defaultFormat();
        }

        $this->hook->fire('countries', $list);
        return $list;
    }

}
