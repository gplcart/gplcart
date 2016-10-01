<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to category groups
 */
class CategoryGroup extends Model
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
     * Load a category group from the database
     * @param integer $group_id
     * @param null|string $language
     * @return array
     */
    public function get($group_id, $language = null)
    {
        $this->hook->fire('get.category.group.before', $group_id);

        $sql = 'SELECT * FROM category_group WHERE category_group_id=?';

        $group = $this->db->fetch($sql, array($group_id));
        $this->attachTranslation($group, $language);

        $this->hook->fire('get.category.group.after', $group);
        return $group;
    }

    /**
     * Adds translations to the category group
     * @param array $category_group
     * @param null|string $language
     */
    protected function attachTranslation(array &$category_group, $language)
    {
        if (empty($category_group)) {
            return;
        }

        $category_group['language'] = 'und';

        $translations = $this->getTranslation($category_group['category_group_id']);

        foreach ($translations as $translation) {
            $category_group['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($category_group['translation'][$language])) {
            $category_group = $category_group['translation'][$language] + $category_group;
        }
    }

    /**
     * Returns an array of category group translations
     * @param integer $category_group_id
     * @return array
     */
    public function getTranslation($category_group_id)
    {
        $sql = 'SELECT *'
            . ' FROM category_group_translation'
            . ' WHERE category_group_id=?';

        return $this->db->fetchAll($sql, array($category_group_id));
    }

    /**
     * Returns an array of category groups
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT cg.*, COALESCE(NULLIF(cgt.title, ""), cg.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(cg.category_group_id)';
        }

        $sql .= ' FROM category_group cg'
            . ' LEFT JOIN category_group_translation cgt'
            . ' ON(cg.category_group_id = cgt.category_group_id AND cgt.language = ?)'
            . ' WHERE cg.category_group_id > 0';

        $language = $this->language->current();
        $where = array($language);

        if (isset($data['title'])) {
            $sql .= ' AND (cg.title LIKE ? OR (cgt.title LIKE ? AND cgt.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['type'])) {
            $sql .= ' AND cg.type = ?';
            $where[] = $data['type'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND cg.store_id = ?';
            $where[] = (int)$data['store_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'store_id', 'title');

        if ((isset($data['sort']) && in_array($data['sort'],
                    $allowed_sort)) && (isset($data['order']) && in_array($data['order'], $allowed_order))
        ) {
            $sql .= " ORDER BY cg.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY cg.title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int)$this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'category_group_id');
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('category.groups', $list);
        return $list;
    }

    /**
     * Adds a category group
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.category.group.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['category_group_id'] = $this->db->insert('category_group', $data);

        $this->setTranslation($data, false);

        $this->hook->fire('add.category.group.after', $data);
        return $data['category_group_id'];
    }

    /**
     * Deletes and/or adds category group translations
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
            $this->deleteTranslation($data['category_group_id']);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['category_group_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes category group translations
     * @param integer $category_group_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($category_group_id, $language = null)
    {
        $conditions = array('category_group_id' => $category_group_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool)$this->db->delete('category_group_translation', $conditions);
    }

    /**
     * Adds a category group translation
     * @param integer $id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($id, $language, array $translation)
    {
        $translation += array(
            'language' => $language,
            'category_group_id' => $id
        );

        return $this->db->insert('category_group_translation', $translation);
    }

    /**
     * Deletes a category group
     * @param integer $category_group_id
     * @return boolean
     */
    public function delete($category_group_id)
    {
        $this->hook->fire('delete.category.group.before', $category_group_id);

        if (empty($category_group_id)) {
            return false;
        }

        if (!$this->canDelete($category_group_id)) {
            return false;
        }

        $conditions = array(
            'category_group_id' => (int)$category_group_id
        );

        $this->db->delete('category_group', $conditions);
        $this->db->delete('category_group_translation', $conditions);

        $this->hook->fire('delete.category.group.after', $category_group_id);
        return true;
    }

    /**
     * Returns true if the category group can be deleted
     * @param integer $category_group_id
     * @return boolean
     */
    public function canDelete($category_group_id)
    {
        $sql = 'SELECT category_id FROM category WHERE category_group_id=?';
        $result = $this->db->fetchColumn($sql, array($category_group_id));

        return empty($result);
    }

    /**
     * Updates a category group
     * @param integer $category_group_id
     * @param array $data
     * @return boolean
     */
    public function update($category_group_id, array $data)
    {
        $this->hook->fire('update.category.group.before', $category_group_id, $data);

        if (empty($category_group_id)) {
            return false;
        }

        $conditions = array('category_group_id' => $category_group_id);

        $updated = (int)$this->db->update('category_group', $data, $conditions);
        $updated += (int)$this->setTranslation($data);

        $result = ($updated > 0);

        $this->hook->fire('update.category.group.after', $category_group_id, $data, $result);
        return $result;
    }

}
