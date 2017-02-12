<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;

/**
 * Manages basic behaviors and data related to product fields
 */
class ProductField extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a field to a product
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('product.field.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['product_field_id'] = $this->db->insert('product_field', $data);

        $this->hook->fire('product.field.add.after', $data);
        return $data['product_field_id'];
    }

    /**
     * Deletes product field(s)
     * @param string $type
     * @param integer $product_id
     * @return boolean
     */
    public function delete($type, $product_id)
    {
        $this->hook->fire('product.field.delete.before', $type, $product_id);

        $conditions = array('type' => $type, 'product_id' => $product_id);
        $result = (bool) $this->db->delete('product_field', $conditions);

        $this->hook->fire('product.field.delete.after', $type, $product_id, $result);
        return $result;
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

        $this->hook->fire('product.field.list', $prodict_id, $list);
        return $list;
    }

}
