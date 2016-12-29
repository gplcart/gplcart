<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Cache;
use core\models\Alias as AliasModel;
use core\models\Image as ImageModel;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to pages
 */
class Page extends Model
{

    /**
     * Cache instance
     * @var \core\Cache $cache
     */
    protected $cache;

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
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ImageModel $image
     * @param AliasModel $alias
     * @param LanguageModel $language
     * @param Cache $cache
     */
    public function __construct(ImageModel $image, AliasModel $alias,
            LanguageModel $language, Cache $cache)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->alias = $alias;
        $this->image = $image;
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

        $this->attachTranslation($page, $language);
        $this->attachImage($page);

        $this->hook->fire('get.page.after', $page_id, $page);
        return $page;
    }

    /**
     * Adds translations to the page
     * @param array $page
     * @param null|string $language
     * @return null
     */
    protected function attachTranslation(array &$page, $language)
    {
        if (empty($page)) {
            return null;
        }

        $page['language'] = 'und';

        $translations = $this->getTranslation($page['page_id']);

        foreach ($translations as $translation) {
            $page['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($page['translation'][$language])) {
            $page = $page['translation'][$language] + $page;
        }

        return null;
    }

    /**
     * Adds images to the page
     * @param array $page
     * @return null
     */
    protected function attachImage(array &$page)
    {
        if (empty($page)) {
            return null;
        }

        $images = $this->image->getList('page_id', $page['page_id']);

        foreach ($images as &$image) {
            $translations = $this->image->getTranslation($image['file_id']);
            foreach ($translations as $translation) {
                $image['translation'][$translation['language']] = $translation;
            }
        }

        $page['images'] = $images;
        return null;
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

        $this->setTranslation($data, false);
        $this->setImages($data);

        if (empty($data['alias'])) {
            $data['alias'] = $this->createAlias($data);
        }

        $this->setAlias($data, false);

        $this->hook->fire('add.page.after', $data);
        return $data['page_id'];
    }

    /**
     * Adds a translation
     * @param integer $page_id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($page_id, $language, array $translation)
    {
        $translation += array(
            'page_id' => $page_id,
            'language' => $language
        );

        return $this->db->insert('page_translation', $translation);
    }

    /**
     * Returns an array of page translations
     * @param integer $page_id
     * @return array
     */
    public function getTranslation($page_id)
    {
        $sql = 'SELECT * FROM page_translation WHERE page_id=?';
        return $this->db->fetchAll($sql, array($page_id));
    }

    /**
     * Creates an page alias
     * @param array $data
     * @return string
     */
    public function createAlias(array $data)
    {
        $pattern = $this->config->get('page_alias_pattern', '%t.html');
        $placeholders = $this->config->get('page_alias_placeholder', array('%t' => 'title'));

        return $this->alias->generate($pattern, $placeholders, $data);
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

        $updated += (int) $this->setAlias($data);
        $updated += (int) $this->setImages($data);
        $updated += (int) $this->setTranslation($data);

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

    /**
     * Adds/updates page images
     * @param array $data
     * @return boolean
     */
    protected function setImages(array $data)
    {
        if (empty($data['images'])) {
            return false;
        }

        return (bool) $this->image->setMultiple('page_id', $data['page_id'], $data['images']);
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
            $this->alias->delete('page_id', (int) $data['page_id']);
        }

        return (bool) $this->alias->add('page_id', $data['page_id'], $data['alias']);
    }

    /**
     * Deletes and/or adds page translations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslation(array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($data['page_id']);
        }

        if (empty($data['translation'])) {
            return false;
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['page_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes page translation(s)
     * @param integer $page_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($page_id, $language = null)
    {
        $where = array('page_id' => (int) $page_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('page_translation', $where);
    }

}
