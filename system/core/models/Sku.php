<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model as Model;
use core\helpers\String as StringHelper;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to product SKUs
 */
class Sku extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
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

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $results = $this->db->fetchAll($sql, $where, array('index' => 'product_sku_id'));

        foreach ($results as &$result) {
            $result['fields'] = $this->getFieldValues($result['combination_id']);
        }

        $this->hook->fire('sku.list', $results);
        return $results;
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

        $data['product_sku_id'] = $this->db->insert('product_sku', $data);

        $this->hook->fire('add.sku.after', $data);
        return $data['product_sku_id'];
    }

    /**
     * Deletes a product SKU
     * @param integer $product_id
     * @param array $options
     * @return boolean
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

        return (bool) $this->db->run($sql, array($product_id))->rowCount();
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
            $sku = StringHelper::replace($pattern, $placeholders, $data);
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
     * Returns an array of field value Ids from a combination ID
     * @param string $combination_id
     * @return array
     */
    public function getFieldValues($combination_id)
    {
        $field_value_ids = explode('_', substr($combination_id, strpos($combination_id, '-') + 1));
        sort($field_value_ids);

        return $field_value_ids;
    }

    /**
     * Creates a field combination id from the field value ids
     * @param array $field_value_ids
     * @param null|integer $product_id
     * @return string
     */
    public function getCombinationId(array $field_value_ids, $product_id = null)
    {
        sort($field_value_ids);

        if (!empty($product_id)) {
            return $product_id . '-' . implode('_', $field_value_ids);
        }

        return implode('_', $field_value_ids);
    }

    /**
     * 
     * @param array $product
     * @param array $field_value_ids
     * @return boolean|string
     */
    public function selectCombination(array $product, array $field_value_ids)
    {
        $this->hook->fire('select.sku.combination.before', $product, $field_value_ids);

        $response = array(
            'modal' => '',
            'cart_access' => true,
            'severity' => '',
            'combination' => array(),
            'message' => ''
        );

        if (empty($product['status'])) {
            $response['severity'] = 'danger';
            $response['message'] = $this->language->text('Unavailable product');
            return $response;
        }

        if (empty($field_value_ids)) {
            $response['severity'] = 'warning';
            $response['message'] = $this->language->text('No option selected');
            return $response;
        }

        $combination_id = $this->getCombinationId($field_value_ids, $product['product_id']);

        if (empty($product['combination'][$combination_id])) {
            $response['severity'] = 'danger';
            $response['message'] = $this->language->text('Invalid option combination');
            return $response;
        }

        $response['combination'] = $product['combination'][$combination_id];
        $response['combination']['currency'] = $product['currency'];

        if (empty($response['combination']['stock']) && $product['subtract']) {
            $response['cart_access'] = false;
            $response['severity'] = 'warning';
            $response['message'] = $this->language->text('Out of stock');
        }

        $this->hook->fire('select.sku.combination.after', $product, $field_value_ids, $response);
        return $response;
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
