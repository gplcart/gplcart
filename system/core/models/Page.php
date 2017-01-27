<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\models\File as FileModel;
use gplcart\core\models\Alias as AliasModel;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to pages
 */
class Page extends Model
{

    use \gplcart\core\traits\EntityImage,
        \gplcart\core\traits\EntityAlias,
        \gplcart\core\traits\EntityTranslation;

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
     * Constructor
     * @param AliasModel $alias
     * @param FileModel $file
     * @param LanguageModel $language
     * @param Cache $cache
     */
    public function __construct(AliasModel $alias, FileModel $file,
            LanguageModel $language, Cache $cache)
    {
        parent::__construct();

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
        $this->hook->fire('get.page.before', $page_id, $language);

        $sql = 'SELECT p.*, u.role_id'
                . ' FROM page p'
                . ' LEFT JOIN user u ON(p.user_id=u.user_id)'
                . ' WHERE p.page_id=?';


        $page = $this->db->fetch($sql, array($page_id));

        $this->attachImagesTrait($this->file, $page, 'page', $language);
        $this->attachTranslationTrait($this->db, $page, 'page', $language);

        $this->hook->fire('get.page.after', $page_id, $page);
        return $page;
    }

    /**
     * Adds a page
     * @param array $data
     * @return integer|boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.page.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data['page_id'] = $this->db->insert('page', $data);

        $this->setTranslationTrait($this->db, $data, 'page', false);
        $this->setImagesTrait($this->file, $data, 'page');
        $this->setAliasTrait($this->alias, $data, 'page', false);

        $this->hook->fire('add.page.after', $data);
        return $data['page_id'];
    }

    /**
     * Updates a page
     * @param integer $page_id
     * @param array $data
     * @return boolean
     */
    public function update($page_id, array $data)
    {
        $this->hook->fire('update.page.before', $page_id, $data);

        if (empty($page_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;
        $conditions = array('page_id' => (int) $page_id);

        $updated = $this->db->update('page', $data, $conditions);

        $data['page_id'] = $page_id;

        $updated += (int) $this->setImagesTrait($this->file, $data, 'page');
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'page');
        $updated += (int) $this->setAliasTrait($this->alias, $data, 'page');

        $this->cache->clear("page.$page_id");

        $result = ($updated > 0);

        $this->hook->fire('update.page.after', $page_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a page
     * @param integer $page_id
     * @return boolean
     */
    public function delete($page_id)
    {
        $this->hook->fire('delete.page.before', $page_id);

        if (empty($page_id)) {
            return false;
        }

        $conditions = array('page_id' => $page_id);
        $conditions2 = array('id_key' => 'page_id', 'id_value' => $page_id);

        $deleted = (bool) $this->db->delete('page', $conditions);

        if ($deleted) {

            $this->db->delete('page_translation', $conditions);
            $this->db->delete('file', $conditions2);
            $this->db->delete('alias', $conditions2);

            $sql = 'DELETE ci'
                    . ' FROM collection_item ci'
                    . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                    . ' WHERE c.type = ? AND ci.value = ?';

            $this->db->run($sql, array('page', $page_id));
        }

        $this->hook->fire('delete.page.after', $page_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Returns an array of pages or total number of pages
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT p.*, a.alias, COALESCE(NULLIF(pt.title, ""), p.title) AS title, u.email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(p.page_id)';
        }

        $language = $this->language->current();
        $where = array($language, 'page_id');

        $sql .= ' FROM page p'
                . ' LEFT JOIN page_translation pt ON(pt.page_id = p.page_id AND pt.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.page_id)'
                . ' LEFT JOIN user u ON(p.user_id = u.user_id)';

        if (!empty($data['page_id'])) {
            $ids = (array) $data['page_id'];
            $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
            $sql .= ' WHERE p.page_id IN(' . $placeholders . ')';
            $where = array_merge($where, $ids);
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
            $sql .= " ORDER BY p.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'page_id');
        $list = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('pages', $list);
        return $list;
    }

}
