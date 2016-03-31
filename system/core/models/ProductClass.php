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
use core\Config;
use core\models\Language;
use core\models\Field;
use core\models\FieldValue;
use core\Hook;

class ProductClass
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

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
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * FieldValue model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    public function __construct(Language $language, Field $field, FieldValue $field_value, Hook $hook, Config $config)
    {
        $this->language = $language;
        $this->field = $field;
        $this->field_value = $field_value;
        $this->hook = $hook;
        $this->db = $config->db();
    }

    /**
     * Returns an array of product classes
     * @param array $data
     * @return array
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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'title':
                    $sql .= " ORDER BY title {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY status {$data['order']}";
                    break;
            }
        } else {
            $sql .= " ORDER BY title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $class) {
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

        $sth = $this->db->prepare('SELECT * FROM product_class WHERE product_class_id=:product_class_id');
        $sth->execute(array(':product_class_id' => (int) $product_class_id));
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

        $class_id = $this->db->insert('product_class', array(
            'status' => (int) $data['status'],
            'title' => $data['title'],
        ));

        $this->hook->fire('add.product.class.after', $data, $class_id);

        return $class_id;
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

        $result = $this->db->delete('product_class', array('product_class_id' => (int) $product_class_id));
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
        $sth = $this->db->prepare('SELECT product_id FROM product WHERE product_class_id=:product_class_id');
        $sth->execute(array(':product_class_id' => $product_class_id));

        return !$sth->fetchColumn();
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

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (empty($values)) {
            return false;
        }

        $result = $this->db->update('product_class', $values, array('product_class_id' => (int) $product_class_id));
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

        $values = array(
            'product_class_id' => (int) $data['product_class_id'],
            'field_id' => (int) $data['field_id'],
            'required' => !empty($data['required']),
            'multiple' => !empty($data['multiple']),
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0,
        );

        $id = $this->db->insert('product_class_field', $values);
        $this->hook->fire('add.product.class.field.after', $data, $id);

        return $id;
    }

    /**
     * Deletes a field from a given product class
     * @param integer $product_class_field_id
     * @param integer|null $product_class_id
     * @return boolean
     */
    public function deleteField($product_class_field_id, $product_class_id = null)
    {
        $this->hook->fire('delete.product.class.field.before', $product_class_field_id, $product_class_id);

        if (empty($product_class_field_id) && empty($product_class_id)) {
            return false;
        }

        $where = array('product_class_field_id' => (int) $product_class_field_id);

        if ($product_class_id) {
            $where = array('product_class_id' => (int) $product_class_id);
        }

        $result = $this->db->delete('product_class_field', $where);
        $this->hook->fire('delete.product.class.field.after', $product_class_field_id, $product_class_id, $result);

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
        $field_list = $this->field->getList();

        foreach ($this->getFields($product_class_id) as $class_field) {
            if (!isset($field_list[$class_field['field_id']])) {
                continue;
            }

            $field_data = array(
                'values' => $this->field_value->getList(array(
                    'field_id' => $class_field['field_id'])));

            $field_data += $field_list[$class_field['field_id']];
            $field_data += $class_field;
            $return[$field_list[$class_field['field_id']]['type']][$class_field['field_id']] = $field_data;
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
        $sql = '
            SELECT pcf.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title
            FROM product_class_field pcf
            LEFT JOIN field f ON(pcf.field_id = f.field_id)
            LEFT JOIN field_translation ft ON(pcf.field_id = ft.field_id AND ft.language=:language)
            WHERE product_class_id=:product_class_id
            ORDER BY weight ASC';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(
            ':product_class_id' => (int) $product_class_id,
            ':language' => $this->language->current()
        ));

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $field) {
            $list[$field['field_id']] = $field;
        }

        return $list;
    }
}
