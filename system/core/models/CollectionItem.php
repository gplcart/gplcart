<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Handler;
use gplcart\core\models\Collection as CollectionModel;

/**
 * Manages basic behaviors and data related to collection items
 */
class CollectionItem extends Model
{

    /**
     * Collection model instance
     * @var \gplcart\core\models\Collection $collection
     */
    protected $collection;

    /**
     * @param CollectionModel $collection
     */
    public function __construct(CollectionModel $collection)
    {
        parent::__construct();

        $this->collection = $collection;
    }

    /**
     * Returns an array of collection items or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $list = &gplcart_static(gplcart_array_hash(array('collection.item.list' => $data)));

        if (isset($list)) {
            return $list;
        }

        $sql = 'SELECT ci.*, c.status AS collection_status, c.store_id,'
                . 'c.type, c.title AS collection_title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(collection_item_id)';
        }

        $sql .= ' FROM collection_item ci'
                . ' LEFT JOIN collection c ON(ci.collection_id=c.collection_id)'
                . ' WHERE ci.collection_item_id > 0';

        $conditions = array();

        if (isset($data['value'])) {
            $sql .= ' AND ci.value = ?';
            $conditions[] = (int) $data['value'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $sql .= ' AND ci.status = ?';
            $conditions[] = (int) $data['status'];
            $conditions[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND c.store_id = ?';
            $conditions[] = (int) $data['store_id'];
        }

        if (isset($data['collection_id'])) {
            $sql .= ' AND ci.collection_id = ?';
            $conditions[] = (int) $data['collection_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('weight', 'status', 'collection_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY ci.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY ci.weight DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $options = array('index' => 'collection_item_id', 'unserialize' => 'data');
        $list = $this->db->fetchAll($sql, $conditions, $options);

        $this->hook->attach('collection.item.list', $list, $this);
        return $list;
    }

    /**
     * Adds a collection item
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('collection.item.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = (int) $this->db->insert('collection_item', $data);
        $this->hook->attach('collection.item.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a collection item from the database
     * @param integer $id
     * @return array
     */
    public function get($id)
    {
        $result = null;
        $this->hook->attach('collection.item.get.before', $id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM collection_item WHERE collection_item_id=?';
        $result = $this->db->fetch($sql, array($id));

        $this->hook->attach('collection.item.get.after', $id, $result, $this);
        return $result;
    }

    /**
     * Deletes a collection item
     * @param integer $id
     * @return boolean
     */
    public function delete($id)
    {
        $result = null;
        $this->hook->attach('collection.item.delete.before', $id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('collection_item', array('collection_item_id' => $id));
        $this->hook->attach('collection.item.delete.after', $id, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a collection item
     * @param integer $id
     * @param array $data
     * @return boolean
     */
    public function update($id, array $data)
    {
        $result = null;
        $this->hook->attach('collection.item.update.before', $id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('collection_item', $data, array('collection_item_id' => $id));
        $this->hook->attach('collection.item.update.after', $id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of collection item entities
     * @param array $conditions
     * @return array
     */
    public function getItems(array $conditions = array())
    {
        $list = $this->getList($conditions);

        if (empty($list)) {
            return array();
        }

        $handler_id = null;

        $items = array();
        foreach ((array) $list as $item) {
            $handler_id = $item['type'];
            $items[$item['value']] = $item;
        }

        $handlers = $this->collection->getHandlers();
        $conditions[$handlers[$handler_id]['id_key']] = array_keys($items);
        $results = $this->getListEntities($handler_id, $conditions);

        if (empty($results)) {
            return array();
        }

        foreach ($results as $entity_id => &$result) {
            if (isset($items[$entity_id])) {
                $result['weight'] = $items[$entity_id]['weight'];
                $result['collection_item'] = $items[$entity_id];
                $result['collection_handler'] = $handlers[$handler_id];
            }
        }

        gplcart_array_sort($results);
        return $results;
    }

    /**
     * Returns the next possible weight for a collection item
     * @param integer $collection_id
     * @return integer
     */
    public function getNextWeight($collection_id)
    {
        $sql = 'SELECT MAX(weight) FROM collection_item WHERE collection_id=?';
        $weight = (int) $this->db->fetchColumn($sql, array($collection_id));
        return ++$weight;
    }

    /**
     * Returns an array of entities for the given collection ID
     * @param string $collection_id
     * @param array $arguments
     * @return array
     */
    public function getListEntities($collection_id, array $arguments)
    {
        try {
            $handlers = $this->collection->getHandlers();
            return Handler::call($handlers, $collection_id, 'list', array($arguments));
        } catch (\Exception $ex) {
            trigger_error($ex->getMessage());
            return array();
        }
    }

}
