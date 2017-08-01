<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to category groups
 */
class CategoryGroup extends Model
{

    use \gplcart\core\traits\TranslationTrait;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
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
        $result = null;
        $this->hook->attach('category.group.get.before', $group_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM category_group WHERE category_group_id=?';
        $result = $this->db->fetch($sql, array($group_id));

        $this->attachTranslationTrait($this->db, $result, 'category_group', $language);

        $this->hook->attach('category.group.get.after', $result, $language, $this);
        return $result;
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
        $allowed_sort = array('type', 'store_id', 'title', 'category_group_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))
        ) {
            $sql .= " ORDER BY cg.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY cg.title ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'category_group_id'));

        $this->hook->attach('category.group.list', $list, $this);
        return $list;
    }

    /**
     * Adds a category group
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('category.group.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['category_group_id'] = $this->db->insert('category_group', $data);

        $this->setTranslationTrait($this->db, $data, 'category_group', false);

        $this->hook->attach('category.group.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a category group
     * @param integer $category_group_id
     * @return boolean
     */
    public function delete($category_group_id)
    {
        $result = null;
        $this->hook->attach('category.group.delete.before', $category_group_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($category_group_id)) {
            return false;
        }

        $conditions = array('category_group_id' => $category_group_id);

        $this->db->delete('category_group', $conditions);
        $this->db->delete('category_group_translation', $conditions);

        $result = true;
        $this->hook->attach('category.group.delete.after', $category_group_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a category group can be deleted
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
        $result = null;
        $this->hook->attach('category.group.update.before', $category_group_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $conditions = array('category_group_id' => $category_group_id);
        $updated = $this->db->update('category_group', $data, $conditions);

        $data['category_group_id'] = $category_group_id;
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'category_group');

        $result = $updated > 0;

        $this->hook->attach('category.group.update.after', $category_group_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of category group types
     * @return array
     */
    public function getTypes()
    {
        $types = $this->getDefaultTypes();
        $this->hook->attach('category.group.types', $types, $this);

        return $types;
    }

    /**
     * Returns an array of default category group types
     * @return array
     */
    protected function getDefaultTypes()
    {
        return array(
            'catalog' => $this->language->text('Catalog'),
            'brand' => $this->language->text('Brand')
        );
    }

}
