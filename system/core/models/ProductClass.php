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
use core\models\Field as ModelsField;
use core\models\Language as ModelsLanguage;
use core\models\FieldValue as ModelsFieldValue;

/**
 * Manages basic behaviors and data related to product classes
 */
class ProductClass extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * FieldValue model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param ModelsField $field
     * @param ModelsFieldValue $field_value
     */
    public function __construct(ModelsLanguage $language, ModelsField $field,
            ModelsFieldValue $field_value)
    {
        parent::__construct();

        $this->field = $field;
        $this->language = $language;
        $this->field_value = $field_value;
    }

    /**
     * Returns an array of product classes
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
        $allowed_sort = array('title', 'status');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort) && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $list = array();
        foreach ($results as $class) {
            $list[$class['product_class_id']] = $class;
        }

        $this->hook->fire('get.product.class.list', $list);
        return $list;
    }

    /**
     * Loads a product class
     * @param integer $product_class_id
     * @return array
     */
    public function get($product_class_id)
    {
        $this->hook->fire('get.product.class.before', $product_class_id);

        $sql = 'SELECT * FROM product_class WHERE product_class_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($product_class_id));
        $product_class = $sth->fetch(PDO::FETCH_ASSOC);

        $this->hook->fire('get.product.class.after', $product_class_id, $product_class);
        return $product_class;
    }

    /**
     * Adds a product class
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.product.class.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->prepareDbInsert('product_class', $data);
        $data['product_class_id'] = $this->db->insert('product_class', $values);

        $this->hook->fire('add.product.class.after', $data);
        return $data['product_class_id'];
    }

    /**
     * Deletes a product class
     * @param integer $product_class_id
     * @return boolean
     */
    public function delete($product_class_id)
    {
        $this->hook->fire('delete.product.class.before', $product_class_id);

        if (empty($product_class_id)) {
            return false;
        }

        if (!$this->canDelete($product_class_id)) {
            return false;
        }

        $conditions = array('product_class_id' => $product_class_id);
        $result = $this->db->delete('product_class', $conditions);

        $this->hook->fire('delete.product.class.after', $product_class_id, $result);
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

        $sth = $this->db->prepare($sql);
        $sth->execute(array($product_class_id));

        $result = $sth->fetchColumn();
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
        $this->hook->fire('update.product.class.before', $product_class_id, $data);

        if (empty($product_class_id)) {
            return false;
        }

        $values = $this->prepareDbInsert('product_class', $data);

        $result = false;

        if (!empty($values)) {
            $conditions = array('product_class_id' => (int) $product_class_id);
            $result = $this->db->update('product_class', $values, $conditions);
        }

        $this->hook->fire('update.product.class.after', $product_class_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Adds a field to a product class
     * @param array $data
     * @return boolean|integer
     */
    public function addField(array $data)
    {
        $this->hook->fire('add.product.class.field.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->prepareDbInsert('product_class_field', $data);
        $data['product_class_field_id'] = $this->db->insert('product_class_field', $values);

        $this->hook->fire('add.product.class.field.after', $data);
        return $data['product_class_field_id'];
    }

    /**
     * Deletes product class fields
     * @param integer|null $product_class_id
     * @return boolean
     */
    public function deleteField($product_class_id)
    {
        $this->hook->fire('delete.product.class.field.before', $product_class_id);

        if (empty($product_class_id)) {
            return false;
        }

        $conditions = array('product_class_id' => (int) $product_class_id);
        $result = $this->db->delete('product_class_field', $conditions);

        $this->hook->fire('delete.product.class.field.after', $product_class_id, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of fields for a given product class
     * @param integer $product_class_id
     * @return array
     */
    public function getFieldData($product_class_id)
    {
        $return = array();
        $fields = $this->field->getList();

        foreach ($this->getFields($product_class_id) as $field) {
            if (!isset($fields[$field['field_id']])) {
                continue;
            }

            $data = array(
                'values' => $this->field_value->getList(array(
                    'field_id' => $field['field_id'])));

            $data += $fields[$field['field_id']];
            $data += $field;
            $return[$fields[$field['field_id']]['type']][$field['field_id']] = $data;
        }

        return $return;
    }

    /**
     * Loads an array of product class fields
     * @param integer $product_class_id
     * @return array
     */
    public function getFields($product_class_id)
    {
        $sql = 'SELECT pcf.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title, f.type AS type'
                . ' FROM product_class_field pcf'
                . ' LEFT JOIN field f ON(pcf.field_id = f.field_id)'
                . ' LEFT JOIN field_translation ft ON(pcf.field_id = ft.field_id AND ft.language=:language)'
                . ' WHERE product_class_id=:product_class_id'
                . ' ORDER BY weight ASC';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(
            ':language' => $this->language->current(),
            ':product_class_id' => (int) $product_class_id
        ));

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $field) {
            $list[$field['field_id']] = $field;
        }

        return $list;
    }

}
