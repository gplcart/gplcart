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
 * Manages basic behaviors and data related to product bundles
 */
class ProductBundle
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
     * Load a bundle
     * @param int|array $condition Either a numeric product bundle ID or an array of conditions for getList()
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('product.bundle.get.before', $condition, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        if (!is_array($condition)) {
            $condition = array('product_bundle_id' => (int) $condition);
        }

        $list = $this->getList($condition);

        $result = array();
        if (is_array($list) && count($list) == 1) {
            $result = reset($list);
        }

        $this->hook->attach('product.bundle.get.after', $condition, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of bundled products
     * @param array $data
     * @param string $index
     * @return array
     */
    public function getList(array $data = array(), $index = 'product_bundle_id')
    {
        $result = &gplcart_static(gplcart_array_hash(array('product.bundle.list' => $data)));

        if (isset($result)) {
            return $result;
        }

        $conditions = array();

        $sql = 'SELECT pb.*, p.store_id'
                . ' FROM product_bundle pb'
                . ' LEFT JOIN product p ON(pb.product_id = p.product_id)'
                . ' LEFT JOIN product p2 ON(pb.item_product_id = p2.product_id)';

        if (isset($data['product_bundle_id'])) {
            $sql .= ' WHERE pb.product_bundle_id = ?';
            $conditions[] = (int) $data['product_bundle_id'];
        } else {
            $sql .= ' WHERE pb.product_bundle_id IS NOT NULL';
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id = ? AND p2.store_id = ?';
            $conditions[] = (int) $data['store_id'];
            $conditions[] = (int) $data['store_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ? AND p2.status = ?';
            $conditions[] = (int) $data['status'];
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['product_id'])) {
            $sql .= ' AND pb.product_id = ?';
            $conditions[] = (int) $data['product_id'];
        }

        $result = $this->db->fetchAll($sql, $conditions, array('index' => $index));
        $this->hook->attach('product.bundle.list', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Adds a product bundle item
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.bundle.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('product_bundle', $data);
        $this->hook->attach('product.bundle.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Delete a product bundle item
     * @param int|array $condition Either a numeric product bundle ID or an array of conditions
     * @return boolean
     */
    public function delete($condition)
    {
        $result = null;
        $this->hook->attach('product.bundle.delete.before', $condition, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('product_bundle_id' => (int) $condition);
        }

        $result = (bool) $this->db->delete('product_bundle', $condition);
        $this->hook->attach('product.bundle.delete.after', $condition, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete old and add new bundled items for the product
     * @param int $product_id
     * @param array $products
     * @return bool
     */
    public function set($product_id, array $products)
    {
        $this->delete(array('product_id' => $product_id));

        $added = $count = 0;
        foreach ($products as $product) {

            $count++;

            $data = array(
                'product_id' => $product_id,
                'item_product_id' => $product['product_id']
            );

            if ($this->add($data)) {
                $added++;
            }
        }

        return $count && $added == $count;
    }

    /**
     * Returns an array of bundled product IDs for the product
     * @param int $product_id
     * @return array
     */
    public function getItems($product_id)
    {
        $product_ids = array();
        foreach ($this->getList(array('product_id' => $product_id)) as $item) {
            $product_ids[] = $item['item_product_id'];
        }

        return $product_ids;
    }

}
