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
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to field values
 */
class FieldValue
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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Translation entity model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param FileModel $file
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, FileModel $file,
            TranslationModel $translation, TranslationEntityModel $translation_entity)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->file = $file;
        $this->translation = $translation;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Returns a field value
     * @param int|array $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('field.value.get.before', $condition, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        if (!is_array($condition)) {
            $condition = array('field_value_id' => (int) $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('field.value.get.after', $condition, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of values for a given field or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('field.value.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT fv.*, COALESCE(NULLIF(fvt.title, ""), fv.title) AS title, f.path';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(fv.field_value_id)';
        }

        $sql .= ' FROM field_value fv'
                . ' LEFT JOIN file f ON(fv.field_value_id = f.entity_id AND f.entity = ?)'
                . ' LEFT JOIN field_value_translation fvt ON(fv.field_value_id = fvt.field_value_id AND fvt.language=?)';

        $conditions = array('field_value', $options['language']);

        if (isset($options['field_value_id'])) {
            $sql .= ' WHERE fv.field_value_id = ?';
            $conditions[] = (int) $options['field_value_id'];
        } else {
            $sql .= ' WHERE fv.field_value_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (fv.title LIKE ? OR (fvt.title LIKE ? AND fvt.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (!empty($options['field_id'])) {
            settype($options['field_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['field_id'])), ',');
            $sql .= " AND fv.field_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['field_id']);
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title' => 'fv.title', 'weight' => 'fv.weight',
            'color' => 'fv.color', 'image' => 'f.file_id', 'field_value_id' => 'fv.field_value_id');

        if (isset($options['sort']) && isset($allowed_sort[$options['sort']])//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= ' ORDER BY fv.weight ASC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'field_value_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('field.value.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a field value
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('field.value.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['field_value_id'] = $this->db->insert('field_value', $data);

        $this->setFile($data, false);
        $this->setTranslations($data, $this->translation_entity, 'field_value', false);
        $this->hook->attach('field.value.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Adds an image to the field value
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setFile(array $data, $delete = true)
    {
        if (empty($data['path'])) {
            return false;
        }

        $conditions = array(
            'entity' => 'field_value',
            'entity_id' => $data['field_value_id']
        );

        if ($delete) {
            $this->db->delete('file', $conditions);
        }

        $conditions['path'] = $data['path'];
        $file_id = $this->file->add($conditions);

        $this->update($data['field_value_id'], array('file_id' => $file_id));
        return true;
    }

    /**
     * Updates a field value
     * @param integer $field_value_id
     * @param array $data
     * @return boolean
     */
    public function update($field_value_id, array $data)
    {
        $result = null;
        $this->hook->attach('field.value.update.before', $field_value_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $updated = $this->db->update('field_value', $data, array('field_value_id' => $field_value_id));

        $data['field_value_id'] = $field_value_id;

        $updated += (int) $this->setFile($data);
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'field_value');

        $result = $updated > 0;
        $this->hook->attach('field.value.update.after', $field_value_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a field value
     * @param integer $field_value_id
     * @param bool $check
     * @return boolean
     */
    public function delete($field_value_id, $check = true)
    {
        $result = null;
        $this->hook->attach('field.value.delete.before', $field_value_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($field_value_id)) {
            return false;
        }

        $result = (bool) $this->db->delete('field_value', array('field_value_id' => $field_value_id));

        if ($result) {
            $this->deleteLinked($field_value_id);
        }

        $this->hook->attach('field.value.delete.after', $field_value_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes all database records related to the field value ID
     * @param int $field_value_id
     */
    protected function deleteLinked($field_value_id)
    {
        $this->db->delete('product_field', array('field_value_id' => $field_value_id));
        $this->db->delete('field_value_translation', array('field_value_id' => $field_value_id));
        $this->db->delete('file', array('entity' => 'field_value', 'entity_id' => $field_value_id));
    }

    /**
     * Whether the field value can be deleted
     * @param integer $field_value_id
     * @return boolean
     */
    protected function canDelete($field_value_id)
    {
        $sql = 'SELECT c.product_id'
                . ' FROM product_field pf'
                . ' LEFT JOIN cart c ON(pf.product_id = c.product_id)'
                . ' WHERE pf.field_value_id=?';

        $result = $this->db->fetchColumn($sql, array($field_value_id));
        return empty($result);
    }

    /**
     * Returns a relative/absolute path for uploaded images
     * @param boolean $absolute
     * @return string
     */
    public function getImagePath($absolute = false)
    {
        $dirname = $this->config->get('field_value_image_dirname', 'field_value');

        if ($absolute) {
            return gplcart_path_absolute($dirname, GC_DIR_IMAGE);
        }

        return trim(substr(GC_DIR_IMAGE, strlen(GC_DIR_FILE)), '/') . "/$dirname";
    }

}
