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
use core\models\Image as ModelsImage;
use core\models\Alias as ModelsAlias;
use core\models\Language as ModelsLanguage;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Manages basic behaviors and data related to product categories
 */
class Category extends Model
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
     * Constructor
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsLanguage $language
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(ModelsImage $image, ModelsAlias $alias,
            ModelsLanguage $language, ModelsCategoryGroup $category_group)
    {
        parent::__construct();

        $this->image = $image;
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
        $this->hook->fire('get.category.before', $category_id);

        $conditions = array($category_id);

        $sql = 'SELECT c.*, cg.store_id'
                . ' FROM category c'
                . ' LEFT JOIN category_group cg'
                . ' ON(c.category_group_id=cg.category_group_id)'
                . ' WHERE c.category_id=?';

        if (isset($store_id)) {
            $sql .= ' AND cg.store_id=?';
            $conditions[] = $store_id;
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($conditions);

        $category = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($category)) {
            $category['data'] = unserialize($category['data']);
            $this->attachTranslation($category, $language);
            $this->attachImage($category, $language);
        }

        $this->hook->fire('get.category.after', $category);
        return $category;
    }

    /**
     * Adds translations to the category
     * @param array $category
     * @param null|string $language
     */
    protected function attachTranslation(array &$category, $language)
    {
        $category['language'] = 'und';

        foreach ($this->getTranslation($category['category_id']) as $translation) {
            $category['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($category['translation'][$language])) {
            $category = $category['translation'][$language] + $category;
        }
    }

    /**
     * Adds images to the category
     * @param array $category
     * @param null|string $language
     */
    protected function attachImage(array &$category, $language)
    {
        $images = $this->image->getList('category_id', $category['category_id']);
        
        foreach($images as &$image){
            
            $translations = $this->image->getTranslation($image['file_id']);
            
            foreach($translations as $translation){
                $image['translation'][$translation['language']] = $translation;
            }
            
            if (isset($language) && isset($image['translation'][$language])) {
                $image = $image['translation'][$language] + $image;
            }
        }
        
        $category['images'] = $images;
    }

    /**
     * Returns an array of category translations
     * @param integer $category_id
     * @return array
     */
    public function getTranslation($category_id)
    {
        $sql = 'SELECT * FROM category_translation WHERE category_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($category_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Returns a list of categories per store to use directly in <select>
     * @param integer $store_id
     * @param string|null $usage
     * @return array
     */
    public function getOptionListByStore($store_id, $usage = null)
    {
        $list = array();
        $conditions = array('store_id' => $store_id, 'type' => $usage);
        foreach ($this->category_group->getList($conditions) as $group) {
            $list[$group['title']] = $this->getOptionList($group['category_group_id']);
        }
        return $list;
    }

    /**
     * Returns a list of categories to use directly in <select>
     * @param null|integer $group_id
     * @param integer $parent_id
     * @param boolean $hierarchy
     * @return array
     */
    public function getOptionList($group_id = null, $parent_id = 0,
            $hierarchy = true)
    {
        $conditions = array(
            'status' => 1,
            'parent_id' => $parent_id,
            'category_group_id' => $group_id
        );

        $categories = $this->getTree($conditions);

        if (empty($categories)) {
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

        $sql = 'SELECT c.*, a.alias, COALESCE(NULLIF(ct.title, ""), c.title) AS title'
                . ' FROM category c'
                . ' LEFT JOIN category_group cg ON(c.category_group_id = cg.category_group_id)'
                . ' LEFT JOIN category_translation ct ON(c.category_id=ct.category_id AND ct.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=c.category_id)'
                . ' WHERE c.category_id > 0';

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
        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $category) {
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
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT c.*, cg.type, cg.store_id, COALESCE(NULLIF(ct.title, ""), c.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(c.category_id)';
        }

        $sql .= ' FROM category c'
                . ' LEFT JOIN category_group cg ON(cg.category_group_id = c.category_group_id)'
                . ' LEFT JOIN category_translation ct ON(c.category_id = ct.category_id AND ct.language = ?)'
                . ' WHERE c.category_id > 0';

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
            return (int) $sth->fetchColumn();
        }

        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        $list = array();
        foreach ($results as $category) {
            $category['data'] = unserialize($category['data']);
            $list[$category['category_id']] = $category;
        }

        $this->hook->fire('categories', $list);
        return $list;
    }

    /**
     * Adds a category
     * @param array $data
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.category.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = $this->prepareDbInsert('category', $data);
        $data['category_id'] = $this->db->insert('category', $values);

        $this->setTranslation($data, false);
        $this->setImages($data);
        
        if (empty($data['alias'])) {
            $data['alias'] = $this->createAlias($data);
        }
        
        $this->setAlias($data, false);

        $this->hook->fire('add.category.after', $data);
        return $data['category_id'];
    }

    /**
     * Deletes category translation(s)
     * @param integer $category_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($category_id, $language = null)
    {
        $conditions = array('category_id' => (int) $category_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool) $this->db->delete('category_translation', $conditions);
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
        $translation['language'] = $language;
        $translation['category_id'] = $category_id;

        $values = $this->prepareDbInsert('category_translation', $translation);
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

        $values = $this->filterDbValues('category', $data);

        $updated = 0;

        if (!empty($values)) {
            $conditions = array('category_id' => (int) $category_id);
            $updated += (int) $this->db->update('category', $values, $conditions);
        }

        $data['category_id'] = $category_id;

        $updated += (int) $this->setTranslation($data);
        $updated += (int) $this->setImages($data);
        $updated += (int) $this->setAlias($data);

        $result = ($updated > 0);

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

        $conditions = array('category_id' => (int) $category_id);
        $conditions2 = array('id_key' => 'category_id', 'id_value' => (int) $category_id);

        $this->db->delete('category', $conditions);
        $this->db->delete('category_translation', $conditions);

        $this->db->delete('file', $conditions2);
        $this->db->delete('alias', $conditions2);

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
        $sql = 'SELECT NOT EXISTS (SELECT product_id FROM product WHERE category_id=:category_id)'
                . ' AND NOT EXISTS (SELECT product_id FROM product WHERE brand_category_id=:category_id)'
                . ' AND NOT EXISTS (SELECT page_id FROM page WHERE category_id=:category_id)'
                . ' AND NOT EXISTS (SELECT category_id FROM category WHERE parent_id=:category_id)';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':category_id' => $category_id));

        return (bool) $sth->fetchColumn();
    }

    /**
     * Deletes and/or adds category translations
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
            $this->deleteTranslation($data['category_id']);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['category_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes and/or adds an alias
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setAlias(array $data, $delete = true)
    {
        if (empty($data['alias'])) {
            return false;
        }

        if ($delete) {
            $this->alias->delete('category_id', $data['category_id']);
        }

        return (bool) $this->alias->add('category_id', $data['category_id'], $data['alias']);
    }

    /**
     * Adds category images
     * @param array $data
     * @return boolean
     */
    protected function setImages(array $data)
    {
        if (empty($data['images'])) {
            return false;
        }

        return (bool) $this->image->setMultiple('category_id', $data['category_id'], $data['images']);
    }

}
