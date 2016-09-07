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
     * @param integer $category_group_id
     * @param null|string $language
     * @return array
     */
    public function get($category_group_id, $language = null)
    {
        $this->hook->fire('get.category.group.before', $category_group_id);

        $sql = 'SELECT * FROM category_group WHERE category_group_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($category_group_id));
        $category_group = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($category_group)) {
            $category_group['data'] = unserialize($category_group['data']);
            $this->setTransalation($category_group, $language);
        }

        $this->hook->fire('get.category.group.after', $category_group);
        return $category_group;
    }

    /**
     * Sets translations to the category group
     * @param array $category_group
     * @param string|null $language
     */
    protected function setTransalation(array &$category_group, $language)
    {
        $category_group['language'] = 'und';

        $sql = 'SELECT * FROM category_group_translation WHERE category_group_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($category_group['category_group_id']));
        $translations = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($translations as $translation) {
            $category_group['translation'][$translation['language']]['title'] = $translation['title'];
        }

        if (isset($language) && isset($category_group['translation'][$language])) {
            $category_group = $category_group['translation'][$language] + $category_group;
        }
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
            $where[] = (int) $data['store_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'store_id', 'title');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))
                && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY cg.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY cg.title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $group) {
            $list[$group['category_group_id']] = $group;
        }

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

        $values = array(
            'type' => $data['type'],
            'title' => $data['title'],
            'store_id' => (int) $data['store_id'],
            'data' => empty($data['data']) ? serialize(array()) : serialize((array) $data['data'])
        );

        $category_group_id = $this->db->insert('category_group', $values);

        if (!empty($data['translation'])) {
            foreach ($data['translation'] as $language => $translation) {
                $this->addTranslation($translation, $language, $category_group_id);
            }
        }

        $this->hook->fire('add.category.group.after', $data, $category_group_id);
        return $category_group_id;
    }

    /**
     * Adds a category group translation
     * @param array $translation
     * @param string $language
     * @param integer $category_group_id
     * @return integer
     */
    public function addTranslation(array $translation, $language,
            $category_group_id)
    {
        $values = array(
            'title' => $translation['title'],
            'language' => $language,
            'category_group_id' => $category_group_id
        );

        return $this->db->insert('category_group_translation', $values);
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
            'category_group_id' => (int) $category_group_id);

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

        $sth = $this->db->prepare($sql);
        $sth->execute(array($category_group_id));
        $result = $sth->fetchColumn();

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

        $values = $this->filterDbValues('category_group', $data);
        $conditions = array('category_group_id' => $category_group_id);

        if (!empty($values)) {
            $this->db->update('category_group', $values, $conditions);
        }

        if (!empty($data['translation'])) {
            $this->db->delete('category_group_translation', $conditions);
            foreach ((array) $data['translation'] as $language => $translation) {
                $this->addTranslation($translation, $language, $category_group_id);
            }
        }

        $this->hook->fire('update.category.group.after', $category_group_id, $data);
        return true;
    }

}
