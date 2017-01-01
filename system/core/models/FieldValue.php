<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Image as ImageModel;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to field values
 */
class FieldValue extends Model
{

    /**
     * Image model instance
     * @var \gplcart\core\models\Image $image
     */
    protected $image;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ImageModel $image
     * @param LanguageModel $language
     */
    public function __construct(ImageModel $image, LanguageModel $language)
    {
        parent::__construct();

        $this->image = $image;
        $this->language = $language;
    }

    /**
     * Returns an array of values for a given field
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT fv.*, COALESCE(NULLIF(fvt.title, ""), fv.title) AS title, f.path';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(fv.field_value_id)';
        }

        $sql .= ' FROM field_value fv'
                . ' LEFT JOIN file f ON(fv.field_value_id = f.id_value AND f.id_key = ?)'
                . ' LEFT JOIN field_value_translation fvt ON(fv.field_value_id = fvt.field_value_id AND fvt.language=?)'
                . ' WHERE fv.field_value_id > 0';

        $language = $this->language->current();
        $where = array('field_value_id', $language);

        if (isset($data['title'])) {
            $sql .= ' AND (fv.title LIKE ? OR (fvt.title LIKE ? AND fvt.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (!empty($data['field_id'])) {
            $field_ids = (array) $data['field_id'];
            $placeholders = rtrim(str_repeat('?, ', count($field_ids)), ', ');
            $sql .= " AND fv.field_id IN($placeholders)";
            $where = array_merge($where, $field_ids);
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title' => 'fv.title', 'weight' => 'fv.weight',
            'color' => 'fv.color', 'image' => 'f.file_id', 'field_value_id' => 'fv.field_value_id');

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= ' ORDER BY fv.weight ASC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'field_value_id'));

        $this->hook->fire('field.value.list', $data, $list);
        return $list;
    }

    /**
     * Returns a field value
     * @param integer $field_value_id
     * @param null|string $language
     * @return array
     */
    public function get($field_value_id, $language = null)
    {
        $this->hook->fire('get.field.value.before', $field_value_id, $language);

        $sql = 'SELECT fv.*, f.path, f.file_id, f.path'
                . ' FROM field_value fv'
                . ' LEFT JOIN file f ON(fv.file_id = f.file_id)'
                . ' WHERE fv.field_value_id=?';

        $field_value = $this->db->fetch($sql, array($field_value_id));
        $this->attachTranslation($field_value, $language);

        $this->hook->fire('get.field.value.after', $field_value);
        return $field_value;
    }

    /**
     * Adds translations to the field value
     * @param array $field_value
     * @param null|string $language
     */
    protected function attachTranslation(array &$field_value, $language)
    {
        if (empty($field_value)) {
            return;
        }

        $field_value['language'] = 'und';
        $translations = $this->getTranslation($field_value['field_value_id']);

        foreach ($translations as $translation) {
            $field_value['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($field_value['translation'][$language])) {
            $field_value = $field_value['translation'][$language] + $field_value;
        }
    }

    /**
     * Returns an array of field value translations
     * @param integer $field_value_id
     * @return array
     */
    public function getTranslation($field_value_id)
    {
        $sql = 'SELECT *'
                . ' FROM field_value_translation'
                . ' WHERE field_value_id=?';

        return $this->db->fetchAll($sql, array($field_value_id));
    }

    /**
     * Adds a field value
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.field.value.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['field_value_id'] = $this->db->insert('field_value', $data);

        $this->setFile($data, false);
        $this->setTranslation($data, false);

        $this->hook->fire('add.field.value.after', $data);
        return $data['field_value_id'];
    }

    /**
     * Deletes and/or adds field value translations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslation(array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($data['field_value_id']);
        } 
        
        if (empty($data['translation'])) {
            return false;
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['field_value_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes field value translation(s)
     * @param integer $field_value_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($field_value_id, $language = null)
    {
        $conditions = array('field_value_id' => (int) $field_value_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool) $this->db->delete('field_value_translation', $conditions);
    }

    /**
     * Adds a translation to the field value
     * @param integer $field_value_id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($field_value_id, $language,
            array $translation)
    {
        $translation += array(
            'language' => $language,
            'field_value_id' => $field_value_id
        );

        return $this->db->insert('field_value_translation', $translation);
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

        if ($delete) {

            $conditions = array(
                'id_key' => 'field_value_id',
                'id_value' => $data['field_value_id']
            );

            $this->db->delete('file', $conditions);
        }

        $conditions = array(
            'path' => $data['path'],
            'id_key' => 'field_value_id',
            'id_value' => $data['field_value_id']
        );

        $file_id = $this->image->add($conditions);
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
        $this->hook->fire('update.field.value.before', $field_value_id, $data);

        if (empty($field_value_id) || empty($data)) {
            return false;
        }

        $conditions = array('field_value_id' => $field_value_id);
        $updated = $this->db->update('field_value', $data, $conditions);

        $data['field_value_id'] = $field_value_id;

        $updated += (int) $this->setFile($data);
        $updated += (int) $this->setTranslation($data);

        $result = ($updated > 0);

        $this->hook->fire('update.field.value.after', $field_value_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a field value
     * @param integer $field_value_id
     * @return boolean
     */
    public function delete($field_value_id)
    {
        $this->hook->fire('delete.field.value.before', $field_value_id);

        if (empty($field_value_id)) {
            return false;
        }

        if (!$this->canDelete($field_value_id)) {
            return false;
        }

        $conditions = array('field_value_id' => $field_value_id);
        $conditions2 = array('id_key' => 'field_value_id', 'id_value' => $field_value_id);

        $deleted = (bool) $this->db->delete('field_value', $conditions);

        if ($deleted) {
            $this->db->delete('file', $conditions2);
            $this->db->delete('product_field', $conditions);
            $this->db->delete('field_value_translation', $conditions);
        }

        $this->hook->fire('delete.field.value.after', $field_value_id, $deleted);
        return (bool) $deleted;
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

}
