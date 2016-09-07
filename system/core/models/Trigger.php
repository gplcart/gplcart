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
use core\classes\Tool;
use core\classes\Cache;
use core\models\Condition as ModelsCondition;

/**
 * Manages basic behaviors and data related to triggers
 */
class Trigger extends Model
{

    /**
     * Condition model instance
     * @var \core\models\Condition $condition
     */
    protected $condition;

    /**
     * Constructor
     */
    public function __construct(ModelsCondition $condition)
    {
        parent::__construct();

        $this->condition = $condition;
    }

    /**
     * Returns an array of triggers
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $cache_key = 'trigger.list.' . md5(json_encode($data));
        $triggers = &Cache::memory($cache_key);

        if (isset($triggers)) {
            return $triggers;
        }

        $triggers = array();

        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(trigger_id)';
        }

        $sql .= ' FROM triggers WHERE trigger_id > 0';

        $where = array();

        if (isset($data['status'])) {
            $sql .= ' AND status = ?';
            $where[] = (int) $data['status'];
        }

        if (isset($data['store_id'])) {
            $sql .= ' AND store_id = ?';
            $where[] = (int) $data['store_id'];
        }

        if (isset($data['weight'])) {
            $sql .= ' AND weight = ?';
            $where[] = (int) $data['weight'];
        }

        if (isset($data['name'])) {
            $sql .= ' AND name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'status', 'store_id');

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

        $results = $sth->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $trigger) {
            $trigger['database'] = true;
            $trigger['data'] = unserialize($trigger['data']);

            if (!empty($trigger['data']['conditions'])) {
                Tool::sortWeight($trigger['data']['conditions']);
            }

            $triggers[$trigger['trigger_id']] = $trigger;
        }

        $this->hook->fire('trigger.list', $triggers);
        return $triggers;
    }

    /**
     * Adds a trigger to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.trigger.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'name' => $data['name'],
            'data' => serialize($data['data']),
            'status' => !empty($data['status']),
            'store_id' => (int) $data['store_id'],
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0
        );

        $data['trigger_id'] = $this->db->insert('triggers', $values);

        $this->hook->fire('add.trigger.after', $data);
        return $data['trigger_id'];
    }

    /**
     * Returns an array of a single trigger
     * @param integer $trigger_id
     * @return array
     */
    public function get($trigger_id)
    {
        $this->hook->fire('get.trigger.before', $trigger_id);

        $sth = $this->db->prepare('SELECT * FROM triggers WHERE trigger_id=?');
        $sth->execute(array($trigger_id));

        $trigger = $sth->fetch(PDO::FETCH_ASSOC);

        if (isset($trigger['data'])) {
            $trigger['data'] = unserialize($trigger['data']);
        }

        $this->hook->fire('get.trigger.after', $trigger_id, $trigger);
        return $trigger;
    }

    /**
     * Deletes a trigger from the database
     * @param integer $trigger_id
     * @return boolean
     */
    public function delete($trigger_id)
    {
        $this->hook->fire('delete.trigger.before', $trigger_id);

        if (empty($trigger_id)) {
            return false;
        }

        $conditions = array('trigger_id' => (int) $trigger_id);
        $result = $this->db->delete('triggers', $conditions);

        $this->hook->fire('delete.trigger.after', $trigger_id, $result);
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
        $this->hook->fire('update.trigger.before', $trigger_id, $data);

        if (empty($trigger_id)) {
            return false;
        }

        $values = $this->filterDbValues('triggers', $data);

        $result = false;
        if (!empty($values)) {
            $conditions = array('trigger_id' => (int) $trigger_id);
            $this->db->update('triggers', $values, $conditions);
            $result = true;
        }

        $this->hook->fire('update.trigger.after', $trigger_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of fired trigger IDs for the current context
     * @param array $options
     * @param array $data
     * @return array
     */
    public function getFired(array $options = array(), array $data = array())
    {
        $options += array('status' => 1);

        $triggers = $this->getList($options);

        if (empty($triggers)) {
            return array();
        }

        $fired = array();
        foreach ($triggers as $trigger) {

            $result = $this->condition->isMet($trigger['data']['conditions'], $data);

            if ($result === true) {
                $fired[] = $trigger['trigger_id'];
            }
        }

        return $fired;
    }

}
