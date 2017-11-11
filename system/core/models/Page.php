<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Cache,
    gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Alias as AliasModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\traits\Image as ImageTrait,
    gplcart\core\traits\Alias as AliasTrait;

/**
 * Manages basic behaviors and data related to pages
 */
class Page
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
     * Cache instance
     * @var \gplcart\core\Cache $cache
     */
    protected $cache;

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
     * @param Cache $cache
     * @param LanguageModel $language
     * @param AliasModel $alias
     * @param FileModel $file
     */
    public function __construct(Hook $hook, Database $db, Config $config, Cache $cache,
            LanguageModel $language, AliasModel $alias, FileModel $file)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;

        $this->file = $file;
        $this->alias = $alias;
        $this->cache = $cache;
        $this->language = $language;
    }

    /**
     * Loads a page from the database
     * @param integer $page_id
     * @param string|null $language
     * @return array
     */
    public function get($page_id, $language = null)
    {
        $result = null;
        $this->hook->attach('page.get.before', $page_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT p.*, u.role_id'
                . ' FROM page p'
                . ' LEFT JOIN user u ON(p.user_id=u.user_id)'
                . ' WHERE p.page_id=?';

        $result = $this->db->fetch($sql, array($page_id));

        $this->attachImagesTrait($this->db, $this->file, $result, 'page', $language);
        $this->attachTranslationTrait($this->db, $result, 'page', $language);

        $this->hook->attach('page.get.after', $page_id, $language, $result, $this);

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

        $this->setTranslationTrait($this->db, $data, 'page', false);
        $this->setImagesTrait($this->file, $data, 'page');
        $this->setAliasTrait($this->alias, $data, 'page', false);

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

        $updated += (int) $this->setImagesTrait($this->file, $data, 'page');
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'page');
        $updated += (int) $this->setAliasTrait($this->alias, $data, 'page');

        $this->cache->clear("page.$page_id");

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

        $conditions = array('page_id' => $page_id);
        $conditions2 = array('id_key' => 'page_id', 'id_value' => $page_id);

        $result = (bool) $this->db->delete('page', $conditions);

        if ($result) {

            $this->db->delete('page_translation', $conditions);
            $this->db->delete('file', $conditions2);
            $this->db->delete('alias', $conditions2);

            $sql = 'DELETE ci'
                    . ' FROM collection_item ci'
                    . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                    . ' WHERE c.type = ? AND ci.value = ?';

            $this->db->run($sql, array('page', $page_id));
        }

        $this->hook->attach('page.delete.after', $page_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of pages or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT p.*, c.category_group_id, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title, u.email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.page_id)';
        }

        $language = $this->language->getLangcode();
        $where = array($language, 'page_id');

        $sql .= ' FROM page p'
                . ' LEFT JOIN page_translation pt ON(pt.page_id = p.page_id AND pt.language=?)'
                . ' LEFT JOIN category c ON(p.category_id = c.category_id)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.page_id)'
                . ' LEFT JOIN user u ON(p.user_id = u.user_id)';

        if (!empty($data['page_id'])) {
            settype($data['page_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['page_id'])), ',');
            $sql .= " WHERE p.page_id IN($placeholders)";
            $where = array_merge($where, $data['page_id']);
        } else {
            $sql .= ' WHERE p.page_id > 0';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['language'])) {
            $sql .= ' AND pt.language = ?';
            $where[] = $data['language'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND p.store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['category_group_id'])) {
            $sql .= ' AND c.category_group_id = ?';
            $where[] = (int) $data['category_group_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND p.status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['email'])) {
            $sql .= ' AND u.email LIKE ?';
            $where[] = "%{$data['email']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title' => 'p.title', 'store_id' => 'p.store_id',
            'page_id' => 'p.page_id', 'status' => 'p.status',
            'created' => 'p.created', 'email' => 'u.email');

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY p.modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'page_id');
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->attach('page.list', $list, $this);
        return $list;
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

        return trim(substr(GC_DIR_IMAGE, strlen(GC_DIR_FILE)), '/') . "/$dirname";
    }

}
