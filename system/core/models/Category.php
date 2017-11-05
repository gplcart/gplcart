<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\traits\Image as ImageTrait,
    gplcart\core\traits\Alias as AliasTrait;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel;

/**
 * Manages basic behaviors and data related to product categories
 */
class Category
{

    use ImageTrait,
        AliasTrait;

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
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Alias model instance
     * @var \gplcart\core\models\Alias $alias
     */
    protected $alias;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param AliasModel $alias
     * @param FileModel $file
     * @param LanguageModel $language
     * @param CategoryGroupModel $category_group
     */
    public function __construct(Hook $hook, Database $db, Config $config, AliasModel $alias,
            FileModel $file, LanguageModel $language, CategoryGroupModel $category_group)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;

        $this->file = $file;
        $this->alias = $alias;
        $this->language = $language;
        $this->category_group = $category_group;
    }

    /**
     * Loads a category from the database
     * @param integer $category_id
     * @param string|null $language
     * @param string|null $store_id
     * @return array
     */
    public function get($category_id, $language = null, $store_id = null)
    {
        $result = null;
        $this->hook->attach('category.get.before', $category_id, $language, $store_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $conditions = array($category_id);

        $sql = 'SELECT c.*, cg.store_id, u.role_id'
                . ' FROM category c'
                . ' LEFT JOIN category_group cg ON(c.category_group_id=cg.category_group_id)'
                . ' LEFT JOIN user u ON(c.user_id=u.user_id)'
                . ' WHERE c.category_id=?';

        if (isset($store_id)) {
            $sql .= ' AND cg.store_id=?';
            $conditions[] = $store_id;
        }

        $category = $this->db->fetch($sql, $conditions);

        $this->attachTranslationTrait($this->db, $category, 'category', $language);
        $this->attachImagesTrait($this->db, $this->file, $category, 'category', $language);

        $this->hook->attach('category.get.after', $category, $language, $store_id, $this);
        return $category;
    }

    /**
     * Returns a list of categories per store to use directly in <select>
     * @param integer $store_id
     * @param string|null $usage
     * @return array
     */
    public function getOptionListByStore($store_id, $usage = null)
    {
        $conditions = array(
            'type' => $usage,
            'store_id' => $store_id
        );

        $groups = (array) $this->category_group->getList($conditions);

        $list = array();
        foreach ($groups as $group) {
            $list[$group['title']] = $this->getOptionList(array('category_group_id' => $group['category_group_id']));
        }

        return $list;
    }

    /**
     * Returns a list of categories to use directly in <select>
     * @param array $options
     * @return array
     */
    public function getOptionList(array $options)
    {
        $options += array('status' => 1, 'parent_id' => 0, 'hierarchy' => true);
        $categories = $this->getTree($options);

        if (empty($categories)) {
            return array();
        }

        $list = array();
        foreach ($categories as $category) {
            $title = empty($options['hierarchy']) ? $category['title'] : str_repeat('— ', $category['depth']) . $category['title'];
            $list[$category['category_id']] = $title;
        }

        return $list;
    }

    /**
     * Creates a hierarchical representation of the category group
     * @param array $data
     * @return array
     */
    public function getTree(array $data)
    {
        $tree = &gplcart_static(gplcart_array_hash(array('category.tree' => $data)));

        if (isset($tree)) {
            return $tree;
        }

        $tree = array();
        $children_tree = array();
        $parents_tree = array();
        $categories_tree = array();

        $parent = isset($data['parent_id']) ? (int) $data['parent_id'] : 0;

        $sql = 'SELECT c.*, a.alias, COALESCE(NULLIF(ct.title, ""), c.title) AS title, cg.store_id'
                . ' FROM category c'
                . ' LEFT JOIN category_group cg ON(c.category_group_id = cg.category_group_id)'
                . ' LEFT JOIN category_translation ct ON(c.category_id=ct.category_id AND ct.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=c.category_id)'
                . ' WHERE c.category_id > 0';

        $language = isset($data['language']) ? $data['language'] : $this->language->getLangcode();

        $where = array($language, 'category_id');

        if (isset($data['category_group_id'])) {
            $sql .= ' AND c.category_group_id=?';
            $where[] = (int) $data['category_group_id'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND cg.store_id=?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND cg.type=?';
            $where[] = $data['type'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND c.status=?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND cg.store_id=?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND cg.type=?';
            $where[] = (string) $data['type'];
        }

        $sql .= ' ORDER BY c.weight ASC';

        $results = $this->db->fetchAll($sql, $where);

        foreach ($results as $category) {
            $children_tree[$category['parent_id']][] = $category['category_id'];
            $parents_tree[$category['category_id']][] = $category['parent_id'];
            $categories_tree[$category['category_id']] = $category;
        }

        $max_depth = isset($data['depth']) ? (int) $data['depth'] : count($children_tree);

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

        $this->hook->attach('category.tree', $tree, $this);
        return $tree;
    }

    /**
     * Returns an array of categories
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, a.alias, cg.type, cg.store_id,'
                . ' COALESCE(NULLIF(ct.title, ""), c.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(c.category_id)';
        }

        $sql .= ' FROM category c'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=c.category_id)'
                . ' LEFT JOIN category_group cg ON(cg.category_group_id = c.category_group_id)'
                . ' LEFT JOIN category_translation ct ON(c.category_id = ct.category_id AND ct.language = ?)'
                . ' WHERE c.category_id > 0';

        $language = $this->language->getLangcode();
        $where = array('category_id', $language);

        if (isset($data['title'])) {
            $sql .= ' AND (c.title LIKE ? OR (ct.title LIKE ? AND ct.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['category_group_id'])) {
            $sql .= ' AND c.category_group_id=?';
            $where[] = (int) $data['category_group_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND cg.type=?';
            $where[] = $data['type'];
        }

        $sql .= " ORDER BY ct.title DESC";

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'category_id'));

        $this->hook->attach('category.list', $list, $this);
        return $list;
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

        $this->setTranslationTrait($this->db, $data, 'category', false);
        $this->setImagesTrait($this->file, $data, 'category');
        $this->setAliasTrait($this->alias, $data, 'category', false);

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

        $updated += (int) $this->setTranslationTrait($this->db, $data, 'category');
        $updated += (int) $this->setImagesTrait($this->file, $data, 'category');
        $updated += (int) $this->setAliasTrait($this->alias, $data, 'category');

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

        $conditions = array('category_id' => $category_id);
        $conditions2 = array('id_key' => 'category_id', 'id_value' => $category_id);

        $this->db->delete('category', $conditions);
        $this->db->delete('category_translation', $conditions);

        $this->db->delete('file', $conditions2);
        $this->db->delete('alias', $conditions2);

        $result = true;
        $this->hook->attach('category.delete.after', $category_id, $check, $result, $this);
        return (bool) $result;
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

        return trim(substr(GC_DIR_IMAGE, strlen(GC_DIR_FILE)), '/') . "/$dirname";
    }

}
