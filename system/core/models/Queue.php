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

/**
 * Manages basic behaviors and data related to system queues
 */
class Queue
{

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
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();
    }

    /**
     * Sets a queue
     * @param array $queue
     * @param array $items
     * @param boolean $replace
     * @return boolean|integer
     */
    public function set(array $queue, array $items, $replace = false)
    {
        if (empty($this->db)) {
            return false;
        }

        $items = (array) $items;

        if (!is_array($queue)) {
            $queue = array('queue_id' => (string) $queue);
        }

        if (!isset($queue['total'])) {
            $queue['total'] = count($items);
        }

        if ($replace) {
            $this->delete($queue['queue_id']);
            $this->add($queue);
            return $this->addItem($queue['queue_id'], $items);
        }

        $existing = $this->get($queue['queue_id']);

        if ($existing) {
            $queue['total'] = (int) $existing['items'] + (int) $queue['total'];
            $this->update($queue['queue_id'], $queue);
        } else {
            $this->add($queue);
        }

        return $this->addItem($queue['queue_id'], $items);
    }

    /**
     * Deletes a queue
     * @param string $queue_id
     * @return boolean
     */
    public function delete($queue_id)
    {
        $this->hook->fire('delete.queue.before', $queue_id);

        if (empty($queue_id)) {
            return false;
        }

        $this->db->delete('queue', array('queue_id' => $queue_id));
        $this->db->delete('queue_item', array('queue_id' => $queue_id));

        $this->hook->fire('delete.queue.after', $queue_id);
        return true;
    }

    /**
     * Adds a queue
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.queue.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'queue_id' => $data['queue_id'],
            'total' => !empty($data['total']) ? (int) $data['total'] : 0,
            'created' => GC_TIME,
            'modified' => 0,
            'status' => !empty($data['status'])
        );

        $this->db->insert('queue', $values);
        $this->hook->fire('after.queue.after', $data);
        return true;
    }

    /**
     * Adds an item to a queue
     * @param string $queue_id
     * @param array $item
     * @return boolean|integer
     */
    public function addItem($queue_id, array $item)
    {
        $this->hook->fire('add.queue.item.before', $queue_id, $item);

        if (empty($queue_id)) {
            return false;
        }

        $items = (array) $item;

        $values = array();
        foreach ($items as $value) {
            $values[] = '(' . implode(',', array($this->db->quote($queue_id), $this->db->quote($value))) . ')';
        }

        $result = 0;

        if ($values) {
            $sql = 'INSERT INTO queue_item(queue_id, value) VALUES ' . implode(',', $values);
            $result = $this->db->query($sql)->rowCount();
        }

        $this->hook->fire('add.queue.item.after', $queue_id, $items, $result);
        return $result;
    }

    /**
     * Loads a queue
     * @param string $queue_id
     * @return array
     */
    public function get($queue_id)
    {
        $this->hook->fire('get.queue.before', $queue_id);

        if (empty($queue_id)) {
            return array();
        }

        $sql = '
        SELECT q.*, COUNT(qi.queue_item_id) AS items
        FROM queue q
        LEFT JOIN queue_item qi ON(q.queue_id = qi.queue_id)
        WHERE q.queue_id=:queue_id GROUP BY q.queue_id';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':queue_id' => $queue_id));

        $queue = $sth->fetch(PDO::FETCH_ASSOC);

        $this->hook->fire('add.queue.after', $queue_id, $queue);
        return $queue;
    }

    /**
     * Updates a queue
     * @param string $queue_id
     * @param array $data
     * @return boolean
     */
    public function update($queue_id, $data)
    {
        $this->hook->fire('update.queue.before', $queue_id, $data);

        if (empty($queue_id)) {
            return false;
        }

        $values = array('modified' => GC_TIME);

        if (!empty($data['modified'])) {
            $values['modified'] = (int) $data['modified'];
        }

        if (!empty($data['total'])) {
            $values['total'] = (int) $data['total'];
        }

        if (isset($data['status'])) {
            $values['status'] = (bool) $data['status'];
        }

        $result = false;

        if ($values) {
            $this->db->update('queue', $values, array('queue_id' => $queue_id));
            $result = true;
        }

        $this->hook->fire('update.queue.after', $queue_id, $data, $result);
        return $result;
    }

    /**
     * Shifts an item from the queue
     * @param string $queue_id
     * @return array|boolean
     */
    public function shiftItem($queue_id)
    {
        $sth = $this->db->prepare('SELECT * FROM queue_item WHERE queue_id=:queue_id LIMIT 0,1');
        $sth->execute(array(':queue_id' => $queue_id));

        $item = false;
        $result = $sth->fetchAll(PDO::FETCH_ASSOC);

        if ($result) {
            $item = reset($result);
            $this->db->delete('queue_item', array('queue_item_id' => $item['queue_item_id']));
        }

        return $item;
    }

    /**
     * Loads an item from a queue
     * @param string $queue_id
     * @param string $value
     * @return boolean|array
     */
    public function getItem($queue_id, $value)
    {
        $this->hook->fire('get.queue.item.before', $queue_id, $value);

        if (empty($value)) {
            return false;
        }

        $sth = $this->db->prepare('SELECT * FROM queue_item WHERE queue_id=:queue_id AND value=:value');
        $sth->execute(array(':queue_id' => $queue_id, ':value' => $value));
        $item = $sth->fetch(PDO::FETCH_ASSOC);

        $this->hook->fire('get.queue.item.after', $queue_id, $value, $item);
        return $item;
    }

    /**
     * Deletes a queue item
     * @param string $queue_id
     * @param string|null $value
     * @return boolean
     */
    public function deleteItem($queue_id, $value = null)
    {
        $this->hook->fire('delete.queue.item.before', $queue_id, $value);

        if (empty($queue_id)) {
            return false;
        }

        $where = array('queue_id' => $queue_id);

        if (isset($value)) {
            $where['value'] = $value;
        }

        $this->db->delete('queue_item', $where);
        $this->hook->fire('delete.queue.item.after', $queue_id, $value);
        return true;
    }

    /**
     * Returns an array of queues
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT q.*, COUNT(qi.queue_item_id) AS items,ROUND((1 - COUNT(qi.queue_item_id) / q.total) * 100) AS progress ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(q.queue_id) ';
        }

        $sql .= '
        FROM queue q
        LEFT JOIN queue_item qi ON(q.queue_id = qi.queue_id)
        WHERE q.queue_id IS NOT NULL';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND q.status = ?';
            $where[] = (bool) $data['status'];
        }

        $sql .= ' GROUP BY q.queue_id ORDER BY q.modified DESC';

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $queue) {
            $list[$queue['queue_id']] = $queue;
        }

        $this->hook->fire('queues', $list);
        return $list;
    }
}
