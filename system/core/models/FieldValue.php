<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to field values
 */
class FieldValue extends Model
{

    use TranslationTrait;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param FileModel $file
     * @param LanguageModel $language
     */
    public function __construct(FileModel $file, LanguageModel $language)
    {
        parent::__construct();

        $this->file = $file;
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

        $language = $this->language->getLangcode();
        $where = array('field_value_id', $language);

        if (isset($data['title'])) {
            $sql .= ' AND (fv.title LIKE ? OR (fvt.title LIKE ? AND fvt.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (!empty($data['field_id'])) {
            settype($data['field_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['field_id'])), ',');
            $sql .= " AND fv.field_id IN($placeholders)";
            $where = array_merge($where, $data['field_id']);
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

        $this->hook->attach('field.value.list', $data, $list, $this);
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
        $result = null;
        $this->hook->attach('field.value.get.before', $field_value_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT fv.*, f.path, f.file_id, f.path'
                . ' FROM field_value fv'
                . ' LEFT JOIN file f ON(fv.file_id = f.file_id)'
                . ' WHERE fv.field_value_id=?';

        $result = $this->db->fetch($sql, array($field_value_id));
        $this->attachTranslationTrait($this->db, $result, 'field_value', $language);

        $this->hook->attach('field.value.get.after', $field_value_id, $language, $result, $this);
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
        $this->setTranslationTrait($this->db, $data, 'field_value', false);

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

        $conditions = array('field_value_id' => $field_value_id);
        $updated = $this->db->update('field_value', $data, $conditions);

        $data['field_value_id'] = $field_value_id;

        $updated += (int) $this->setFile($data);
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'field_value');

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

        $conditions = array('field_value_id' => $field_value_id);
        $conditions2 = array('id_key' => 'field_value_id', 'id_value' => $field_value_id);

        $result = (bool) $this->db->delete('field_value', $conditions);

        if ($result) {
            $this->db->delete('file', $conditions2);
            $this->db->delete('product_field', $conditions);
            $this->db->delete('field_value_translation', $conditions);
        }

        $this->hook->attach('field.value.delete.after', $field_value_id, $check, $result, $this);
        return (bool) $result;
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
            return GC_IMAGE_DIR . "/$dirname";
        }

        return trim(substr(GC_IMAGE_DIR, strlen(GC_FILE_DIR)), '/') . "/$dirname";
    }

}
