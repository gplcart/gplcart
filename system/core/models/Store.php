<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\helpers\Request as RequestHelper;

/**
 * Manages basic behaviors and data related to stores
 */
class Store extends Model
{

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param RequestHelper $request
     */
    public function __construct(RequestHelper $request)
    {
        parent::__construct();

        $this->request = $request;
    }

    /**
     * Returns an array of store names
     * @return array
     */
    public function getNames()
    {
        $stores = (array) $this->getList();

        $names = array();
        foreach ($stores as $store) {
            $names[$store['store_id']] = $store['name'];
        }

        return $names;
    }

    /**
     * Returns an array of stores or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $stores = &Cache::memory(array(__METHOD__ => $data));

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

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'domain', 'basepath', 'status', 'created', 'modified');

        if (isset($data['sort'])//
                && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('unserialize' => 'data', 'index' => 'store_id');
        $stores = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('store.list', $stores);
        return $stores;
    }

    /**
     * Returns the current store ID
     * @return integer|null
     */
    public function id()
    {
        $current = $this->current();

        if (isset($current['store_id'])) {
            return (int) $current['store_id'];
        }

        return null;
    }

    /**
     * Returns the current store
     * @return array
     */
    public function current()
    {
        $domain = $this->request->host();
        $basepath = trim($this->request->base(true), '/');

        if ($basepath !== '') {
            $domain .= "/$basepath";
        }

        $store = $this->get($domain);
        return $store;
    }

    /**
     * Loads a store
     * @param integer|string $store_id Either store ID or domain
     * @return array
     */
    public function get($store_id)
    {
        if (empty($this->db)) {
            return array();
        }

        $store = &Cache::memory(__METHOD__ . $store_id);

        if (isset($store)) {
            return $store;
        }

        $this->hook->fire('store.get.before', $store_id);

        if (is_numeric($store_id)) {
            $store = $this->selectById($store_id);
        } else {
            $store = $this->selectByDomain($store_id);
        }

        if (!empty($store)) {
            $store['data'] += $this->defaultConfig();
        }

        $this->hook->fire('store.get.after', $store);
        return $store;
    }

    /**
     * Selects a store by a numeric ID
     * @param integer $store_id
     * @return array
     */
    protected function selectById($store_id)
    {
        $sql = 'SELECT * FROM store WHERE store_id=?';

        $options = array('unserialize' => 'data');
        return $this->db->fetch($sql, array($store_id), $options);
    }

    /**
     * Selects a store by a domain
     * @param string $domain
     * @return array
     */
    protected function selectByDomain($domain)
    {
        $sql = 'SELECT * FROM store WHERE domain=?';
        $conditions = array($domain);

        if (strpos($domain, '/') !== false) {
            $sql .= ' AND basepath=?';
            $conditions = explode('/', $domain, 2);
        }

        $options = array('unserialize' => 'data');
        return $this->db->fetch($sql, $conditions, $options);
    }

    /**
     * Returns an array of default store settings
     * @return array
     */
    public function defaultConfig()
    {
        return array(
            'address' => '',
            'country' => '',
            'state' => '',
            'city' => '',
            'postcode' => '',
            'anonymous_checkout' => 1,
            'email' => array(),
            'favicon' => '',
            'fax' => array(),
            'logo' => '',
            'map' => array(),
            'meta_title' => 'GPL Cart',
            'meta_description' => '',
            'owner' => '',
            'phone' => array(),
            'theme' => 'frontend',
            'title' => 'GPL Cart',
            'collection_file' => 1,
            'collection_product' => 2,
            'collection_page' => 3,
            'js' => ''
        );
    }

    /**
     * Adds a store to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('store.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $data['store_id'] = $this->db->insert('store', $data);

        $this->hook->fire('store.add.after', $data);
        return $data['store_id'];
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

        if ($load) {
            return $this->get($store_id);
        }

        return (int) $store_id;
    }

    /**
     * Updates a store
     * @param integer $store_id
     * @param array $data
     * @return boolean
     */
    public function update($store_id, array $data)
    {
        $this->hook->fire('store.update.before', $store_id, $data);

        if (empty($store_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;
        $conditions = array('store_id' => $store_id);

        $result = $this->db->update('store', $data, $conditions);

        $this->hook->fire('store.update.after', $store_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a store
     * @param integer $store_id
     * @return boolean
     */
    public function delete($store_id)
    {
        $this->hook->fire('store.delete.before', $store_id);

        if (empty($store_id)) {
            return false;
        }

        if (!$this->canDelete($store_id)) {
            return false;
        }

        $conditions = array('store_id' => $store_id);
        $result = $this->db->delete('store', $conditions);

        $this->hook->fire('store.delete.after', $store_id, $result);
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

        $sql = 'SELECT'
                . ' NOT EXISTS (SELECT store_id FROM product WHERE store_id=:id)'
                . ' AND NOT EXISTS (SELECT store_id FROM category_group WHERE store_id=:id)'
                . ' AND NOT EXISTS (SELECT store_id FROM page WHERE store_id=:id)'
                . ' AND NOT EXISTS (SELECT store_id FROM orders WHERE store_id=:id)'
                . ' AND NOT EXISTS (SELECT store_id FROM cart WHERE store_id=:id)'
                . ' AND NOT EXISTS (SELECT store_id FROM user WHERE store_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $store_id));
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

        return gplcart_array_get_value($store['data'], $item);
    }

    /**
     * Returns a string containing absolute store URI
     * @param array $store
     * @return string
     */
    public function url($store)
    {
        $scheme = $this->request->scheme();
        return rtrim("$scheme{$store['domain']}/{$store['basepath']}", '/');
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
