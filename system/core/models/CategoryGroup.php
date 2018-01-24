<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\interfaces\Crud as CrudInterface;
use gplcart\core\models\Translation as TranslationModel;
use gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to category groups
 */
class CategoryGroup implements CrudInterface
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
     * Load a category group from the database
     * @param int|array $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('category.group.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!is_array($condition)) {
            $condition = array('category_group_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);

        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('category.group.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of category groups
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('category.group.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT cg.*, COALESCE(NULLIF(cgt.title, ""), cg.title) AS title';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(cg.category_group_id)';
        }

        $sql .= ' FROM category_group cg
                  LEFT JOIN category_group_translation cgt
                  ON(cg.category_group_id = cgt.category_group_id AND cgt.language = ?)';

        $conditions = array($options['language']);

        if (isset($options['category_group_id'])) {
            $sql .= ' WHERE cg.category_group_id = ?';
            $conditions[] = $options['category_group_id'];
        } else {
            $sql .= ' WHERE cg.category_group_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (cg.title LIKE ? OR (cgt.title LIKE ? AND cgt.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['type'])) {
            $sql .= ' AND cg.type = ?';
            $conditions[] = $options['type'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND cg.store_id = ?';
            $conditions[] = $options['store_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'store_id', 'title', 'category_group_id');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY cg.{$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY cg.title ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'category_group_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('category.group.list.after', $options, $result, $this);
        return $result;
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

        $this->setTranslations($data, $this->translation_entity, 'category_group', false);

        $this->hook->attach('category.group.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a category group
     * @param integer $category_group_id
     * @param bool $check
     * @return boolean
     */
    public function delete($category_group_id, $check = true)
    {
        $result = null;
        $this->hook->attach('category.group.delete.before', $category_group_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($category_group_id)) {
            return false;
        }

        $result = $this->db->delete('category_group', array('category_group_id' => $category_group_id));

        if ($result) {
            $this->deleteLinked($category_group_id);
        }

        $this->hook->attach('category.group.delete.after', $category_group_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete all database records related to the category group ID
     * @param int $category_group_id
     */
    protected function deleteLinked($category_group_id)
    {
        $this->db->delete('category_group_translation', array('category_group_id' => $category_group_id));
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

        $updated = $this->db->update('category_group', $data, array('category_group_id' => $category_group_id));
        $data['category_group_id'] = $category_group_id;
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'category_group');

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
        $types = array(
            'brand' => $this->translation->text('Brand'),
            'common' => $this->translation->text('Common'),
            'catalog' => $this->translation->text('Catalog')
        );

        $this->hook->attach('category.group.types', $types, $this);
        return $types;
    }

}
