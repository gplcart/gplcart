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
     * @param int $product_bundle_id
     * @return array
     */
    public function get($product_bundle_id)
    {
        $result = null;
        $this->hook->attach('product.bundle.get.before', $product_bundle_id, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $sql = 'SELECT * FROM product_bundle WHERE product_bundle_id = ?';

        $result = $this->db->fetch($sql, array($product_bundle_id));
        $this->hook->attach('product.bundle.get.after', $product_bundle_id, $result, $this);
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
     * @param integer $product_bundle_id
     * @return boolean
     */
    public function delete($product_bundle_id)
    {
        $result = null;
        $this->hook->attach('product.bundle.delete.before', $product_bundle_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('product_bundle', array('product_bundle_id' => $product_bundle_id));
        $this->hook->attach('product.bundle.delete.after', $product_bundle_id, $result, $this);
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
        $this->deleteByProduct($product_id);

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
                . ' LEFT JOIN product p2 ON(pb.item_product_id = p2.product_id)'
                . ' WHERE pb.product_bundle_id IS NOT NULL';

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
     * Load all bundled items for the product
     * @param int $product_id
     * @param array $options
     * @return array
     */
    public function getByProduct($product_id, array $options = array())
    {
        $options['product_id'] = $product_id;
        return $this->getList($options);
    }

    /**
     * Returns an array of bundled product IDs for the product
     * @param int $product_id
     * @param array $options
     * @return array
     */
    public function getBundledProducts($product_id, array $options = array())
    {
        $product_ids = array();
        foreach ($this->getByProduct($product_id, $options) as $item) {
            $product_ids[] = $item['item_product_id'];
        }

        return $product_ids;
    }

    /**
     * Delete all bundled items for the product
     * @param int $product_id
     * @return bool
     */
    public function deleteByProduct($product_id)
    {
        $deleted = $count = 0;
        foreach ($this->getByProduct($product_id) as $item) {
            $count ++;
            $deleted += (int) $this->delete($item['product_bundle_id']);
        }

        return $count && $deleted == $count;
    }

}
