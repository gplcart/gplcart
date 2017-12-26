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
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Image as ImageTrait,
    gplcart\core\traits\Alias as AliasTrait,
    gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to product categories
 */
class Category
{

    use ImageTrait,
        AliasTrait,
        TranslationTrait;

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
     * Alias model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

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
     * @param AliasModel $alias
     * @param FileModel $file
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, AliasModel $alias, FileModel $file,
            TranslationModel $translation, TranslationEntityModel $translation_entity)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();

        $this->file = $file;
        $this->alias = $alias;
        $this->translation = $translation;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Loads a category from the database
     * @param int|array|string $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('category.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!is_array($condition)) {
            $condition = array('category_id' => (int) $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('category.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of categories
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('category.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT c.*, a.alias, cg.type, cg.store_id,'
                . 'ct.language,'
                . 'COALESCE(NULLIF(ct.title, ""), c.title) AS title,'
                . 'COALESCE(NULLIF(ct.meta_title, ""), c.meta_title) AS meta_title,'
                . 'COALESCE(NULLIF(ct.meta_description, ""), c.meta_description) AS meta_description,'
                . 'COALESCE(NULLIF(ct.description_1, ""), c.description_1) AS description_1,'
                . 'COALESCE(NULLIF(ct.description_2, ""), c.description_2) AS description_2';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(c.category_id)';
        }

        $sql .= ' FROM category c'
                . ' LEFT JOIN alias a ON(a.entity=? AND a.entity_id=c.category_id)'
                . ' LEFT JOIN category_group cg ON(cg.category_group_id = c.category_group_id)'
                . ' LEFT JOIN category_translation ct ON(c.category_id = ct.category_id AND ct.language = ?)';

        $conditions = array('category', $options['language']);

        if (isset($options['category_id'])) {
            $sql .= ' WHERE c.category_id=?';
            $conditions[] = (int) $options['category_id'];
        } else {
            $sql .= ' WHERE c.category_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (c.title LIKE ? OR (ct.title LIKE ? AND ct.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['category_group_id'])) {
            $sql .= ' AND c.category_group_id=?';
            $conditions[] = (int) $options['category_group_id'];
        }

        if (isset($options['type'])) {
            $sql .= ' AND cg.type=?';
            $conditions[] = $options['type'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND cg.store_id=?';
            $conditions[] = (int) $options['store_id'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND c.status=?';
            $conditions[] = (int) $options['status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'category_id', 'weight', 'status');

        if (isset($options['sort']) && in_array($options['sort'], $allowed_sort)//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY c.{$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY c.weight ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'category_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('category.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Returns an array of categories with parent categories set
     * @param array $options
     * @return array
     */
    public function getTree(array $options)
    {
        $tree = &gplcart_static(gplcart_array_hash(array('category.tree' => $options)));

        if (isset($tree)) {
            return $tree;
        }

        $list = (array) $this->getList($options);
        $tree = $this->prepareTree($list, $options);
        $this->hook->attach('category.tree', $tree, $this);
        return $tree;
    }

    /**
     * Create tree structure from an array of categories
     * @param array $categories
     * @param array $options
     * @return array
     */
    protected function prepareTree(array $categories, array $options)
    {
        $tree = array();
        $parents_tree = array();
        $children_tree = array();
        $categories_tree = array();

        $parent = isset($options['parent_id']) ? (int) $options['parent_id'] : 0;

        foreach ($categories as $category) {
            $children_tree[$category['parent_id']][] = $category['category_id'];
            $parents_tree[$category['category_id']][] = $category['parent_id'];
            $categories_tree[$category['category_id']] = $category;
        }

        $max_depth = isset($options['depth']) ? (int) $options['depth'] : count($children_tree);

        $process_parents = array();
        $process_parents[] = $parent;

        while (count($process_parents)) {
            $parent = array_pop($process_parents);
            $depth = count($process_parents);

            if ($max_depth <= $depth || empty($children_tree[$parent])) {
                continue;
            }

            $has_children = false;

            $child = current($children_tree[$parent]);

            do {
                if (empty($child)) {
                    break;
                }

                $category = $categories_tree[$child];

                $category['depth'] = $depth;
                $category['parents'] = $parents_tree[$category['category_id']];

                $tree[$category['category_id']] = $category;

                if (!empty($children_tree[$category['category_id']])) {
                    $has_children = true;
                    $process_parents[] = $parent;
                    $process_parents[] = $category['category_id'];
                    reset($categories_tree[$category['category_id']]);
                    next($children_tree[$parent]);
                    break;
                }
            } while ($child = next($children_tree[$parent]));

            if (!$has_children) {
                reset($children_tree[$parent]);
            }
        }

        return $tree;
    }

    /**
     * Adds a category
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('category.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['category_id'] = $this->db->insert('category', $data);

        $this->setTranslations($data, $this->translation_entity, 'category', false);
        $this->setImages($data, $this->file, 'category');
        $this->setAlias($data, $this->alias, 'category', false);

        $this->hook->attach('category.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a category
     * @param integer $category_id
     * @param array $data
     * @return boolean
     */
    public function update($category_id, array $data)
    {
        $result = null;
        $this->hook->attach('category.update.before', $category_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $updated = $this->db->update('category', $data, array('category_id' => $category_id));

        $data['category_id'] = $category_id;

        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'category');
        $updated += (int) $this->setImages($data, $this->file, 'category');
        $updated += (int) $this->setAlias($data, $this->alias, 'category');

        $result = $updated > 0;
        $this->hook->attach('category.update.after', $category_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a category
     * @param integer $category_id
     * @param bool $check
     * @return boolean
     */
    public function delete($category_id, $check = true)
    {
        $result = null;
        $this->hook->attach('category.delete.before', $category_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($category_id)) {
            return false;
        }

        $this->deleteLinked($category_id);

        $result = true;
        $this->hook->attach('category.delete.after', $category_id, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete all database records related to the category ID
     * @param string $category_id
     */
    protected function deleteLinked($category_id)
    {
        $conditions = array('category_id' => $category_id);
        $conditions2 = array('entity' => 'category', 'entity_id' => $category_id);

        $this->db->delete('category', $conditions);
        $this->db->delete('category_translation', $conditions);

        $this->db->delete('file', $conditions2);
        $this->db->delete('alias', $conditions2);
    }

    /**
     * Whether a category can be deleted
     * @param integer $category_id
     * @return boolean
     */
    public function canDelete($category_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT product_id FROM product WHERE category_id=:id)'
                . ' AND NOT EXISTS (SELECT product_id FROM product WHERE brand_category_id=:id)'
                . ' AND NOT EXISTS (SELECT page_id FROM page WHERE category_id=:id)'
                . ' AND NOT EXISTS (SELECT category_id FROM category WHERE parent_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => $category_id));
    }

    /**
     * Returns a relative/absolute path for uploaded images
     * @param boolean $absolute
     * @return string
     */
    public function getImagePath($absolute = false)
    {
        $dirname = $this->config->get('category_image_dirname', 'category');

        if ($absolute) {
            return gplcart_path_absolute($dirname, GC_DIR_IMAGE);
        }

        return gplcart_path_relative(GC_DIR_IMAGE, GC_DIR_FILE) . "/$dirname";
    }

}
