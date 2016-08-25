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
            'radio' => $this->language->text('Radio buttons'),
            'select' => $this->language->text('Dropdown list'),
            'image' => $this->language->text('Image'),
            'color' => $this->language->text('Color picker'));


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

        $values = array(
            'type' => $data['type'],
            'widget' => $data['widget'],
            'title' => $data['title'],
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0,
            'data' => !empty($data['data']) ? serialize((array) $data['data']) : serialize(array())
        );

        $field_id = $this->db->insert('field', $values);

        if (!empty($data['translation'])) {
            foreach ($data['translation'] as $language => $translation) {
                $this->addTranslation($translation, $language, $field_id);
            }
        }

        $this->hook->fire('add.field.after', $data, $field_id);

        return $field_id;
    }

    /**
     * Adds a field translation
     * @param array $translation
     * @param string $language
     * @param integer $field_id
     * @return integer
     */
    public function addTranslation(array $translation, $language, $field_id)
    {
        $values = array(
            'field_id' => (int) $field_id,
            'title' => $translation['title'],
            'language' => $language
        );

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

        $sql = 'SELECT f.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.field_id) ';
        }

        $sql .= '
            FROM field f
            LEFT JOIN field_translation ft ON (f.field_id = ft.field_id AND ft.language=?)
            WHERE f.field_id > 0';

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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $order = $data['order'];

            switch ($data['sort']) {
                case 'title':
                    $sql .= " ORDER BY f.title $order";
                    break;
                case 'type':
                    $sql .= " ORDER BY f.type $order";
                    break;
                case 'widget':
                    $sql .= " ORDER BY f.widget $order";
            }
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

        $sth = $this->db->prepare('SELECT * FROM field WHERE field_id=:field_id');
        $sth->execute(array(':field_id' => (int) $field_id));

        $field = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($field)) {
            $field['data'] = unserialize($field['data']);
            $field['language'] = 'und';

            $sth = $this->db->prepare('SELECT * FROM field_translation WHERE field_id=:field_id');
            $sth->execute(array(':field_id' => (int) $field_id));

            foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $translation) {
                $field['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($field['translation'][$language])) {
                $field = $field['translation'][$language] + $field;
            }
        }

        $this->hook->fire('get.field.after', $field_id, $language, $field);
        return $field;
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

        $this->db->delete('field', array('field_id' => (int) $field_id));
        $this->db->delete('field_translation', array('field_id' => (int) $field_id));
        $this->db->delete('product_class_field', array('field_id' => (int) $field_id));
        $this->db->delete('field_value', array('field_id' => (int) $field_id));
        $this->db->delete('field_value_translation', array('field_id' => (int) $field_id));

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
        $sth = $this->db->prepare('SELECT field_id FROM product_field WHERE field_id=:field_id');
        $sth->execute(array(':field_id' => (int) $field_id));
        return !$sth->fetchColumn();
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

        $values = array();

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (!empty($data['widget'])) {
            $values['widget'] = $data['widget'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        $result = false;

        if (!empty($values)) {
            $result = $this->db->update('field', $values, array('field_id' => (int) $field_id));
        }

        if (!empty($data['translation'])) {
            $this->db->delete('field_translation', array('field_id' => (int) $field_id));
            foreach ($data['translation'] as $language => $translation) {
                $this->addTranslation($translation, $language, $field_id);
            }

            $result = true;
        }

        $this->hook->fire('update.field.after', $field_id, $data, $result);
        return (bool) $result;
    }

}
