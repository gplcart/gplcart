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
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to product class fields
 */
class ProductClassField implements CrudInterface
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
     * @param Hook $hook
     * @param Config $config
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
        $this->translation = $translation;
    }

    /**
     * Loads a product class field from the database
     * @param integer $product_class_field_id
     * @return array
     */
    public function get($product_class_field_id)
    {
        $result = null;
        $this->hook->attach('product.class.field.get.before', $product_class_field_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM product_class_field WHERE product_class_field_id=?';
        $result = $this->db->fetch($sql, array($product_class_field_id));

        $this->hook->attach('product.class.field.get.after', $result, $this);
        return $result;
    }

    /**
     * Adds a product class field
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('product.class.field.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('product_class_field', $data);
        $this->hook->attach('product.class.field.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a product class field
     * @param int|array $condition
     * @return boolean
     */
    public function delete($condition)
    {
        $result = null;
        $this->hook->attach('product.class.field.delete.before', $condition, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('product_class_field_id' => $condition);
        }

        $result = (bool) $this->db->delete('product_class_field', $condition);
        $this->hook->attach('product.class.field.delete.after', $condition, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a product class field
     * @param integer $product_class_field_id
     * @param array $data
     * @return boolean
     */
    public function update($product_class_field_id, array $data)
    {
        $result = null;
        $this->hook->attach('product.class.field.update.before', $product_class_field_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('product_class_field_id' => $product_class_field_id);
        $result = (bool) $this->db->update('product_class_field', $data, $conditions);
        $this->hook->attach('product.class.field.update.after', $product_class_field_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of product class fields or counts them
     * @param array $options
     * @return array|int
     */
    public function getList(array $options = array())
    {
        $options += array(
            'index' => 'product_class_field_id',
            'language' => $this->translation->getLangcode());

        if (empty($options['count'])) {
            $sql = 'SELECT pcf.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title, f.type AS type';
        } else {
            $sql = 'SELECT COUNT(pcf.product_class_field_id)';
        }

        $sql .= ' FROM product_class_field pcf
                  LEFT JOIN field f ON(pcf.field_id = f.field_id)
                  LEFT JOIN field_translation ft ON(pcf.field_id = ft.field_id AND ft.language=?)
                  WHERE pcf.product_class_field_id IS NOT NULL';

        $conditions = array($options['language']);

        if (isset($options['product_class_id'])) {
            $sql .= ' AND pcf.product_class_id=?';
            $conditions[] = $options['product_class_id'];
        }

        if (isset($options['field_id'])) {
            $sql .= ' AND pcf.field_id=?';
            $conditions[] = $options['field_id'];
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

        $allowed_sort = array('type' => 'f.type', 'field_id' => 'f.field_id', 'title' => 'f.title',
            'weight' => 'pcf.weight', 'required' => 'pcf.required', 'multiple' => 'pcf.multiple',
            'product_class_field_id' => 'pcf.product_class_field_id');

        if (isset($options['sort'])
            && isset($allowed_sort[$options['sort']])
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY pcf.weight ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => $options['index']));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        return $result;
    }

}
