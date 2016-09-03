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
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to collections
 */
class Collection extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     */
    public function __construct(ModelsLanguage $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of collections depending on various conditions
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(collection_id)';
        }

        $sql .= ' FROM collection WHERE collection_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['type'])) {
            $sql .= ' AND type = ?';
            $where[] = $data['type'];
        }

        if (isset($data['title'])) {
            $sql .= ' AND title LIKE ?';
            $where[] = "%{$data['title']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'status', 'type', 'store_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort)) && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $collections = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $collection) {
            $collections[$collection['collection_id']] = $collection;
        }

        $this->hook->fire('collection.list', $collections);
        return $collections;
    }

    /**
     * Adds a collection
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.collection.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'type' => $data['type'],
            'title' => $data['title'],
            'status' => !empty($data['status']),
            'store_id' => (int) $data['store_id'],
            'description' => isset($data['description']) ? $data['description'] : ''
        );

        $data['collection_id'] = $this->db->insert('collection', $values);

        $this->setTranslations($data['collection_id'], $data);

        $this->hook->fire('add.collection.after', $data);
        return $data['collection_id'];
    }

    /**
     * Deletes and/or adds collection translations
     * @param integer $collection_id
     * @param array $data
     * @return boolean
     */
    protected function setTranslations($collection_id, array $data)
    {
        if (empty($data['translation'])) {
            return false;
        }

        $this->deleteTranslation($collection_id);

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($collection_id, $language, $translation);
        }

        return true;
    }

    /**
     * Deletes collection translation(s)
     * @param integer $collection_id
     * @param null|string $language
     * @return boolean
     */
    protected function deleteTranslation($collection_id, $language = null)
    {
        $conditions = array('collection_id' => (int) $collection_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool) $this->db->delete('collection_translation', $conditions);
    }

    /**
     * Adds a collection translation
     * @param integer $collection_id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($collection_id, $language, array $translation)
    {
        if($translation['title'] === ''){
            return false;
        }
        
        $values = array(
            'language' => $language,
            'title' => $translation['title'],
            'collection_id' => (int) $collection_id
        );

        return $this->db->insert('collection_translation', $values);
    }

    /**
     * Loads a collection from the database
     * @param integer $collection_id
     * @param null|string $language
     * @return array
     */
    public function get($collection_id, $language = null)
    {
        $this->hook->fire('get.collection.before', $collection_id);

        $sth = $this->db->prepare('SELECT * FROM collection WHERE collection_id=?');
        $sth->execute(array($collection_id));
        $collection = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($collection)) {

            $collection['language'] = 'und';

            foreach ($this->getTranslations($collection_id) as $translation) {
                $collection['translation'][$translation['language']] = $translation;
            }

            if (isset($language) && isset($collection['translation'][$language])) {
                $collection = $collection['translation'][$language] + $collection;
            }
        }

        $this->hook->fire('get.collection.after', $collection_id, $collection);
        return $collection;
    }

    /**
     * Returns an array of translations
     * @param integer $collection_id
     * @return array
     */
    public function getTranslations($collection_id)
    {
        $sql = 'SELECT * FROM collection_translation WHERE collection_id=?';
        $sth = $this->db->prepare($sql);
        $sth->execute(array($collection_id));

        return $sth->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a collection
     * @param integer $collection_id
     * @return boolean
     */
    public function delete($collection_id)
    {
        $this->hook->fire('delete.collection.before', $collection_id);

        if (empty($collection_id) || !$this->canDelete($collection_id)) {
            return false;
        }

        $conditions = array('collection_id' => (int) $collection_id);
        $result = $this->db->delete('collection', $conditions);

        if (!empty($result)) {
            $this->deleteTranslation($collection_id);
        }

        $this->hook->fire('delete.collection.after', $collection_id, $result);
        return (bool) $result;
    }

    /**
     * Whether the collection can be deleted
     * @param integer $collection_id
     * @return boolean
     */
    public function canDelete($collection_id)
    {
        $sql = 'SELECT collection_item_id FROM collection_item WHERE collection_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($collection_id));
        $result = $sth->fetchColumn();

        return empty($result);
    }

    /**
     * Updates a collection
     * @param integer $collection_id
     * @param array $data
     * @return boolean
     */
    public function update($collection_id, array $data)
    {
        $this->hook->fire('update.collection.before', $collection_id, $data);

        if (empty($collection_id)) {
            return false;
        }

        $this->setTranslations($collection_id, $data);

        $result = false;
        $values = $this->getDbSchemeValues('collection', $data);
        
        unset($values['type']); // Cannot change item type!

        if (!empty($values)) {
            $conditions = array('collection_id' => (int) $collection_id);
            $this->db->update('collection', $values, $conditions);
            $result = true;
        }

        $this->hook->fire('update.collection.after', $collection_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of collection handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory('collection.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['product'] = array(
            'title' => $this->language->text('Product'),
            'id_key' => 'product_id',
            'handlers' => array(
                'list' => array('core\\models\\Product', 'getList'),
                'validate' => array('core\\handlers\\validator\\Collection', 'product'),
            ),
        );

        $handlers['file'] = array(
            'title' => $this->language->text('File'),
            'id_key' => 'file_id',
            'handlers' => array(
                'list' => array('core\\models\\File', 'getList'),
                'validate' => array('core\\handlers\\validator\\Collection', 'file'),
            ),
        );

        $handlers['page'] = array(
            'title' => $this->language->text('Page'),
            'id_key' => 'page_id',
            'handlers' => array(
                'list' => array('core\\models\\Page', 'getList'),
                'validate' => array('core\\handlers\\validator\\Collection', 'page'),
            ),
        );

        $this->hook->fire('collection.handlers', $handlers);
        return $handlers;
    }
}
