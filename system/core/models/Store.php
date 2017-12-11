<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\helpers\Request as RequestHelper;

/**
 * Manages basic behaviors and data related to stores
 */
class Store
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
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param RequestHelper $request
     */
    public function __construct(Hook $hook, Database $db, Config $config, RequestHelper $request)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;
        $this->request = $request;
    }

    /**
     * Loads a store
     * @param integer|string $store Either store ID or domain
     * @return array
     */
    public function get($store)
    {
        if (!$this->db->isInitialized()) {
            return array();
        }

        $result = &gplcart_static("store.get.$store");

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('store.get.before', $store, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $conditions = array();
        if (is_numeric($store)) {
            $conditions['store_id'] = $store;
        } else if (strpos($store, '/') === false) {
            $conditions['domain'] = $store;
        } else {
            list($domain, $basepath) = explode('/', $store, 2);
            $conditions['domain'] = $domain;
            $conditions['basepath'] = $basepath;
        }

        $result = $this->getList($conditions);

        if (!empty($result)) {
            $result = reset($result);
            $result['data'] += $this->defaultConfig();
        }

        $this->hook->attach('store.get.after', $store, $result, $this);
        return $result;
    }

    /**
     * Returns an array of stores or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $stores = &gplcart_static(gplcart_array_hash(array('store.list' => $data)));

        if (isset($stores)) {
            return $stores;
        }

        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(store_id)';
        }

        $sql .= ' FROM store';

        $conditions = array();

        if (isset($data['store_id'])) {
            $sql .= ' WHERE store_id = ?';
            $conditions[] = $data['store_id'];
        } else {
            $sql .= ' WHERE store_id IS NOT NULL';
        }

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $conditions[] = "%{$data['name']}%";
        }

        if (isset($data['domain'])) {
            $sql .= ' AND domain = ?';
            $conditions[] = $data['domain'];
        }

        if (isset($data['basepath'])) {
            $sql .= ' AND basepath = ?';
            $conditions[] = $data['basepath'];
        }

        if (isset($data['domain_like'])) {
            $sql .= ' AND domain LIKE ?';
            $conditions[] = "%{$data['domain_like']}%";
        }

        if (isset($data['basepath_like'])) {
            $sql .= ' AND basepath LIKE ?';
            $conditions[] = "%{$data['basepath_like']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $conditions[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'domain', 'basepath', 'status', 'created', 'modified', 'store_id');

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
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $options = array('unserialize' => 'data', 'index' => 'store_id');
        $stores = $this->db->fetchAll($sql, $conditions, $options);

        $this->hook->attach('store.list', $stores, $this);
        return $stores;
    }

    /**
     * Returns the current store
     * @return array
     */
    public function getCurrent()
    {
        $domain = $this->request->host();
        $basepath = trim($this->request->base(true), '/');

        if ($basepath !== '') {
            $domain .= "/$basepath";
        }

        return $this->get($domain);
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
            'meta_title' => 'GPLCart',
            'meta_description' => '',
            'owner' => '',
            'phone' => array(),
            'theme' => 'frontend',
            'title' => 'GPLCart',
            'collection_file' => 1,
            'collection_product' => 2,
            'collection_page' => 3,
            'blog_category_group_id' => 0,
            'js' => ''
        );
    }

    /**
     * Adds a store to the database
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('store.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $this->db->insert('store', $data);

        $this->hook->attach('store.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Whether the store is default
     * @param integer $store_id
     * @return boolean
     */
    public function isDefault($store_id)
    {
        return $store_id == $this->getDefault();
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
        $result = null;
        $this->hook->attach('store.update.before', $store_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $result = (bool) $this->db->update('store', $data, array('store_id' => $store_id));

        $this->hook->attach('store.update.after', $store_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a store
     * @param integer $store_id
     * @param bool $check
     * @return boolean
     */
    public function delete($store_id, $check = true)
    {
        $result = null;
        $this->hook->attach('store.delete.before', $store_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($store_id)) {
            return false;
        }

        $conditions = array('store_id' => $store_id);
        $result = (bool) $this->db->delete('store', $conditions);

        if ($result) {
            $this->db->delete('triggers', $conditions);
            $this->db->delete('wishlist', $conditions);
            $this->db->delete('collection', $conditions);
            $this->db->delete('product_sku', $conditions);
        }

        $this->hook->attach('store.delete.after', $store_id, $check, $result, $this);
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
     * Returns a translatable store configuration item
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
            $store = $this->getCurrent();
        } elseif (!is_array($store)) {
            $store = $this->get((string) $store);
        }

        if (empty($store['data'])) {
            $store['data'] = $this->defaultConfig();
        }

        if (!isset($item)) {
            return $store['data'];
        }

        return gplcart_array_get($store['data'], $item);
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

}
