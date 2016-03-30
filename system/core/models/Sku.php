<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\classes\Tool;

class Sku
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->db();
    }

    /**
     * Loads a SKU by a product ID
     * @param integer $product_id
     * @return array
     */
    public function getByProduct($product_id)
    {
        $skus = $this->getList(array('product_id' => $product_id));

        $results = array('base' => '');
        foreach ($skus as $sku) {
            if (!$sku['combination_id']) {
                $results['base'] = $sku['sku'];
                continue;
            }
            $results['combinations'][$sku['combination_id']] = $sku['sku'];
        }
        return $results;
    }

    /**
     * Returns an array of SKUs or counts their total quantity
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(product_sku_id)';
        }

        $sql .= ' FROM product_sku WHERE product_sku_id > 0';

        $where = array();

        if (isset($data['sku'])) {
            $sql .= ' AND sku LIKE ?';
            $where[] = "%{$data['sku']}%";
        }

        if (isset($data['product_id'])) {
            $sql .= ' AND product_id=?';
            $where[] = (int) $data['product_id'];
        }

        if (isset($data['combination_id'])) {
            $sql .= ' AND combination_id=?';
            $where[] = $data['combination_id'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id=?';
            $where[] = (int) $data['store_id'];
        }

        $sql .= " ORDER BY sku ASC";

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $sku) {
            $list[$sku['product_sku_id']] = $sku;
        }

        $this->hook->fire('skus', $list);
        return $list;
    }

    /**
     * Adds a SKU
     * @param string $sku
     * @param integer $product_id
     * @param integer $store_id
     * @param string $option_combination_id
     * @return boolean|integer
     */
    public function add($sku, $product_id, $store_id = 1, $option_combination_id = '')
    {
        $arguments = func_get_args();

        $this->hook->fire('add.sku.before', $arguments);

        if (empty($arguments)) {
            return false;
        }

        $values = array(
            'sku' => $sku,
            'product_id' => (int) $product_id,
            'store_id' => (int) $store_id,
            'combination_id' => $option_combination_id
        );

        $id = $this->db->insert('product_sku', $values);

        $this->hook->fire('add.sku.after', $arguments, $id);
        return $id;
    }

    /**
     * Deletes a product SKU
     * @param integer $product_id
     * @param array $options
     * @return integer
     */
    public function delete($product_id, array $options = array())
    {
        $sql = 'DELETE FROM product_sku WHERE product_id=:product_id';

        if (!empty($options['combinations'])) {
            $sql .= ' AND LENGTH(combination_id) > 0';
        }

        if (!empty($options['base'])) {
            $sql .= ' AND LENGTH(combination_id) = 0';
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':product_id' => (int) $product_id));
        return $sth->rowCount();
    }

    /**
     * Generates a SKU
     * @param string $pattern
     * @param array $placeholders
     * @param array $data
     * @return string
     */
    public function generate($pattern, array $placeholders = array(), array $data = array())
    {
        $sku = $pattern;

        if ($placeholders) {
            $sku = Tool::replacePlaceholders($pattern, $placeholders, $data);
        }

        $sku = mb_strimwidth($sku, 0, 200, 'UTF-8');

        $store_id = isset($data['store_id']) ? $data['store_id'] : null;

        if ($this->get($sku, $store_id)) {

            $counter = 0;
            do {
                $sku = $sku . '-' . $counter++;
            } while ($this->get($sku, $store_id));
        }

        return $sku;
    }

    /**
     * Loads a SKU
     * @param string $sku
     * @param integer|null $store_id
     * @param integer|null $exclude_product_id
     * @return array
     */
    public function get($sku, $store_id = null, $exclude_product_id = null)
    {
        $results = $this->getList(array('sku' => $sku, 'store_id' => $store_id));

        foreach ($results as $result) {
            if (isset($exclude_product_id) && $result['product_id'] == $exclude_product_id) {
                continue;
            }

            if ($result['sku'] === $sku) {
                return $result;
            }
        }

        return array();
    }

}
