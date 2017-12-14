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
use gplcart\core\models\Field as FieldModel,
    gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\FieldValue as FieldValueModel;

/**
 * Manages basic behaviors and data related to product classes
 */
class ProductClass
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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * FieldValue model instance
     * @var \gplcart\core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * ProductClass constructor.
     * @param Hook $hook
     * @param Config $config
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, Config $config,
            FieldModel $field, FieldValueModel $field_value, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();

        $this->field = $field;
        $this->translation = $translation;
        $this->field_value = $field_value;
    }

    /**
     * Returns an array of product classes or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(product_class_id)';
        }

        $sql .= ' FROM product_class WHERE product_class_id > 0';

        $where = array();

        if (isset($data['title'])) {
            $sql .= ' AND title LIKE ?';
            $where[] = "%{$data['title']}%";
        }

        if (isset($data['status'])) {
            $sql .= ' AND status=?';
            $where[] = (int) $data['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'product_class_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'product_class_id'));
        $this->hook->attach('product.class.list', $list, $this);

        return $list;
    }

    /**
     * Loads a product class
     * @param integer $product_class_id
     * @return array
     */
    public function get($product_class_id)
    {
        $result = null;
        $this->hook->attach('product.class.get.before', $product_class_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM product_class WHERE product_class_id=?';
        $result = $this->db->fetch($sql, array($product_class_id));

        $this->hook->attach('product.class.get.after', $result, $this);
        return $result;
    }

    /**
     * Adds a product class
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.class.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('product_class', $data);

        $this->hook->attach('product.class.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a product class
     * @param integer $product_class_id
     * @param bool $check
     * @return boolean
     */
    public function delete($product_class_id, $check = true)
    {
        $result = null;
        $this->hook->attach('product.class.delete.before', $product_class_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($product_class_id)) {
            return false;
        }

        $conditions = array('product_class_id' => $product_class_id);
        $result = (bool) $this->db->delete('product_class', $conditions);

        if ($result) {
            $this->db->delete('product_class_field', $conditions);
        }

        $this->hook->attach('product.class.delete.after', $product_class_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the product class can be deleted
     * @param integer $product_class_id
     * @return boolean
     */
    public function canDelete($product_class_id)
    {
        $sql = 'SELECT product_id FROM product WHERE product_class_id=?';
        $result = $this->db->fetchColumn($sql, array($product_class_id));

        return empty($result);
    }

    /**
     * Updates a product class
     * @param integer $product_class_id
     * @param array $data
     * @return boolean
     */
    public function update($product_class_id, array $data)
    {
        $result = null;
        $this->hook->attach('product.class.update.before', $product_class_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('product_class_id' => $product_class_id);
        $result = (bool) $this->db->update('product_class', $data, $conditions);

        $this->hook->attach('product.class.update.after', $product_class_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Adds a field to the product class
     * @param array $data
     * @return integer
     */
    public function addField(array $data)
    {
        $result = null;
        $this->hook->attach('product.class.add.field.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('product_class_field', $data);
        $this->hook->attach('product.class.add.field.after', $data, $result, $this);

        return (int) $result;
    }

    /**
     * Deletes product class fields
     * @param integer $product_class_id
     * @return boolean
     */
    public function deleteField($product_class_id)
    {
        $result = null;
        $this->hook->attach('product.class.delete.field.before', $product_class_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('product_class_id' => $product_class_id);
        $result = (bool) $this->db->delete('product_class_field', $conditions);

        $this->hook->attach('product.class.delete.field.after', $product_class_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of fields for a given product class
     * @param integer $product_class_id
     * @return array
     */
    public function getFieldData($product_class_id)
    {
        $result = &gplcart_static("product.class.field.data.$product_class_id");

        if (isset($result)) {
            return $result;
        }

        $result = array();
        $fields = $this->field->getList();

        foreach ($this->getFields(array('product_class_id' => $product_class_id)) as $field) {

            if (!isset($fields[$field['field_id']])) {
                continue;
            }

            $options = array('field_id' => $field['field_id']);
            $data = array('values' => $this->field_value->getList($options));

            $data += $fields[$field['field_id']];
            $data += $field;
            $result[$fields[$field['field_id']]['type']][$field['field_id']] = $data;
        }

        return $result;
    }

    /**
     * Loads an array of product class fields
     * @param array $data
     * @return array
     */
    public function getFields(array $data = array())
    {
        $sql = 'SELECT pcf.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title, f.type AS type'
                . ' FROM product_class_field pcf'
                . ' LEFT JOIN field f ON(pcf.field_id = f.field_id)'
                . ' LEFT JOIN field_translation ft ON(pcf.field_id = ft.field_id AND ft.language=?)'
                . ' WHERE pcf.product_class_field_id IS NOT NULL';

        $conditions = array($this->translation->getLangcode());

        if (isset($data['product_class_id'])) {
            $sql .= ' AND pcf.product_class_id=?';
            $conditions[] = (int) $data['product_class_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND f.type=?';
            $conditions[] = $data['type'];
        }

        if (isset($data['required'])) {
            $sql .= ' AND pcf.required=?';
            $conditions[] = (int) $data['required'];
        }

        if (isset($data['multiple'])) {
            $sql .= ' AND pcf.multiple=?';
            $conditions[] = (int) $data['multiple'];
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'type' => 'f.type',
            'title' => 'f.title',
            'required' => 'pcf.required',
            'multiple' => 'pcf.multiple',
            'weight' => 'pcf.weight'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order'])//
                && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY pcf.weight ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        return $this->db->fetchAll($sql, $conditions, array('index' => 'field_id'));
    }

}
