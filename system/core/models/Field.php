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
use gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to product fields
 */
class Field
{

    use TranslationTrait;

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
     * Translation UI model class instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Translation entity model class instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation,
            TranslationEntityModel $translation_entity)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
        $this->translation = $translation;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Returns an array of widget types
     * @return array
     */
    public function getWidgetTypes()
    {
        $types = &gplcart_static('field.widget.types');

        if (isset($types)) {
            return $types;
        }

        $types = array(
            'button' => $this->translation->text('Button'),
            'radio' => $this->translation->text('Radio buttons'),
            'select' => $this->translation->text('Dropdown list')
        );

        $this->hook->attach('field.widget.types', $types, $this);
        return $types;
    }

    /**
     * Returns an array of field types
     * @return array
     */
    public function getTypes()
    {
        $types = &gplcart_static('field.types');

        if (isset($types)) {
            return $types;
        }

        $types = array(
            'option' => $this->translation->text('Option'),
            'attribute' => $this->translation->text('Attribute')
        );

        $this->hook->attach('field.types', $types, $this);
        return $types;
    }

    /**
     * Adds a field
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('field.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['field_id'] = $this->db->insert('field', $data);

        $this->setTranslations($data, $this->translation_entity, 'field', false);

        $this->hook->attach('field.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Returns an array of fields
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT f.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.field_id)';
        }

        $sql .= ' FROM field f'
                . ' LEFT JOIN field_translation ft'
                . ' ON (f.field_id = ft.field_id AND ft.language=?)'
                . ' WHERE f.field_id > 0';

        $language = $this->translation->getLangcode();
        $where = array($language);

        if (!empty($data['field_id'])) {
            settype($data['field_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['field_id'])), ',');
            $sql .= " AND f.field_id IN($placeholders)";
            $where = array_merge($where, $data['field_id']);
        }

        if (isset($data['title'])) {
            $sql .= ' AND (f.title LIKE ? OR (ft.title LIKE ? AND ft.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['type'])) {
            $sql .= ' AND f.type=?';
            $where[] = $data['type'];
        }

        if (isset($data['widget'])) {
            $sql .= ' AND f.widget=?';
            $where[] = $data['widget'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'type', 'widget', 'field_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY f.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY f.weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'field_id'));
        $this->hook->attach('field.list', $list, $this);

        return $list;
    }

    /**
     * Loads a field from the database
     * @param integer $field_id
     * @param string|null $language
     * @return array
     */
    public function get($field_id, $language = null)
    {
        $result = null;
        $this->hook->attach('field.get.before', $field_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->db->fetch('SELECT * FROM field WHERE field_id=?', array($field_id));

        $this->attachTranslations($result, $this->translation_entity, 'field', $language);

        $this->hook->attach('field.get.after', $field_id, $language, $result, $this);
        return $result;
    }

    /**
     * Deletes a field
     * @param integer $field_id
     * @param bool $check
     * @return boolean
     */
    public function delete($field_id, $check = true)
    {
        $result = null;
        $this->hook->attach('field.delete.before', $field_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($field_id)) {
            return false;
        }

        $sql = 'DELETE fvt'
                . ' FROM field_value_translation AS fvt'
                . ' WHERE fvt.field_value_id IN (SELECT DISTINCT(fv.field_value_id)'
                . ' FROM field_value AS fv'
                . ' INNER JOIN field_value AS fv2'
                . ' ON (fv.field_value_id = fv2.field_value_id)'
                . ' WHERE fv.field_id = ?);';

        $this->db->run($sql, array($field_id));

        $conditions = array('field_id' => (int) $field_id);
        $result = (bool) $this->db->delete('field', $conditions);

        if ($result) {
            $this->db->delete('field_value', $conditions);
            $this->db->delete('field_translation', $conditions);
            $this->db->delete('product_class_field', $conditions);
        }

        $this->hook->attach('field.delete.after', $field_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether the field can be deleted
     * @param integer $field_id
     * @return boolean
     */
    public function canDelete($field_id)
    {
        $result = $this->db->fetchColumn('SELECT field_id FROM product_field WHERE field_id=?', array($field_id));
        return empty($result);
    }

    /**
     * Updates a field
     * @param integer $field_id
     * @param array $data
     * @return boolean
     */
    public function update($field_id, array $data)
    {
        $result = null;
        $this->hook->attach('field.update.before', $field_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $updated = $this->db->update('field', $data, array('field_id' => $field_id));
        $data['field_id'] = $field_id;
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'field');

        $result = $updated > 0;
        $this->hook->attach('field.update.after', $field_id, $data, $result, $this);
        return (bool) $result;
    }

}
