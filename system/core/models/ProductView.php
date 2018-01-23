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
     * @param array $options
     * @return array|int
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('product.view.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(product_view_id)';
        }

        $sql .= ' FROM product_view WHERE product_view_id IS NOT NULL';

        $conditions = array();

        if (isset($options['user_id'])) {
            $sql .= ' AND user_id = ?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['product_id'])) {
            $sql .= ' AND product_id = ?';
            $conditions[] = $options['product_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('product_id', 'user_id', 'created');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY created DESC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'product_view_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('product.view.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a viewed product
     * @param array $data
     * @return int
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
     * @param int $product_view_id
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

}
