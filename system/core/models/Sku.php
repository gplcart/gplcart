<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Model;
use core\classes\Tool;

/**
 * Manages basic behaviors and data related to product SKUs
 */
class Sku extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
            if (empty($sku['combination_id'])) {
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
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.sku.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->db->prepareInsert('product_sku', $data);
        $data['product_sku_id'] = $this->db->insert('product_sku', $values);

        $this->hook->fire('add.sku.after', $data);
        return $data['product_sku_id'];
    }

    /**
     * Deletes a product SKU
     * @param integer $product_id
     * @param array $options
     * @return integer
     */
    public function delete($product_id, array $options = array())
    {
        $sql = 'DELETE FROM product_sku WHERE product_id=?';

        if (!empty($options['combinations'])) {
            $sql .= ' AND LENGTH(combination_id) > 0';
        }

        if (!empty($options['base'])) {
            $sql .= ' AND LENGTH(combination_id) = 0';
        }

        $sth = $this->db->prepare($sql);
        $sth->execute(array($product_id));
        return $sth->rowCount();
    }

    /**
     * Generates a SKU
     * @param string $pattern
     * @param array $placeholders
     * @param array $data
     * @return string
     */
    public function generate($pattern, array $placeholders = array(),
            array $data = array())
    {
        $sku = $pattern;

        if (!empty($placeholders)) {
            $sku = Tool::replacePlaceholders($pattern, $placeholders, $data);
        }

        $sku = mb_strimwidth($sku, 0, 200, 'UTF-8');

        $store_id = isset($data['store_id']) ? $data['store_id'] : null;
        $existing = $this->get($sku, $store_id);

        if (!empty($existing)) {
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
