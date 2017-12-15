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
 * Manages basic behaviors and data for product relations
 */
class ProductRelation
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
     * Deletes product relations
     * @param int $product_id
     * @return bool
     */
    public function delete($product_id)
    {
        $result = null;
        $this->hook->attach('product.related.delete.before', $product_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        foreach (array('product_id', 'item_product_id') as $field) {
            if (!$this->db->delete('product_related', array($field => $product_id))) {
                return false;
            }
        }

        $this->hook->attach('product.related.delete.after', $product_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Adds product relations
     * @param int $related_product_id
     * @param int $product_id
     */
    public function add($related_product_id, $product_id)
    {
        $result = null;
        $this->hook->attach('product.related.add.before', $related_product_id, $product_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $this->db->insert('product_related', array('product_id' => $product_id, 'item_product_id' => $related_product_id));
        $this->db->insert('product_related', array('product_id' => $related_product_id, 'item_product_id' => $product_id));

        $this->hook->attach('product.related.add.after', $related_product_id, $product_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of related products for the given product ID
     * @param array $data
     * @return array
     */
    public function getList(array $data)
    {
        if (empty($data['product_id'])) {
            return array();
        }

        $sql = 'SELECT item_product_id FROM product_related WHERE product_id=?';

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $list = $this->db->fetchColumnAll($sql, array($data['product_id']));
        $this->hook->attach('product.related.list', $data, $list, $this);
        return $list;
    }

}
