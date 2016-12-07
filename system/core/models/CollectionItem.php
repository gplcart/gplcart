<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Handler;
use core\models\Collection as CollectionModel;

/**
 * Manages basic behaviors and data related to collection items
 */
class CollectionItem extends Model
{

    /**
     * Collection model instance
     * @var \core\models\Collection $collection
     */
    protected $collection;

    /**
     * Constructor
     */
    public function __construct(CollectionModel $collection)
    {
        parent::__construct();

        $this->collection = $collection;
    }

    /**
     * Returns an array of collection items depending on various conditions
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        ksort($data);

        $items = &gplcart_cache('collection.item.list.' . md5(json_encode($data)));

        if (isset($items)) {
            return $items;
        }

        list($sql, $params) = $this->getListSql($data);

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $params);
        }

        $options = array('index' => 'collection_item_id', 'unserialize' => 'data');
        $items = $this->db->fetchAll($sql, $params, $options);

        $this->hook->fire('collection.item.list', $items);
        return $items;
    }

    /**
     * Returns an array containing SQL query and its parameters for getList() method
     * @param array $data
     * @return array
     */
    protected function getListSql(array $data = array())
    {
        $sql = 'SELECT ci.*, c.status AS collection_status, c.store_id,'
                . 'c.type, c.title AS collection_title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(collection_item_id)';
        }

        $sql .= ' FROM collection_item ci'
                . ' LEFT JOIN collection c ON(ci.collection_id=c.collection_id)'
                . ' WHERE ci.collection_item_id > 0';

        $where = array();

        if (isset($data['value'])) {
            $sql .= ' AND ci.value = ?';
            $where[] = (int) $data['value'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND c.status = ?';
            $sql .= ' AND ci.status = ?';
            $where[] = (int) $data['status'];
            $where[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND c.store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['collection_id'])) {
            $sql .= ' AND ci.collection_id = ?';
            $where[] = (int) $data['collection_id'];
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

        return array($sql, $where);
    }

    /**
     * Adds a collection item
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.collection.item.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['collection_item_id'] = $this->db->insert('collection_item', $data);

        $this->hook->fire('add.collection.item.after', $data);
        return $data['collection_item_id'];
    }

    /**
     * Loads a collection item from the database
     * @param integer $id
     * @return array
     */
    public function get($id)
    {
        $this->hook->fire('get.collection.item.before', $id);

        $sql = 'SELECT *'
                . ' FROM collection_item'
                . ' WHERE collection_item_id=?';

        $result = $this->db->fetch($sql, array($id));

        $this->hook->fire('get.collection.item.after', $id, $result);
        return $result;
    }

    /**
     * Deletes a collection item
     * @param integer $id
     * @return boolean
     */
    public function delete($id)
    {
        $this->hook->fire('delete.collection.item.before', $id);

        if (empty($id)) {
            return false;
        }

        $conditions = array('collection_item_id' => $id);
        $result = $this->db->delete('collection_item', $conditions);

        $this->hook->fire('delete.collection.item.after', $id, $result);
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
        $this->hook->fire('update.collection.item.before', $id, $data);

        if (empty($id)) {
            return false;
        }

        $conditions = array('collection_item_id' => $id);
        $result = $this->db->update('collection_item', $data, $conditions);

        $this->hook->fire('update.collection.item.after', $id, $data, $result);
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
        foreach ($list as $item) {
            $handler_id = $item['type'];
            $items[$item['value']] = $item;
        }

        $handlers = $this->collection->getHandlers();
        $conditions[$handlers[$handler_id]['id_key']] = array_keys($items);

        $results = Handler::call($handlers, $handler_id, 'list', array($conditions));

        if (empty($results)) {
            return array();
        }

        foreach ($results as $entity_id => &$result) {
            if (isset($items[$entity_id])) {
                $result['weight'] = $items[$entity_id]['weight'];
                $result['collection_item'] = $items[$entity_id];
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
     * Returns an array of autocomplete suggestion for the given collection type
     * @param array $collection
     * @param array $options
     * @return array
     */
    public function getSuggestions(array $collection, array $options = array())
    {
        $handler_id = $collection['type'];
        $handlers = $this->collection->getHandlers();

        $options += array(
            'status' => 1,
            'store_id' => $collection['store_id']
        );

        $results = Handler::call($handlers, $handler_id, 'list', array($options));

        if (empty($results)) {
            return array();
        }

        return $results;
    }

}
