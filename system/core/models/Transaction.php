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

/**
 * Manages basic behaviors and data related to payment transactions
 */
class Transaction
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
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
    }

    /**
     * Returns an array of transactions or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = ' SELECT COUNT(transaction_id) ';
        }

        $sql .= ' FROM transactions WHERE transaction_id IS NOT NULL';

        $conditions = array();

        if (isset($data['order_id'])) {
            $sql .= ' AND order_id = ?';
            $conditions[] = (int) $data['order_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND created = ?';
            $conditions[] = (int) $data['created'];
        }

        if (isset($data['payment_method'])) {
            $sql .= ' AND payment_method LIKE ?';
            $conditions[] = "%{$data['payment_method']}%";
        }

        if (isset($data['gateway_transaction_id'])) {
            $sql .= ' AND gateway_transaction_id LIKE ?';
            $conditions[] = "%{$data['gateway_transaction_id']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('order_id', 'created', 'payment_method', 'gateway_transaction_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $results = $this->db->fetchAll($sql, $conditions, array('index' => 'transaction_id', 'unserialize' => 'data'));
        $this->hook->attach('transaction.list', $results, $this);
        return $results;
    }

    /**
     * Adds a transaction
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('transaction.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = GC_TIME;
        $result = $this->db->insert('transactions', $data);

        $this->hook->attach('transaction.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a transaction from the database
     * @param integer $transaction_id
     * @return array
     */
    public function get($transaction_id)
    {
        $result = null;
        $this->hook->attach('transaction.get.before', $transaction_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM transactions WHERE transaction_id=?';
        $result = $this->db->fetch($sql, array($transaction_id), array('unserialize' => 'data'));

        $this->hook->attach('transaction.get.after', $transaction_id, $result, $this);
        return $result;
    }

    /**
     * Deletes a transaction
     * @param integer $transaction_id
     * @return boolean
     */
    public function delete($transaction_id)
    {
        $result = null;
        $this->hook->attach('transaction.delete.before', $transaction_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('transactions', array('transaction_id' => $transaction_id));
        $this->hook->attach('transaction.delete.after', $transaction_id, $result, $this);
        return (bool) $result;
    }

}
