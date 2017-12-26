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
    gplcart\core\models\FieldValue as FieldValueModel,
    gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, Config $config, FieldModel $field,
            FieldValueModel $field_value, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();

        $this->field = $field;
        $this->field_value = $field_value;
        $this->translation = $translation;
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
     * Returns an array of product classes or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('product.class.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(product_class_id)';
        }

        $sql .= ' FROM product_class WHERE product_class_id IS NOT NULL';

        $conditions = array();

        if (isset($options['title'])) {
            $sql .= ' AND title LIKE ?';
            $conditions[] = "%{$options['title']}%";
        }

        if (isset($options['status'])) {
            $sql .= ' AND status=?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'product_class_id');

        if (isset($options['sort']) && in_array($options['sort'], $allowed_sort)//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'product_class_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('product.class.list.after', $options, $result, $this);
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

        $result = (bool) $this->db->delete('product_class', array('product_class_id' => $product_class_id));

        if ($result) {
            $this->deleteLinked($product_class_id);
        }

        $this->hook->attach('product.class.delete.after', $product_class_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete all database records related to the product class ID
     * @param int $product_class_id
     */
    protected function deleteLinked($product_class_id)
    {
        $this->db->delete('product_class_field', array('product_class_id' => $product_class_id));
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
            if (isset($fields[$field['field_id']])) {
                $options = array('field_id' => $field['field_id']);
                $data = array('values' => $this->field_value->getList($options));
                $data += $fields[$field['field_id']];
                $data += $field;
                $result[$fields[$field['field_id']]['type']][$field['field_id']] = $data;
            }
        }

        return $result;
    }

    /**
     * Loads an array of product class fields
     * @param array $options
     * @return array
     */
    public function getFields(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $sql = 'SELECT pcf.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title, f.type AS type'
                . ' FROM product_class_field pcf'
                . ' LEFT JOIN field f ON(pcf.field_id = f.field_id)'
                . ' LEFT JOIN field_translation ft ON(pcf.field_id = ft.field_id AND ft.language=?)'
                . ' WHERE pcf.product_class_field_id IS NOT NULL';

        $conditions = array($options['language']);

        if (isset($options['product_class_id'])) {
            $sql .= ' AND pcf.product_class_id=?';
            $conditions[] = (int) $options['product_class_id'];
        }

        if (isset($options['type'])) {
            $sql .= ' AND f.type=?';
            $conditions[] = $options['type'];
        }

        if (isset($options['required'])) {
            $sql .= ' AND pcf.required=?';
            $conditions[] = (int) $options['required'];
        }

        if (isset($options['multiple'])) {
            $sql .= ' AND pcf.multiple=?';
            $conditions[] = (int) $options['multiple'];
        }

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'type' => 'f.type',
            'title' => 'f.title',
            'weight' => 'pcf.weight',
            'required' => 'pcf.required',
            'multiple' => 'pcf.multiple'
        );

        if (isset($options['sort']) && isset($allowed_sort[$options['sort']])//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY pcf.weight ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        return $this->db->fetchAll($sql, $conditions, array('index' => 'field_id'));
    }

}
