<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to collections
 */
class Collection extends Model
{

    use \gplcart\core\traits\TranslationTrait;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of collections or counts them
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
        $allowed_sort = array('title', 'status', 'type', 'store_id', 'collection_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))
        ) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'collection_id');
        $collections = $this->db->fetchAll($sql, $where, $options);

        $this->hook->attach('collection.list', $collections, $this);
        return $collections;
    }

    /**
     * Adds a collection
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('collection.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $data['collection_id'] = $this->db->insert('collection', $data);
        $this->setTranslationTrait($this->db, $data, 'collection', false);

        $this->hook->attach('collection.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a collection from the database
     * @param integer $collection_id
     * @param null|string $language
     * @return array
     */
    public function get($collection_id, $language = null)
    {
        $result = null;
        $this->hook->attach('collection.get.before', $collection_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM collection WHERE collection_id=?';
        $result = $this->db->fetch($sql, array($collection_id));

        $this->attachTranslationTrait($this->db, $result, 'collection', $language);

        $this->hook->attach('collection.get.after', $collection_id, $language, $result, $this);
        return $result;
    }

    /**
     * Deletes a collection
     * @param integer $collection_id
     * @return boolean
     */
    public function delete($collection_id)
    {
        $result = null;
        $this->hook->attach('collection.delete.before', $collection_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!$this->canDelete($collection_id)) {
            return false;
        }

        $conditions = array('collection_id' => $collection_id);
        $result = $this->db->delete('collection', $conditions);

        if (!empty($result)) {
            $this->db->delete('collection_translation', $conditions);
        }

        $this->hook->attach('collection.delete.after', $collection_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Whether a collection can be deleted
     * @param integer $collection_id
     * @return boolean
     */
    public function canDelete($collection_id)
    {
        $sql = 'SELECT collection_item_id'
                . ' FROM collection_item'
                . ' WHERE collection_id=?';

        $result = $this->db->fetchColumn($sql, array($collection_id));
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
        $result = null;
        $this->hook->attach('collection.update.before', $collection_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        unset($data['type']); // Cannot change item type!

        $updated = $this->db->update('collection', $data, array('collection_id' => $collection_id));

        $data['collection_id'] = $collection_id;

        $updated += (int) $this->setTranslationTrait($this->db, $data, 'collection');

        $result = $updated > 0;

        $this->hook->attach('collection.update.after', $collection_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of collection handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = require GC_CONFIG_COLLECTION;

        array_walk($handlers, function(&$handler) {
            $handler['title'] = $this->language->text($handler['title']);
        });

        $this->hook->attach('collection.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns an array of collection type names keyed by a handler ID
     * @return array
     */
    public function getTypes()
    {
        $handlers = $this->getHandlers();

        $types = array();
        foreach ($handlers as $handler_id => $handler) {
            $types[$handler_id] = $handler['title'];
        }

        return $types;
    }

}
