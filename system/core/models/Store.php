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
use core\classes\Cache;
use core\classes\Request;

/**
 * Manages basic behaviors and data related to stores
 */
class Store
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Hook $hook
     * @param Request $request
     * @param Config $config
     */
    public function __construct(Hook $hook, Request $request, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
        $this->db = $this->config->getDb();
    }

    /**
     * Returns an array of store names
     * @return array
     */
    public function getNames()
    {
        $list = array();
        foreach ($this->getList() as $store) {
            $list[$store['store_id']] = $store['name'];
        }

        return $list;
    }

    /**
     * Returns an array of stores or counts them
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $cache_key = 'stores';

        if (!empty($data)) {
            $cache_key .= md5(serialize($data));
        }

        $stores = &Cache::memory($cache_key);

        if (isset($stores)) {
            return $stores;
        }

        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(store_id)';
        }

        $sql .= ' FROM store WHERE store_id > 0';

        $where = array();
        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        if (isset($data['domain'])) {
            $sql .= ' AND domain LIKE ?';
            $where[] = "%{$data['domain']}%";
        }

        if (isset($data['basepath'])) {
            $sql .= ' AND basepath LIKE ?';
            $where[] = "%{$data['basepath']}%";
        }

        if (isset($data['scheme'])) {
            $sql .= ' AND scheme = ?';
            $where[] = (strpos($data['scheme'], '://') === false) ? $data['scheme'] . '://' : $data['scheme'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'name':
                    $sql .= " ORDER BY name {$data['order']}";
                    break;
                case 'domain':
                    $sql .= " ORDER BY domain {$data['order']}";
                    break;
                case 'basepath':
                    $sql .= " ORDER BY basepath {$data['order']}";
                    break;
                case 'scheme':
                    $sql .= " ORDER BY scheme {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
            }
        } else {
            $sql .= " ORDER BY name ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $stores = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $store) {
            $store['data'] = unserialize($store['data']);
            $stores[$store['store_id']] = $store;
        }

        $this->hook->fire('stores', $stores);
        return $stores;
    }

    /**
     * Returns the current store ID
     * @return integer|null
     */
    public function id()
    {
        $current = $this->current();
        return isset($current['store_id']) ? (int) $current['store_id'] : null;
    }

    /**
     * Returns the current store
     * @return array
     */
    public function current()
    {
        $store = &Cache::memory('current_store');

        if (isset($store)) {
            return $store;
        }

        $domain = $this->request->host();
        $basepath = trim($this->request->base(true), '/');

        if ($basepath !== '') {
            $domain .= "/$basepath";
        }

        $store = $this->get($domain);
        return $store;
    }

    /**
     * Loads a store from the database
     * @param integer|string $store_id Either store ID or domain
     * @return array
     */
    public function get($store_id)
    {
        if (empty($this->db)) {
            return array();
        }

        $this->hook->fire('get.store.before', $store_id);

        $store = &Cache::memory("store.$store_id");

        if (isset($store)) {
            return $store;
        }

        if (is_numeric($store_id)) {
            $sql = 'SELECT * FROM store WHERE store_id=:store_id';
            $where = array(':store_id' => $store_id);
        } else {
            $sql = 'SELECT * FROM store WHERE domain=:domain';
            $where = array(':domain' => $store_id);

            if (strpos($store_id, '/') !== false) {
                $parts = explode('/', $store_id, 2);
                $sql = 'SELECT * FROM store WHERE domain=:domain AND basepath=:basepath';
                $where = array(':domain' => $parts[0], ':basepath' => $parts[1]);
            }
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        $store = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($store)) {
            $store['data'] = unserialize($store['data']);
            $default_settings = $this->defaultConfig();
            $store['data'] = $store['data'] + $default_settings;
        }

        $this->hook->fire('get.store.after', $store_id, $store);
        return $store;
    }

    /**
     * Returns an array of default settings
     * @return array
     */
    public function defaultConfig()
    {
        return array(
            'address' => '',
            'anonymous_checkout' => 1,
            'catalog_pricerule' => 1,
            'email' => array(),
            'favicon' => '',
            'fax' => array(),
            'invoice_prefix' => '',
            'logo' => '',
            'map' => array(),
            'meta_title' => 'GPL Cart',
            'owner' => '',
            'phone' => array(),
            'theme' => 'frontend',
            'title' => 'GPL Cart',
            'hours' => array(
                array('09:00 AM' => '05:00 PM'),
                array('09:00 AM' => '05:00 PM'),
                array('09:00 AM' => '05:00 PM'),
                array('09:00 AM' => '05:00 PM'),
                array('09:00 AM' => '05:00 PM'),
                array(),
                array(),
            ),
            'social' => array()
        );
    }

    /**
     * Adds a store to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.store.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'name' => $data['name'],
            'domain' => $data['domain'],
            'status' => !empty($data['status']),
            'scheme' => !empty($data['scheme']) ? $data['scheme'] : 'http://',
            'basepath' => !empty($data['basepath']) ? $data['basepath'] : '',
            'data' => serialize($data['data'])
        );

        $store_id = $this->db->insert('store', $values);

        $this->hook->fire('add.store.after', $data, $store_id);
        return $store_id;
    }

    /**
     * Whether the store is default
     * @param integer $store_id
     * @return boolean
     */
    public function isDefault($store_id)
    {
        return ((int) $store_id === (int) $this->getDefault());
    }

    /**
     * Returns a default store
     * @param boolean $load
     * @return array|integer
     */
    public function getDefault($load = false)
    {
        $store_id = $this->config->get('store', 1);

        if (!$load) {
            return (int) $store_id;
        }

        return $this->get($store_id);
    }

    /**
     * Updates a store
     * @param integer $store_id
     * @param array $data
     * @return boolean
     */
    public function update($store_id, array $data)
    {
        $this->hook->fire('update.store.before', $store_id, $data);

        if (empty($store_id)) {
            return false;
        }

        $values = array();

        if (!empty($data['domain'])) {
            $values['domain'] = $data['domain'];
        }

        if (!empty($data['scheme'])) {
            $values['scheme'] = $data['scheme'];
        }

        if (!empty($data['basepath'])) {
            $values['basepath'] = $data['basepath'];
        }

        if (!empty($data['name'])) {
            $values['name'] = $data['name'];
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize($data['data']);
        }

        $result = false;

        if ($values) {
            $result = $this->db->update('store', $values, array('store_id' => (int) $store_id));
        }

        $this->hook->fire('update.store.after', $store_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a store
     * @param integer $store_id
     * @return boolean
     */
    public function delete($store_id)
    {
        $this->hook->fire('delete.store.before', $store_id);

        if (empty($store_id)) {
            return false;
        }

        if (!$this->canDelete($store_id)) {
            return false;
        }

        $result = $this->db->delete('store', array('store_id' => (int) $store_id));
        $this->hook->fire('delete.store.after', $store_id, $result);
        return (bool) $result;
    }

    /**
     * Whether the store can be deleted
     * @param integer $store_id
     * @return boolean
     */
    public function canDelete($store_id)
    {
        if ($this->isDefault($store_id)) {
            return false;
        }

        $sql = '
        SELECT NOT EXISTS (SELECT store_id FROM product WHERE store_id=:store_id) AND
        NOT EXISTS (SELECT store_id FROM category_group WHERE store_id=:store_id) AND
        NOT EXISTS (SELECT store_id FROM page WHERE store_id=:store_id) AND
        NOT EXISTS (SELECT store_id FROM orders WHERE store_id=:store_id) AND
        NOT EXISTS (SELECT store_id FROM cart WHERE store_id=:store_id) AND
        NOT EXISTS (SELECT store_id FROM user WHERE store_id=:store_id)';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':store_id' => $store_id));
        return (bool) $sth->fetchColumn();
    }

    /**
     * Returns a translatable store config item
     * @param string $item
     * @param string $langcode
     * @param mixed $store
     * @return string
     */
    public function getTranslation($item, $langcode, $store = null)
    {
        $config = $this->config(null, $store);

        if (!empty($config['translation'][$langcode][$item])) {
            return $config['translation'][$langcode][$item];
        }

        if (isset($config[$item])) {
            return $config[$item];
        }

        return '';
    }

    /**
     * Returns a value from a given config item
     * @param mixed $item
     * @param mixed $store
     * @return mixed
     */
    public function config($item = null, $store = null)
    {
        if (empty($store)) {
            $store = $this->current();
        } elseif (!is_array($store)) {
            $store = $this->get((string) $store);
        }

        if (empty($store['data'])) {
            $store['data'] = $this->defaultConfig();
        }

        if (!isset($item)) {
            return $store['data'];
        }

        return array_key_exists($item, $store['data']) ? $store['data'][$item] : null;
    }

    /**
     * Returns a string containing absolute store URI
     * @param array $store
     * @return string
     */
    public function url($store)
    {
        return rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", '/');
    }

    /**
     * Returns stores email(s)
     * @param array $store
     * @param mixed $type
     * @return mixed
     */
    public function email($store, $type = true)
    {
        $emails = (array) $store['data']['email'];

        switch ($type) {
            case true:
                return reset($emails);
            case false:
                array_shift($emails);
                return $emails;
        }

        if (is_numeric($type)) {
            return isset($emails[$type]) ? $emails[$type] : '';
        }

        return $emails;
    }
}
