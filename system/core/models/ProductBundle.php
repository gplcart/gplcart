<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Database;

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
     * @param Database $db
     */
    public function __construct(Hook $hook, Database $db)
    {
        $this->db = $db;
        $this->hook = $hook;
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
                'item_sku' => $product['sku'],
                'item_product_id' => $product['product_id']
            );

            if ($this->add($data)) {
                $added++;
            }
        }

        return $count && $added == $count;
    }

    /**
     * Load all bundled items for the product
     * @param int $product_id
     * @return array
     */
    public function getByProduct($product_id)
    {
        $result = null;
        $this->hook->attach('product.bundle.get.product.before', $product_id, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $sql = 'SELECT * FROM product_bundle WHERE product_id = ?';

        $result = $this->db->fetchAll($sql, array($product_id));
        $this->hook->attach('product.bundle.get.product.after', $product_id, $result, $this);
        return (array) $result;
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
