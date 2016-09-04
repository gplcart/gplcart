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
use core\Handler;
use core\models\Collection as ModelsCollection;

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
    public function __construct(ModelsCollection $collection)
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
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(collection_item_id)';
        }

        $sql .= ' FROM collection_item WHERE collection_item_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['collection_id'])) {
            $sql .= ' AND collection_id = ?';
            $where[] = (int) $data['collection_id'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('weight', 'status', 'collection_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort)) && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY weight ASC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $items = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $item['data'] = unserialize($item['data']);
            $items[$item['collection_item_id']] = $item;
        }

        $this->hook->fire('collection.item.list', $items);
        return $items;
    }

    /**
     * Returns an array of collection item values
     * @param array $options
     * @return array
     */
    public function getValues(array $options)
    {
        $items = $this->getList($options);

        if (empty($items)) {
            return array();
        }

        $values = array();
        foreach ((array) $items as $id => $item) {
            $values[$item['value']] = $id;
        }

        return $values;
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

        $values = array(
            'value' => (int) $data['value'],
            'status' => !empty($data['status']),
            'collection_id' => (int) $data['collection_id'],
            'weight' => isset($data['weight']) ? $data['weight'] : 0,
            'data' => empty($data['data']) ? serialize(array()) : serialize((array) $data['data'])
        );

        $data['collection_item_id'] = $this->db->insert('collection', $values);

        $this->hook->fire('add.collection.after', $data);
        return $data['collection_item_id'];
    }

    /**
     * Loads a collection item from the database
     * @param integer $collection_item_id
     * @return array
     */
    public function get($collection_item_id)
    {
        $this->hook->fire('get.collection.item.before', $collection_item_id);

        $sql = 'SELECT * FROM collection_item WHERE collection_item_id=?';

        $sth = $this->db->prepare($sql);
        $sth->execute(array($collection_item_id));
        $result = $sth->fetch(PDO::FETCH_ASSOC);

        $this->hook->fire('get.collection.item.after', $collection_item_id, $result);
        return $result;
    }

    /**
     * Deletes a collection item
     * @param integer $collection_item_id
     * @return boolean
     */
    public function delete($collection_item_id)
    {
        $this->hook->fire('delete.collection.item.before', $collection_item_id);

        if (empty($collection_item_id)) {
            return false;
        }

        $conditions = array('collection_item_id' => (int) $collection_item_id);
        $result = $this->db->delete('collection_item', $conditions);

        $this->hook->fire('delete.collection.item.after', $collection_item_id, $result);
        return (bool) $result;
    }

    /**
     * Updates a collection item
     * @param integer $collection_item_id
     * @param array $data
     * @return boolean
     */
    public function update($collection_item_id, array $data)
    {
        $this->hook->fire('update.collection.item.before', $collection_item_id, $data);

        if (empty($collection_item_id)) {
            return false;
        }

        $result = false;
        $values = $this->getDbSchemeValues('collection_item', $data);

        if (!empty($values)) {
            $conditions = array('collection_item_id' => (int) $collection_item_id);
            $result = $this->db->update('collection_item', $values, $conditions);
        }

        $this->hook->fire('update.collection.item.after', $collection_item_id, $data, $result);
        return (bool) $result;
    }

    /**
     * 
     * @param array $collection
     * @param array $options
     * @return array
     */
    public function getEntities(array $collection, array $options = array())
    {
        $handler_id = $collection['type'];
        $handlers = $this->collection->getHandlers();

        $options += array(
            'status' => 1,
            'store_id' => $collection['store_id'],
            'collection_id' => $collection['collection_id']
        );

        $values = $this->getValues($options);

        if (empty($values)) {
            return array();
        }

        $options[$handlers[$handler_id]['id_key']] = array_keys($values);
        $results = Handler::call($handlers, $handler_id, 'list', array($options));

        if (empty($results)) {
            return array();
        }

        foreach ($results as $id => &$result) {
            if (isset($values[$id])) {
                $result['collection_item_id'] = $values[$id];
            }
        }

        return $results;
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
