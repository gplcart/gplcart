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
 * Manages basic behaviors and data related to pages
 */
class Page
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
     * @param FileModel $file
     * @param AliasModel $alias
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, FileModel $file, AliasModel $alias,
            TranslationModel $translation, TranslationEntityModel $translation_entity
    )
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
     * Loads a page from the database
     * @param array|int $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('page.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!is_array($condition)) {
            $condition = array('page_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('page.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of pages or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('page.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT p.*, c.category_group_id,'
                . 'a.alias,'
                . 'u.email, u.role_id,'
                . 'pt.language,'
                . 'COALESCE(NULLIF(pt.title, ""), p.title) AS title,'
                . 'COALESCE(NULLIF(pt.description, ""), p.description) AS description,'
                . 'COALESCE(NULLIF(pt.meta_title, ""), p.meta_title) AS meta_title,'
                . 'COALESCE(NULLIF(pt.meta_description, ""), p.meta_description) AS meta_description';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(p.page_id)';
        }

        $conditions = array($options['language'], 'page');

        $sql .= ' FROM page p'
                . ' LEFT JOIN page_translation pt ON(pt.page_id = p.page_id AND pt.language=?)'
                . ' LEFT JOIN category c ON(p.category_id = c.category_id)'
                . ' LEFT JOIN alias a ON(a.entity=? AND a.entity_id=p.page_id)'
                . ' LEFT JOIN user u ON(p.user_id = u.user_id)';

        if (!empty($options['page_id'])) {
            settype($options['page_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['page_id'])), ',');
            $sql .= " WHERE p.page_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['page_id']);
        } else {
            $sql .= ' WHERE p.page_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $conditions[] = (int) $options['store_id'];
        }

        if (isset($options['category_group_id'])) {
            $sql .= ' AND c.category_group_id = ?';
            $conditions[] = (int) $options['category_group_id'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND p.status = ?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['email'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$options['email']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title' => 'p.title', 'store_id' => 'p.store_id',
            'page_id' => 'p.page_id', 'status' => 'p.status',
            'created' => 'p.created', 'email' => 'u.email');

        if (isset($options['sort']) && isset($allowed_sort[$options['sort']])//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY p.modified DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'page_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('page.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a page
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('page.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $data['page_id'] = $this->db->insert('page', $data);

        $this->setTranslations($data, $this->translation_entity, 'page', false);
        $this->setImages($data, $this->file, 'page');
        $this->setAlias($data, $this->alias, 'page', false);

        $this->hook->attach('page.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a page
     * @param integer $page_id
     * @param array $data
     * @return boolean
     */
    public function update($page_id, array $data)
    {
        $result = null;
        $this->hook->attach('page.update.before', $page_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $updated = $this->db->update('page', $data, array('page_id' => $page_id));
        $data['page_id'] = $page_id;

        $updated += (int) $this->setImages($data, $this->file, 'page');
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'page');
        $updated += (int) $this->setAlias($data, $this->alias, 'page');

        $result = $updated > 0;
        $this->hook->attach('page.update.after', $page_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a page
     * @param integer $page_id
     * @return boolean
     */
    public function delete($page_id)
    {
        $result = null;
        $this->hook->attach('page.delete.before', $page_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('page', array('page_id' => $page_id));

        if ($result) {
            $this->deleteLinked($page_id);
        }

        $this->hook->attach('page.delete.after', $page_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes all database records related to the page ID
     * @param int $page_id
     */
    protected function deleteLinked($page_id)
    {
        $this->db->delete('page_translation', array('page_id' => $page_id));
        $this->db->delete('file', array('entity' => 'page', 'entity_id' => $page_id));
        $this->db->delete('alias', array('entity' => 'page', 'entity_id' => $page_id));

        $sql = 'DELETE ci'
                . ' FROM collection_item ci'
                . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                . ' WHERE c.type = ? AND ci.value = ?';

        $this->db->run($sql, array('page', $page_id));
    }

    /**
     * Returns a relative/absolute path for uploaded images
     * @param boolean $absolute
     * @return string
     */
    public function getImagePath($absolute = false)
    {
        $dirname = $this->config->get('page_image_dirname', 'page');

        if ($absolute) {
            return gplcart_path_absolute($dirname, GC_DIR_IMAGE);
        }

        return gplcart_path_relative(GC_DIR_IMAGE, GC_DIR_FILE) . "/$dirname";
    }

}
