<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;
use gplcart\core\models\Condition as ConditionModel;

/**
 * Manages basic behaviors and data related to triggers
 */
class Trigger
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param ConditionModel $condition
     */
    public function __construct(Hook $hook, Config $config, ConditionModel $condition)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
        $this->condition = $condition;
    }

    /**
     * Load a trigger
     * @param integer $trigger_id
     * @return array
     */
    public function get($trigger_id)
    {
        $result = null;
        $this->hook->attach('trigger.get.before', $trigger_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM triggers WHERE trigger_id=?';
        $result = $this->db->fetch($sql, array($trigger_id), array('unserialize' => 'data'));
        $this->hook->attach('trigger.get.after', $trigger_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of triggers or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = &gplcart_static(gplcart_array_hash(array('trigger.list' => $options)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('trigger.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(trigger_id)';
        }

        $sql .= ' FROM triggers WHERE trigger_id IS NOT NULL';

        $conditions = array();

        if (isset($options['status'])) {
            $sql .= ' AND status = ?';
            $conditions[] = (int) $options['status'];
        }

        if (isset($options['store_id'])) {
            $sql .= ' AND store_id = ?';
            $conditions[] = $options['store_id'];
        }

        if (isset($options['name'])) {
            $sql .= ' AND name LIKE ?';
            $conditions[] = "%{$options['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'status', 'store_id', 'trigger_id');

        if (isset($options['sort']) && in_array($options['sort'], $allowed_sort)//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY weight ASC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'trigger_id', 'unserialize' => 'data'));
            $result = $this->prepareList($result);
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('trigger.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a trigger
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('trigger.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $result = $this->db->insert('triggers', $data);
        $this->hook->attach('trigger.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Deletes a trigger
     * @param integer $trigger_id
     * @return boolean
     */
    public function delete($trigger_id)
    {
        $result = null;
        $this->hook->attach('trigger.delete.before', $trigger_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('triggers', array('trigger_id' => $trigger_id));
        $this->hook->attach('trigger.delete.after', $trigger_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Updates a trigger
     * @param integer $trigger_id
     * @param array $data
     * @return boolean
     */
    public function update($trigger_id, array $data)
    {
        $result = null;
        $this->hook->attach('trigger.update.before', $trigger_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->update('triggers', $data, array('trigger_id' => $trigger_id));
        $this->hook->attach('trigger.update.after', $trigger_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of triggered IDs for the given context
     * @param array $data
     * @param array $options
     * @return array
     */
    public function getTriggered(array $data = array(), array $options = array())
    {
        $options += array('status' => 1);
        $triggers = (array) $this->getList($options);

        if (empty($triggers)) {
            return array();
        }

        $triggered = array();
        foreach ($triggers as $trigger) {
            if ($this->condition->isMet($trigger, $data)) {
                $triggered[] = $trigger['trigger_id'];
            }
        }

        return $triggered;
    }

    /**
     * Prepare an array of triggers
     * @param array $list
     * @return array
     */
    protected function prepareList(array $list)
    {
        foreach ($list as &$item) {
            if (!empty($item['data']['conditions'])) {
                gplcart_array_sort($item['data']['conditions']);
            }
        }

        return $list;
    }

}
