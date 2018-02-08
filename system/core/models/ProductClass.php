<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;
use gplcart\core\models\ProductClassField as ProductClassFieldModel;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to product classes
 */
class ProductClass implements CrudInterface
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
     * Product class field model instance
     * @var \gplcart\core\models\ProductClassField $product_class_field
     */
    protected $product_class_field;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param Field $field
     * @param FieldValue $field_value
     * @param ProductClassField $product_class_field
     * @param Translation $translation
     */
    public function __construct(Hook $hook, Config $config, FieldModel $field, FieldValueModel $field_value,
                                ProductClassFieldModel $product_class_field, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();

        $this->field = $field;
        $this->field_value = $field_value;
        $this->translation = $translation;
        $this->product_class_field = $product_class_field;
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

        $sql = 'SELECT pc.*';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(pc.product_class_id)';
        }

        $sql .= ' FROM product_class pc WHERE pc.product_class_id IS NOT NULL';

        $conditions = array();

        if (isset($options['title'])) {
            $sql .= ' AND pc.title LIKE ?';
            $conditions[] = "%{$options['title']}%";
        }

        if (isset($options['status'])) {
            $sql .= ' AND pc.status=?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'product_class_id');

        $sql .= ' GROUP BY pc.product_class_id';

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY pc.{$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY pc.title ASC';
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
     * Returns an array of fields for a given product class
     * @param integer $product_class_id
     * @return array
     */
    public function getFields($product_class_id)
    {
        $result = &gplcart_static("product.class.fields.$product_class_id");

        if (isset($result)) {
            return $result;
        }

        $result = array();
        $fields = $this->field->getList();

        $ops = array(
            'product_class_id' => $product_class_id
        );

        foreach ((array) $this->product_class_field->getList($ops) as $product_class_field) {
            if (isset($fields[$product_class_field['field_id']])) {

                $data = array('values' => $this->field_value->getList(array(
                    'field_id' => $product_class_field['field_id'])));

                $data += $fields[$product_class_field['field_id']];
                $data += $product_class_field;

                $result[$fields[$product_class_field['field_id']]['type']][$product_class_field['field_id']] = $data;
            }
        }

        return $result;
    }

}
