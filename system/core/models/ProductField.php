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
 * Manages basic behaviors and data related to product fields
 */
class ProductField
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
     * Returns an array of fields for a given product
     * @param integer $prodict_id
     * @return array
     */
    public function getList($prodict_id)
    {
        $result = null;
        $this->hook->attach('product.field.list.before', $prodict_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM product_field WHERE product_id=?';
        $fields = $this->db->fetchAll($sql, array($prodict_id));

        $result = array();
        foreach ($fields as $field) {
            $result[$field['type']][$field['field_id']][] = $field['field_value_id'];
        }

        $this->hook->attach('product.field.list.after', $prodict_id, $result, $this);
        return $result;
    }

    /**
     * Adds a field to a product
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.field.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('product_field', $data);
        $this->hook->attach('product.field.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a product field(s)
     * @param int|array $condition
     * @return boolean
     */
    public function delete($condition)
    {
        $result = null;
        $this->hook->attach('product.field.delete.before', $condition, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('product_field_id' => (int) $condition);
        }

        $result = (bool) $this->db->delete('product_field', $condition);
        $this->hook->attach('product.field.delete.after', $condition, $result, $this);
        return (bool) $result;
    }

}
