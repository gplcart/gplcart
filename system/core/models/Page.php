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
use core\models\Alias;
use core\models\Image;
use core\models\Language;
use core\classes\Cache;

class Page
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
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook class instance
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
     * @param Image $image
     * @param Alias $alias
     * @param Language $language
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Image $image, Alias $alias, Language $language, Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->alias = $alias;
        $this->image = $image;
        $this->config = $config;
        $this->db = $this->config->db();
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
        $sth = $this->db->prepare('SELECT * FROM page WHERE page_id=:page_id');
        $sth->execute(array(':page_id' => $page_id));

        $page = $sth->fetch(PDO::FETCH_ASSOC);

        if ($page) {

            $page['data'] = unserialize($page['data']);
            $page['language'] = 'und';

            $sth = $this->db->prepare('SELECT * FROM page_translation WHERE page_id=:page_id');
            $sth->execute(array(':page_id' => $page_id));

            foreach ($this->getTranslations($page_id) as $translation) {
                $page['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($page['translation'][$language])) {
                $page = $page['translation'][$language] + $page;
            }

            $page['images'] = $this->image->getList('page_id', $page_id);
        }

        $this->hook->fire('get.page.after', $page_id, $page);
        return $page;
    }

    /**
     * Adds a page
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.page.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'modified' => 0,
            'title' => $data['title'],
            'description' => $data['description'],
            'user_id' => (int) $data['user_id'],
            'status' => !empty($data['status']),
            'front' => !empty($data['front']),
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME,
            'data' => !empty($data['data']) ? serialize($data['data']) : serialize(array()),
            'meta_title' => !empty($data['meta_title']) ? $data['meta_title'] : '',
            'meta_description' => !empty($data['meta_description']) ? $data['meta_description'] : '',
            'store_id' => !empty($data['store_id']) ? (int) $data['store_id'] : $this->config->get('store', 1),
            'category_id' => !empty($data['category_id']) ? (int) $data['category_id'] : 0,
        );

        $page_id = $this->db->insert('page', $values);

        if (!empty($data['translation'])) {
            $this->setTranslations($page_id, $data, false);
        }

        if (!empty($data['images'])) {
            $this->setImages($page_id, $data);
        }

        if (empty($data['alias'])) {
            $data['page_id'] = $page_id;
            $data['alias'] = $this->generateAlias($data);
        }

        if ($data['alias']) {
            $this->setAlias($page_id, $data, false);
        }

        $this->hook->fire('add.page.after', $data, $page_id);
        return $page_id;
    }

    /**
     * Adds/updates page images
     * @param integer $page_id
     * @param array $data
     * @return array
     */
    protected function setImages($page_id, array $data)
    {
        return $this->image->setMultiple('page_id', $page_id, $data['images']);
    }

    /**
     * Deletes and/or adds an alias
     * @param integer $page_id
     * @param array $data
     * @param boolean $delete
     * @return integer
     */
    protected function setAlias($page_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->alias->delete('page_id', (int) $page_id);
        }

        return $this->alias->add('page_id', $page_id, $data['alias']);
    }

    /**
     * Deletes and/or adds page translations
     * @param integer $page_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslations($page_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($page_id);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($page_id, $language, $translation);
        }

        return true;
    }

    /**
     * Deletes page translation(s)
     * @param integer $page_id
     * @param null|string $language
     * @return boolean
     */
    protected function deleteTranslation($page_id, $language = null)
    {
        $where = array('page_id' => (int) $page_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('page_translation', $where);
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
        $values = array(
            'page_id' => (int) $page_id,
            'language' => $language,
            'title' => !empty($translation['title']) ? $translation['title'] : '',
            'description' => !empty($translation['description']) ? $translation['description'] : '',
            'meta_description' => !empty($translation['meta_description']) ? $translation['meta_description'] : '',
            'meta_title' => !empty($translation['meta_title']) ? $translation['meta_title'] : '',
        );

        return $this->db->insert('page_translation', $values);
    }

    /**
     * Returns an array of page translations
     * @param integer $page_id
     * @return array
     */
    public function getTranslations($page_id)
    {
        $sth = $this->db->prepare('SELECT * FROM page_translation WHERE page_id=:page_id');
        $sth->execute(array(':page_id' => (int) $page_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Creates an page alias
     * @param array $data
     * @return string
     */
    public function generateAlias(array $data)
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

        $values = array();
        $where = array('page_id' => (int) $page_id);

        if (isset($data['status'])) {
            $values['status'] = (int) $data['status'];
        }
        
        if (isset($data['front'])) {
            $values['front'] = (int) $data['front'];
        }

        if (!empty($data['created'])) {
            $values['created'] = (int) $data['created'];
        }

        if (!empty($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (!empty($data['meta_title'])) {
            $values['meta_title'] = $data['meta_title'];
        }

        if (!empty($data['meta_description'])) {
            $values['meta_description'] = $data['meta_description'];
        }

        if (!empty($data['description'])) {
            $values['description'] = $data['description'];
        }

        if (isset($data['category_id'])) {
            $values['category_id'] = (int) $data['category_id'];
        }

        if (isset($data['user_id'])) {
            $values['user_id'] = (int) $data['user_id'];
        }

        if (isset($data['store_id'])) {
            $values['store_id'] = (int) $data['store_id'];
        }

        if (!empty($data['data'])) {
            $values['data'] = serialize((array) $data['data']);
        }

        if ($values) {
            $values['modified'] = !empty($data['modified']) ? (int) $data['modified'] : GC_TIME;
            $this->db->update('page', $values, $where);
        }

        if (!empty($data['translation'])) {
            $this->setTranslations($page_id, $data);
        }

        if (!empty($data['images'])) {
            $this->setImages($page_id, $data);
        }

        if (!empty($data['alias'])) {
            $this->setAlias($page_id, $data);
        }

        Cache::clear("page.$page_id");

        $this->hook->fire('update.page.after', $page_id, $data);
        return true;
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

        $this->db->delete('page', array('page_id' => (int) $page_id));
        $this->db->delete('page_translation', array('page_id' => (int) $page_id));
        $this->db->delete('alias', array('id_key' => 'page_id', 'id_value' => (int) $page_id));
        $this->db->delete('file', array('id_key' => 'page_id', 'id_value' => (int) $page_id));

        $this->hook->fire('delete.page.after', $page_id);
        return true;
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

        $sql .= '
        FROM page p
        LEFT JOIN page_translation pt ON(pt.page_id = p.page_id AND pt.language=?)
        LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.page_id)
        LEFT JOIN user u ON(p.user_id = u.user_id)
        WHERE p.page_id > 0';

        $language = $this->language->current();

        $where = array($language, 'page_id');

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
        
        if (isset($data['front'])) {
            $sql .= ' AND p.front = ?';
            $where[] = (int) $data['front'];
        }

        if (isset($data['email'])) {
            $sql .= ' AND u.email LIKE ?';
            $where[] = "%{$data['email']}%";
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {

            switch ($data['sort']) {
                case 'title':
                    $sql .= " ORDER BY p.title {$data['order']}";
                    break;
                case 'store_id':
                    $sql .= " ORDER BY p.store_id {$data['order']}";
                    break;
                case 'status':
                    $sql .= " ORDER BY p.status {$data['order']}";
                    break;
                case 'front':
                    $sql .= " ORDER BY p.front {$data['order']}";
                    break;
                case 'created':
                    $sql .= " ORDER BY p.created {$data['order']}";
                    break;
                case 'email':
                    $sql .= " ORDER BY u.email {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY p.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $page) {
            $list[$page['page_id']] = $page;
        }

        $this->hook->fire('pages', $list);
        return $list;
    }

}
