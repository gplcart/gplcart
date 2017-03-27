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

    use \gplcart\core\traits\EntityTranslation;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Collection constructor.
     * @param Language $language
     */
    public function __construct(LanguageModel $language)
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
        $this->hook->fire('collection.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['collection_id'] = $this->db->insert('collection', $data);

        $this->setTranslationTrait($this->db, $data, 'collection', false);

        $this->hook->fire('collection.add.after', $data);
        return $data['collection_id'];
    }

    /**
     * Loads a collection from the database
     * @param integer $collection_id
     * @param null|string $language
     * @return array
     */
    public function get($collection_id, $language = null)
    {
        $this->hook->fire('collection.get.before', $collection_id);

        $sql = 'SELECT * FROM collection WHERE collection_id=?';
        $collection = $this->db->fetch($sql, array($collection_id));

        $this->attachTranslationTrait($this->db, $collection, 'collection', $language);

        $this->hook->fire('collection.get.after', $collection_id, $collection);
        return $collection;
    }

    /**
     * Deletes a collection
     * @param integer $collection_id
     * @return boolean
     */
    public function delete($collection_id)
    {
        $this->hook->fire('collection.delete.before', $collection_id);

        if (empty($collection_id) || !$this->canDelete($collection_id)) {
            return false;
        }

        $conditions = array('collection_id' => (int) $collection_id);
        $result = $this->db->delete('collection', $conditions);

        if (!empty($result)) {
            $this->db->delete('collection_translation', $conditions);
        }

        $this->hook->fire('collection.delete.after', $collection_id, $result);
        return (bool) $result;
    }

    /**
     * Whether the collection can be deleted
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
        $this->hook->fire('collection.update.before', $collection_id, $data);

        if (empty($collection_id)) {
            return false;
        }

        unset($data['type']); // Cannot change item type!

        $conditions = array('collection_id' => (int) $collection_id);
        $updated = $this->db->update('collection', $data, $conditions);

        $data['collection_id'] = $collection_id;
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'collection');
        $result = ($updated > 0);

        $this->hook->fire('collection.update.after', $collection_id, $data, $result);
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

        $handlers = array();

        $handlers['product'] = array(
            'title' => $this->language->text('Product'),
            'id_key' => 'product_id',
            'handlers' => array(
                'list' => array('gplcart\\core\\models\\Product', 'getList'),
                'validate' => array('gplcart\\core\\handlers\\validator\\CollectionItem', 'product'),
            ),
            'template' => array(
                'item' => 'product/item/grid',
                'list' => 'collection/list/product'
            ),
        );

        $handlers['file'] = array(
            'title' => $this->language->text('File'),
            'id_key' => 'file_id',
            'handlers' => array(
                'list' => array('gplcart\\core\\models\\File', 'getList'),
                'validate' => array('gplcart\\core\\handlers\\validator\\CollectionItem', 'file'),
            ),
            'template' => array(
                'item' => 'collection/item/file',
                'list' => 'collection/list/file'
            )
        );

        $handlers['page'] = array(
            'title' => $this->language->text('Page'),
            'id_key' => 'page_id',
            'handlers' => array(
                'list' => array('gplcart\\core\\models\\Page', 'getList'),
                'validate' => array('gplcart\\core\\handlers\\validator\\CollectionItem', 'page'),
            ),
            'template' => array(
                'item' => 'collection/item/page',
                'list' => 'collection/list/page'
            )
        );

        $this->hook->fire('collection.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns an array of collection type names keyed by handler ID
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
