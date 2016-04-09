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
use core\Hook;
use core\Config;
use core\models\Image as ModelsImage;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to field values
 */
class FieldValue
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

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
     * Constructor
     * @param ModelsImage $image
     * @param ModelsLanguage $language
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsImage $image, ModelsLanguage $language,
                                Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->image = $image;
        $this->db = $config->getDb();
        $this->language = $language;
    }

    /**
     * Returns an array of values for a given field
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT fv.*, COALESCE(NULLIF(fvt.title, ""), fv.title) AS title, f.path';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(fv.field_value_id)';
        }

        $sql .= ' FROM field_value fv
            LEFT JOIN file f ON(fv.field_value_id = f.id_value AND f.id_key = ?)
            LEFT JOIN field_value_translation fvt ON(fv.field_value_id = fvt.field_value_id AND fvt.language=?)
            WHERE fv.field_value_id > 0';

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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc')))) {
            switch ($data['sort']) {
                case 'title':
                    $sql .= " ORDER BY fv.title {$data['order']}";
                    break;
                case 'weight':
                    $sql .= " ORDER BY fv.weight {$data['order']}";
                    break;
                case 'color':
                    $sql .= " ORDER BY fv.color {$data['order']}";
                    break;
                case 'image':
                    $sql .= " ORDER BY f.file_id {$data['order']}";
                    break;
            }
        } else {
            $sql .= ' ORDER BY fv.weight ASC';
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
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $field_value) {
            $list[$field_value['field_value_id']] = $field_value;
        }

        $this->hook->fire('get.field.value.list', $field_id, $list);
        return $list;
    }

    /**
     * Returns a field value
     * @param integer $field_value_id
     * @param string $language
     * @return array
     */
    public function get($field_value_id, $language = null)
    {
        $this->hook->fire('get.field.value.before', $field_value_id, $language);

        $sql = '
            SELECT fv.*, f.path, f.file_id, f.path
            FROM field_value fv
            LEFT JOIN file f ON(fv.field_value_id = f.id_value AND f.id_key = :id_key)
            WHERE fv.field_value_id=:field_value_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':field_value_id' => (int) $field_value_id, ':id_key' => 'field_value_id'));
        $field_value = $sth->fetch(PDO::FETCH_ASSOC);


        if ($field_value) {
            $field_value['language'] = 'und';

            $sth = $this->db->prepare('SELECT * FROM field_value_translation WHERE field_value_id=:field_value_id');
            $sth->execute(array(':field_value_id' => (int) $field_value_id));

            foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $translation) {
                $field_value['translation'][$translation['language']] = $translation;

                if (isset($language) && isset($field_value['translation'][$language])) {
                    $field_value = $field_value['translation'][$language] + $field_value;
                }
            }
        }

        $this->hook->fire('get.field.value.after', $field_value_id, $language, $field_value);
        return $field_value;
    }

    /**
     * Adds a field value
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.field.value.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'field_id' => (int) $data['field_id'],
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0,
            'color' => !empty($data['color']) ? $data['color'] : '',
            'file_id' => !empty($data['file_id']) ? $data['file_id'] : 0,
            'title' => $data['title']
        );

        $field_value_id = $this->db->insert('field_value', $values);

        if (!empty($data['translation'])) {
            foreach ($data['translation'] as $language => $translation) {
                $this->addTranslation($data['field_id'], $field_value_id, $language, $translation['title']);
            }
        }

        if (!empty($data['path'])) {
            $this->image->add(array(
                'path' => $data['path'],
                'id_key' => 'field_value_id',
                'id_value' => $field_value_id
            ));
        }

        $this->hook->fire('add.field.value.after', $data, $field_value_id);
        return $field_value_id;
    }

    /**
     * Adds a field value translation
     * @param integer $field_id
     * @param integer $field_value_id
     * @param string $language
     * @param string $title
     * @return integer
     */
    public function addTranslation($field_id, $field_value_id, $language, $title)
    {
        $values = array(
            'title' => $title,
            'language' => $language,
            'field_id' => (int) $field_id,
            'field_value_id' => (int) $field_value_id,
        );

        return $this->db->insert('field_value_translation', $values);
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

        if (empty($field_value_id)) {
            return false;
        }

        $values = array();

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (!empty($data['color'])) {
            $values['color'] = $data['color'];
        }

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        $result = false;

        if ($values) {
            $result = (bool) $this->db->update('field_value', $values, array('field_value_id' => (int) $field_value_id));
        }

        if (!empty($data['translation'])) {
            $this->db->delete('field_value_translation', array('field_value_id' => (int) $field_value_id));
            foreach ($data['translation'] as $language => $translation) {
                $this->addTranslation($data['field_id'], $field_value_id, $language, $translation['title']);
            }
            $result = true;
        }

        if (!empty($data['path'])) {
            $this->db->delete('file', array('id_key' => 'field_value_id', 'id_value' => (int) $field_value_id));
            $image = array('path' => $data['path'], 'id_value' => $field_value_id, 'id_key' => 'field_value_id');
            $values['file_id'] = $this->image->add($image);
            $result = true;
        }

        $this->hook->fire('update.field.value.after', $field_value_id, $data, $result);
        return $result;
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

        $this->db->delete('field_value', array('field_value_id' => (int) $field_value_id));
        $this->db->delete('field_value_translation', array('field_value_id' => (int) $field_value_id));
        $this->db->delete('product_field', array('field_value_id' => (int) $field_value_id));
        $this->db->delete('file', array('id_key' => 'field_value_id', 'id_value' => (int) $field_value_id));

        $this->hook->fire('delete.field.value.after', $field_value_id);
        return true;
    }

    /**
     * Whether the field value can be deleted
     * @param integer $field_value_id
     * @return boolean
     */
    protected function canDelete($field_value_id)
    {
        $sql = 'SELECT c.product_id
                FROM product_field pf
                LEFT JOIN cart c ON(pf.product_id = c.product_id)
                WHERE pf.field_value_id=:field_value_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':field_value_id' => (int) $field_value_id));

        return !$sth->fetchColumn();
    }
}
