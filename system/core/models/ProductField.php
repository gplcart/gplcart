<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Config,
    gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to product fields
 */
class ProductField extends Model
{

    /**
     * @param Config $config
     * @param Hook $hook
     */
    public function __construct(Config $config, Hook $hook)
    {
        parent::__construct($config, $hook);
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
     * @param string $type
     * @param integer $product_id
     * @return boolean
     */
    public function delete($type, $product_id)
    {
        $result = null;
        $this->hook->attach('product.field.delete.before', $type, $product_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('type' => $type, 'product_id' => $product_id);
        $result = (bool) $this->db->delete('product_field', $conditions);

        $this->hook->attach('product.field.delete.after', $type, $product_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of fields for a given product
     * @param integer $prodict_id
     * @return array
     */
    public function getList($prodict_id)
    {
        $sql = 'SELECT * FROM product_field WHERE product_id=?';
        $fields = $this->db->fetchAll($sql, array($prodict_id));

        $list = array();
        foreach ($fields as $field) {
            $list[$field['type']][$field['field_id']][] = $field['field_value_id'];
        }

        $this->hook->attach('product.field.list', $prodict_id, $list, $this);
        return $list;
    }

}
