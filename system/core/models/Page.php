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
use core\models\Alias as ModelsAlias;
use core\models\Image as ModelsImage;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to pages
 */
class Page extends Model
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
     * Constructor
     * @param ModelsImage $image
     * @param ModelsAlias $alias
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsImage $image, ModelsAlias $alias,
            ModelsLanguage $language)
    {
        parent::__construct();

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

        $sth = $this->db->prepare('SELECT * FROM page WHERE page_id=?');
        $sth->execute(array($page_id));

        $page = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($page)) {
            $page['data'] = unserialize($page['data']);
            $this->attachTransalation($page, $language);
            $this->attachImage($page);
        }

        $this->hook->fire('get.page.after', $page_id, $page);
        return $page;
    }

    /**
     * Adds translations to the page
     * @param array $page
     * @param null|string $language
     */
    protected function attachTransalation(array &$page, $language)
    {
        $page['language'] = 'und';

        foreach ($this->getTranslation($page['page_id']) as $translation) {
            $page['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($page['translation'][$language])) {
            $page = $page['translation'][$language] + $page;
        }
    }

    /**
     * Adds images to the page
     * @param array $page
     */
    protected function attachImage(array &$page)
    {
        $page['images'] = $this->image->getList('page_id', $page['page_id']);
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

        $data += array('created' => GC_TIME);
        $values = $this->prepareDbInsert('page', $data);
        $data['page_id'] = $this->db->insert('page', $values);

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
        $translation += array('page_id' => $page_id, 'language' => $language);

        $values = $this->prepareDbInsert('page_translation', $translation);
        return $this->db->insert('page_translation', $values);
    }

    /**
     * Returns an array of page translations
     * @param integer $page_id
     * @return array
     */
    public function getTranslation($page_id)
    {
        $sql = 'SELECT * FROM page_translation WHERE page_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($page_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
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

        $data += array('modified' => GC_TIME);
        $values = $this->filterDbValues('page', $data);

        $updated = 0;

        if (!empty($values)) {
            $conditions = array('page_id' => (int) $page_id);
            $updated += (int) $this->db->update('page', $values, $conditions);
        }

        $data['page_id'] = $page_id;

        $updated += (int) $this->setAlias($data);
        $updated += (int) $this->setImages($data);
        $updated += (int) $this->setTranslation($data);

        Cache::clear("page.$page_id");

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

        $this->db->delete('page', $conditions);
        $this->db->delete('page_translation', $conditions);

        $this->db->delete('file', $conditions2);
        $this->db->delete('alias', $conditions2);

        $sql = 'DELETE ci'
                . ' FROM collection_item ci'
                . ' INNER JOIN collection c ON(ci.collection_id = c.collection_id)'
                . ' WHERE c.type = ? AND ci.value = ?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array('page', $page_id));

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

        $sql .= ' FROM page p'
                . ' LEFT JOIN page_translation pt ON(pt.page_id = p.page_id AND pt.language=?)'
                . ' LEFT JOIN alias a ON(a.id_key=? AND a.id_value=p.page_id)'
                . ' LEFT JOIN user u ON(p.user_id = u.user_id)'
                . ' WHERE p.page_id > 0';

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

        if (isset($data['email'])) {
            $sql .= ' AND u.email LIKE ?';
            $where[] = "%{$data['email']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'store_id', 'status', 'created', 'email');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {

            if ($data['sort'] === 'email') {
                $sql .= " ORDER BY u.email {$data['order']}";
            } else {
                $sql .= " ORDER BY p.{$data['sort']} {$data['order']}";
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
            return (int) $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $page) {
            $page['data'] = unserialize($page['data']);
            $list[$page['page_id']] = $page;
        }

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
        if (empty($data['translation'])) {
            return false;
        }

        if ($delete) {
            $this->deleteTranslation($data['page_id']);
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
    protected function deleteTranslation($page_id, $language = null)
    {
        $where = array('page_id' => (int) $page_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('page_translation', $where);
    }

}
