<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;

/**
 * Manages basic behaviors and data related to payment transactions
 */
class Transaction extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array of transactions or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT t.*';

        if (!empty($data['count'])) {
            $sql = ' SELECT COUNT(t.transaction_id) ';
        }

        $sql .= ' FROM transaction t';

        $where = array();

        if (isset($data['order_id'])) {
            $sql .= ' AND t.order_id = ?';
            $where[] = (int) $data['order_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND t.created = ?';
            $where[] = (int) $data['created'];
        }

        if (isset($data['payment_method'])) {
            $sql .= ' AND t.payment_method LIKE ?';
            $where[] = "%{$data['payment_method']}%";
        }

        if (isset($data['gateway_transaction_id'])) {
            $sql .= ' AND t.gateway_transaction_id LIKE ?';
            $where[] = "%{$data['gateway_transaction_id']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('order_id', 'created', 'payment_method', 'gateway_transaction_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY t.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY t.created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'transaction_id', 'unserialize' => 'data');
        $results = $this->db->fetchAll($sql, $where, $options);

        $this->hook->fire('transaction.list', $results, $this);
        return $results;
    }

    /**
     * Adds a transaction
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('transaction.add.before', $data, $this);

        if (empty($data)) {
            return false;
        }

        $data += array('created' => GC_TIME);
        $data['transaction_id'] = $this->db->insert('transaction', $data);

        $this->hook->fire('transaction.add.after', $data, $this);

        return $data['transaction_id'];
    }

    /**
     * Loads a transaction the database
     * @param integer $transaction_id
     * @return array
     */
    public function get($transaction_id)
    {
        $this->hook->fire('transaction.get.before', $transaction_id, $this);

        $options = array('unserialize' => 'data');
        $sql = 'SELECT * FROM transaction WHERE transaction_id=?';
        $transaction = $this->db->fetch($sql, array($transaction_id), $options);

        $this->hook->fire('transaction.get.after', $transaction_id, $transaction, $this);
        return $transaction;
    }

    /**
     * Deletes a transaction
     * @param integer $transaction_id
     * @return boolean
     */
    public function delete($transaction_id)
    {
        $this->hook->fire('transaction.delete.before', $transaction_id, $this);

        if (empty($transaction_id)) {
            return false;
        }

        $conditions = array('transaction_id' => $transaction_id);
        $result = $this->db->delete('transaction', $conditions);

        $this->hook->fire('transaction.delete.after', $transaction_id, $result, $this);
        return (bool) $result;
    }

}
