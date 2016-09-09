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
use core\classes\Cache;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to product fields
 */
class Field extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of widget types
     * @return array
     */
    public function widgetTypes()
    {
        $types = &Cache::memory('widget.types');

        if (isset($types)) {
            return $types;
        }

        $types = array(
            'image' => $this->language->text('Image'),
            'radio' => $this->language->text('Radio buttons'),
            'select' => $this->language->text('Dropdown list'),
            'color' => $this->language->text('Color picker')
        );

        $this->hook->fire('widget.types', $types);
        return $types;
    }

    /**
     * Adds a field
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.field.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->prepareDbInsert('field', $data);
        $data['field_id'] = $this->db->insert('field', $values);

        $this->setTranslation($data, false);

        $this->hook->fire('add.field.after', $data);
        return $data['field_id'];
    }

    /**
     * Deletes and/or adds field translations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslation(array $data, $delete = true)
    {
        if (empty($data['translation'])) {
            return false;
        }

        if ($delete) {
            $this->deleteTranslation($data['field_id']);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['field_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes a field translation(s)
     * @param integer $field_id
     * @param null|string $language
     * @return boolean
     */
    protected function deleteTranslation($field_id, $language = null)
    {
        $conditions = array('field_id' => (int) $field_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool) $this->db->delete('field_translation', $conditions);
    }

    /**
     * Adds a field translation
     * @param integer $field_id
     * @param string $language
     * @param array $translation
     * @return type
     */
    protected function addTranslation($field_id, $language, array $translation)
    {
        $translation += array(
            'language' => $language,
            'field_id' => (int) $field_id,
        );

        $values = $this->prepareDbInsert('field_translation', $translation);
        return $this->db->insert('field_translation', $values);
    }

    /**
     * Returns an array of fields
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $list = array();

        $sql = 'SELECT f.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.field_id)';
        }

        $sql .= ' FROM field f'
                . ' LEFT JOIN field_translation ft'
                . ' ON (f.field_id = ft.field_id AND ft.language=?)'
                . ' WHERE f.field_id > 0';

        $language = $this->language->current();
        $where = array($language);

        if (!empty($data['field_id'])) {
            $sql .= ' AND f.field_id IN(' . rtrim(str_repeat('?, ', count($data['field_id'])), ', ') . ')';
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
        $allowed_sort = array('title', 'type', 'widget');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort) ) && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY f.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY f.weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $field) {
            $field['data'] = unserialize($field['data']);
            $list[$field['field_id']] = $field;
        }

        $this->hook->fire('get.field.list', $list);
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
        $this->hook->fire('get.field.before', $field_id, $language);

        $sql = 'SELECT * FROM field WHERE field_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($field_id));

        $field = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($field)) {
            $field['data'] = unserialize($field['data']);
            $this->attachTranslation($field, $language);
        }

        $this->hook->fire('get.field.after', $field_id, $language, $field);
        return $field;
    }

    /**
     * Adds translations to the field
     * @param array $field
     * @param null|string $language
     */
    protected function attachTranslation(array &$field, $language)
    {
        $field['language'] = 'und';
        $translations = $this->getTranslation($field['field_id']);

        foreach ($translations as $translation) {
            $field['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($field['translation'][$language])) {
            $field = $field['translation'][$language] + $field;
        }
    }

    /**
     * Returns an array of field translations
     * @param integer $field_id
     * @return array
     */
    public function getTranslation($field_id)
    {
        $sql = 'SELECT * FROM field_translation WHERE field_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($field_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a field
     * @param integer $field_id
     * @return boolean
     */
    public function delete($field_id)
    {
        $this->hook->fire('delete.field.before', $field_id);

        if (empty($field_id)) {
            return false;
        }

        if (!$this->canDelete($field_id)) {
            return false;
        }

        $sql = 'DELETE fvt'
                . ' FROM field_value_translation AS fvt'
                . ' WHERE fvt.field_value_id IN (SELECT DISTINCT(fv.field_value_id)'
                . ' FROM field_value AS fv'
                . ' INNER JOIN field_value AS fv2'
                . ' ON (fv.field_value_id = fv2.field_value_id)'
                . ' WHERE fv.field_id = ?);';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($field_id));

        $conditions = array('field_id' => (int) $field_id);

        $this->db->delete('field', $conditions);
        $this->db->delete('field_value', $conditions);
        $this->db->delete('field_translation', $conditions);
        $this->db->delete('product_class_field', $conditions);

        $this->hook->fire('delete.field.after', $field_id);
        return true;
    }

    /**
     * Whether the field can be deleted
     * @param integer $field_id
     * @return boolean
     */
    public function canDelete($field_id)
    {
        $sql = 'SELECT field_id FROM product_field WHERE field_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($field_id));
        $result = $sth->fetchColumn();

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
        $this->hook->fire('update.field.before', $field_id, $data);

        if (empty($field_id)) {
            return false;
        }

        $values = $this->filterDbValues('field', $data);

        $updated = 0;

        if (!empty($values)) {
            $conditions = array('field_id' => (int) $field_id);
            $updated += (int) $this->db->update('field', $values, $conditions);
        }

        $data['field_id'] = $field_id;
        $updated += (int) $this->setTranslation($data);

        $result = ($updated > 0);

        $this->hook->fire('update.field.after', $field_id, $data, $result);
        return (bool) $result;
    }

}
