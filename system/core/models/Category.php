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
use core\classes\Cache;
use core\models\Image as ModelsImage;
use core\models\Alias as ModelsAlias;
use core\models\Language as ModelsLanguage;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Manages basic behaviors and data related to product categories
 */
class Category
{

    /**
     * Image model instance
     * @var \core\models\Image $image
     */
    protected $image;

    /**
     * Url model instance
     * @var \core\models\Alias $alias
     */
    protected $alias;

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook model instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsLanguage $language
     * @param ModelsCategoryGroup $category_group
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(
    ModelsImage $image, ModelsAlias $alias, ModelsLanguage $language,
    ModelsCategoryGroup $category_group, Hook $hook, Config $config
    ) {
        $this->hook = $hook;
        $this->image = $image;
        $this->alias = $alias;
        $this->config = $config;
        $this->db = $config->getDb();
        $this->language = $language;
        $this->category_group = $category_group;
    }

    /**
     * Loads a category from the database
     * @param integer $category_id
     * @param string $language
     * @return array
     */
    public function get($category_id, $language = null)
    {
        $this->hook->fire('get.category.before', $category_id);

        $sql = '
                SELECT c.*, cg.store_id FROM category c
                LEFT JOIN category_group cg ON(c.category_group_id=cg.category_group_id)
                WHERE c.category_id=:category_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':category_id' => (int) $category_id));

        $category = $sth->fetch(PDO::FETCH_ASSOC);

        if ($category) {
            $category['data'] = unserialize($category['data']);
            $category['language'] = 'und';

            foreach ($this->getTranslations($category_id) as $translation) {
                $category['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($category['translation'][$language])) {
                $category = $category['translation'][$language] + $category;
            }
        }

        $category['images'] = $this->image->getList('category_id', $category_id);

        $this->hook->fire('get.category.after', $category);
        return $category;
    }

    /**
     * Returns an array of category translations
     * @param integer $category_id
     * @return array
     */
    public function getTranslations($category_id)
    {
        $sth = $this->db->prepare('SELECT * FROM category_translation WHERE category_id=:category_id');
        $sth->execute(array(':category_id' => (int) $category_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a list of categories per store to use directly in <select>
     * @param integer $store_id
     * @param string $usage
     * @return array
     */
    public function getOptionListByStore($store_id, $usage = null)
    {
        $list = array();
        foreach ($this->category_group->getList(array('store_id' => $store_id, 'type' => $usage)) as $group) {
            $list[$group['title']] = $this->getOptionList($group['category_group_id']);
        }
        return $list;
    }

    /**
     * Returns a list of categories to use directly in <select>
     * @param integer $group_id
     * @param integer $parent_id
     * @param boolean $hierarchy
     * @return array
     */
    public function getOptionList($group_id = null, $parent_id = 0,
                                  $hierarchy = true)
    {
        $categories = $this->getTree(array('category_group_id' => $group_id, 'parent_id' => $parent_id, 'status' => 1));

        if (!$categories) {
            return array();
        }

        $options = array();
        foreach ($categories as $category) {
            $title = $hierarchy ? str_repeat('— ', $category['depth']) . $category['title'] : $category['title'];
            $options[$category['category_id']] = $title;
        }

        return $options;
    }

    /**
     * Creates a hierarchical representation of the category group
     * @param array $data
     * @return array
     */
    public function getTree(array $data)
    {
        $tree = &Cache::memory('category.tree.' . implode('.', $data));

        if (isset($tree)) {
            return $tree;
        }

        $tree = array();
        $children_tree = array();
        $parents_tree = array();
        $categories_tree = array();

        $parent = isset($data['parent_id']) ? (int) $data['parent_id'] : 0;

        $sql = '
        SELECT c.*, a.alias, COALESCE(NULLIF(ct.title, ""), c.title) AS title
        FROM category c
        LEFT JOIN category_group cg ON(c.category_group_id = cg.category_group_id)
        LEFT JOIN category_translation ct ON(c.category_id=ct.category_id AND ct.language=?)
        LEFT JOIN alias a ON(a.id_key=? AND a.id_value=c.category_id)
        WHERE c.category_id > 0';

        $language = isset($data['language']) ? $data['language'] : $this->language->current();

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

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $category['data'] = unserialize($category['data']);
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
                //$tree[] = $category;

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
     * Counts number of children for a given category ID
     * @param integer $category_id
     * @param array $tree
     * @return integer
     */
    public function countChildren($category_id, $tree)
    {
        $children = 0;
        foreach ($tree as $item) {
            if ($item['parents'][0] == $category_id) {
                $children++;
            }
        }

        return $children;
    }

    /**
     * Returns an array of children for a given category ID
     * @param integer $category_id
     * @param array $tree
     * @return array
     */
    public function getChildren($category_id, $tree)
    {
        $children = array();
        foreach ($tree as $item) {
            if (in_array($category_id, $item['parents'])) {
                $children[] = $item;
            }
        }

        return $children;
    }

    /**
     * Returns an array of categories
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, cg.type, cg.store_id, COALESCE(NULLIF(ct.title, ""), c.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(c.category_id)';
        }

        $sql .= '
            FROM category c
            LEFT JOIN category_group cg ON(cg.category_group_id = c.category_group_id)
            LEFT JOIN category_translation ct ON(c.category_id = ct.category_id
            AND ct.language = ?)
            WHERE c.category_id > 0';

        $language = $this->language->current();
        $where = array($language);

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

        $sql .= " ORDER BY title DESC";

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $category) {
            $list[$category['category_id']] = $category;
        }

        $this->hook->fire('categories', $list);
        return $list;
    }

    /**
     * Adds a category
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.category.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'status' => !empty($data['status']),
            'category_group_id' => (int) $data['category_group_id'],
            'parent_id' => !empty($data['parent_id']) ? (int) $data['parent_id'] : 0,
            'data' => !empty($data['data']) ? serialize((array) $data['data']) : serialize(array()),
            'title' => $data['title'],
            'meta_title' => !empty($data['meta_title']) ? $data['meta_title'] : '',
            'description_1' => !empty($data['description_1']) ? $data['description_1'] : '',
            'description_2' => !empty($data['description_2']) ? $data['description_2'] : '',
            'meta_description' => !empty($data['meta_description']) ? $data['meta_description'] : ''
        );

        $category_id = $this->db->insert('category', $values);

        if (!empty($data['translation'])) {
            $this->setTranslations($category_id, $data, false);
        }

        if (empty($data['alias'])) {
            $data['category_id'] = $category_id;
            $data['alias'] = $this->createAlias($data);
        }

        if (!empty($data['alias'])) {
            $this->setAlias($category_id, $data, false);
        }

        if (!empty($data['images'])) {
            $this->setImages($category_id, $data);
        }

        $this->hook->fire('add.category.after', $data);

        return $category_id;
    }

    /**
     * Deletes category translation(s)
     * @param integer $category_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($category_id, $language = null)
    {
        $where = array('category_id' => (int) $category_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('category_translation', $where);
    }

    /**
     * Adds a category translation
     * @param integer $category_id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($category_id, $language, array $translation)
    {
        $values = array(
            'category_id' => (int) $category_id,
            'language' => $language,
            'title' => $translation['title'],
            'description_1' => $translation['description_1'],
            'description_2' => $translation['description_2'],
            'meta_description' => $translation['meta_description'],
            'meta_title' => $translation['meta_title']
        );

        return $this->db->insert('category_translation', $values);
    }

    /**
     * Returns a string containing a generated URL alias
     * @param array $data
     * @return string
     */
    public function createAlias(array $data)
    {
        $pattern = $this->config->get('category_alias_pattern', '%t.html');
        $placeholders = $this->config->get('category_alias_placeholder', array('%t' => 'title'));
        return $this->alias->generate($pattern, $placeholders, $data);
    }

    /**
     * Updates a category
     * @param integer $category_id
     * @param array $data
     * @return boolean
     */
    public function update($category_id, array $data)
    {
        $this->hook->fire('update.category.before', $category_id, $data);

        if (empty($category_id)) {
            return false;
        }

        $values = array();

        if (isset($data['parent_id'])) {
            $values['parent_id'] = (int) $data['parent_id'];
        }

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (!empty($data['category_group_id'])) {
            $values['category_group_id'] = (int) $data['category_group_id'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (!empty($data['meta_title'])) {
            $values['meta_title'] = $data['meta_title'];
        }

        if (!empty($data['description_1'])) {
            $values['description_1'] = $data['description_1'];
        }

        if (!empty($data['description_2'])) {
            $values['description_2'] = $data['description_2'];
        }

        if (!empty($data['meta_description'])) {
            $values['meta_description'] = $data['meta_description'];
        }

        $result = false;

        if ($values) {
            $result = $this->db->update('category', $values, array('category_id' => (int) $category_id));
        }

        if (!empty($data['translation'])) {
            $this->setTranslations($category_id, $data);
            $result = true;
        }

        if (!empty($data['alias'])) {
            $this->setAlias($category_id, $data);
            $result = true;
        }

        if (!empty($data['images'])) {
            $this->setImages($category_id, $data);
            $result = true;
        }

        $this->hook->fire('update.category.after', $category_id, $data, $result);
        return $result;
    }

    /**
     * Deletes a category
     * @param integer $category_id
     * @return boolean
     */
    public function delete($category_id)
    {
        $this->hook->fire('delete.category.before', $category_id);

        if (empty($category_id)) {
            return false;
        }

        if (!$this->canDelete($category_id)) {
            return false;
        }

        $this->db->delete('category_translation', array('category_id' => (int) $category_id));
        $this->db->delete('category', array('category_id' => (int) $category_id));
        $this->db->delete('alias', array('id_key' => 'category_id', 'id_value' => (int) $category_id));
        $this->db->delete('file', array('id_key' => 'category_id', 'id_value' => (int) $category_id));

        $this->hook->fire('delete.category.after', $category_id);
        return true;
    }

    /**
     * Whether a category can be deleted
     * @param integer $category_id
     * @return boolean
     */
    public function canDelete($category_id)
    {
        $sql = '
            SELECT
            NOT EXISTS (SELECT product_id FROM product WHERE category_id=:category_id) AND
            NOT EXISTS (SELECT product_id FROM product WHERE brand_category_id=:category_id) AND
            NOT EXISTS (SELECT page_id FROM page WHERE category_id=:category_id) AND
            NOT EXISTS (SELECT category_id FROM category WHERE parent_id=:category_id)';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':category_id' => $category_id));

        return (bool) $sth->fetchColumn();
    }

    /**
     * Deletes and/or adds category translations
     * @param integer $category_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslations($category_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($category_id);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($category_id, $language, $translation);
        }

        return true;
    }

    /**
     * Deletes and/or adds an alias
     * @param integer $category_id
     * @param array $data
     * @param boolean $delete
     * @return integer
     */
    protected function setAlias($category_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->alias->delete('category_id', (int) $category_id);
        }

        return $this->alias->add('category_id', $category_id, $data['alias']);
    }

    /**
     * Adds category images
     * @param integer $category_id
     * @param array $data
     * @return array
     */
    protected function setImages($category_id, array $data)
    {
        return $this->image->setMultiple('category_id', $category_id, $data['images']);
    }
}
