<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;

/**
 * Manages basic behaviors and data related to product views
 */
class ProductView
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
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();
    }

    /**
     * Returns an array of viewed products
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(product_view_id)';
        }

        $sql .= ' FROM product_view WHERE product_view_id IS NOT NULL';

        $conditions = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND user_id = ?';
            $conditions[] = $data['user_id'];
        }

        if (isset($data['product_id'])) {
            $sql .= ' AND product_id = ?';
            $conditions[] = $data['product_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('product_id', 'user_id', 'created');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'product_view_id'));
        $this->hook->attach('product.view.list', $list, $this);
        return $list;
    }

    /**
     * Add a viewed product for a user
     * @param int $product_id
     * @param int $user_id
     * @return array
     */
    public function set($product_id, $user_id)
    {
        $list = (array) $this->getList(array('user_id' => $user_id));

        foreach ($list as $item) {
            if ($item['product_id'] == $product_id) {
                return $list;
            }
        }

        $limit = $this->getLimit();

        if (!empty($limit)) {
            $exceeding = array_slice($list, $limit - 1, null, true);
        }

        if (!empty($exceeding)) {
            foreach (array_keys($exceeding) as $product_view_id) {
                $this->delete($product_view_id);
            }
        }

        $data = array(
            'user_id' => $user_id,
            'product_id' => $product_id
        );

        $this->add($data);
        return $list;
    }

    /**
     * Returns max number of viewed products to keep in the database
     * @return int
     */
    public function getLimit()
    {
        return (int) $this->config->get('product_view_limit', 100);
    }

    /**
     * Adds a viewed product
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.view.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = GC_TIME;
        $result = $this->db->insert('product_view', $data);
        $this->hook->attach('product.view.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a viewed product
     * @param integer $product_view_id
     * @return boolean
     */
    public function delete($product_view_id)
    {
        $result = null;
        $this->hook->attach('product.view.delete.before', $product_view_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('product_view', array('product_view_id' => $product_view_id));
        $this->hook->attach('product.view.delete.after', $product_view_id, $result, $this);
        return (bool) $result;
    }

}
