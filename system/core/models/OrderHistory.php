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
use gplcart\core\models\History as HistoryModel;

/**
 * Manages basic behaviors and data related to order history
 */
class OrderHistory
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
     * History model class instance
     * @var \gplcart\core\models\History $history
     */
    protected $history;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param HistoryModel $history
     */
    public function __construct(Hook $hook, Config $config, HistoryModel $history)
    {
        $this->hook = $hook;
        $this->history = $history;
        $this->db = $config->getDb();
    }

    /**
     * Mark the order is viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function setViewed(array $order)
    {
        return $this->history->set('order', $order['order_id'], $order['created']);
    }

    /**
     * Whether the order has not been viewed by the current user
     * @param array $order
     * @return boolean
     */
    public function isNew(array $order)
    {
        return $this->history->isNew($order['created'], $order['viewed']);
    }

    /**
     * Adds an order log record to the database
     * @param array $log
     * @return int
     */
    public function add(array $log)
    {
        $result = null;
        $this->hook->attach('order.log.add.before', $log, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $log += array(
            'data' => array(),
            'created' => GC_TIME
        );

        $result = $this->db->insert('order_log', $log);
        $this->hook->attach('order.log.add.after', $log, $result, $this);
        return (int) $result;
    }

    /**
     * Returns an array of log records
     * @param array $options
     * @return array|int
     */
    public function getList(array $options)
    {
        $sql = 'SELECT ol.*, u.name AS user_name, u.email AS user_email, u.status AS user_status';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(ol.order_log_id)';
        }

        $sql .= ' FROM order_log ol
                  LEFT JOIN user u ON(ol.user_id=u.user_id)
                  WHERE ol.order_id=?';

        if (!empty($options['count'])) {
            return (int) $this->db->fetchColumn($sql, array($options['order_id']));
        }

        $sql .= ' ORDER BY ol.created DESC';

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        $fetch_options = array('unserialize' => 'data', 'index' => 'order_log_id');
        $list = $this->db->fetchAll($sql, array($options['order_id']), $fetch_options);
        $this->hook->attach('order.log.list', $list, $this);
        return (array) $list;
    }

}
