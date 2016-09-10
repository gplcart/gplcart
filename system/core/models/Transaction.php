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
     * Returns an array of transactions
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

        if (isset($data['payment_service'])) {
            $sql .= ' AND t.payment_service LIKE ?';
            $where[] = "%{$data['payment_service']}%";
        }

        if (isset($data['service_transaction_id'])) {
            $sql .= ' AND t.service_transaction_id LIKE ?';
            $where[] = "%{$data['service_transaction_id']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('order_id', 'created', 'payment_service', 'service_transaction_id');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort)) && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY t.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY t.created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        $results = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $result) {
            $result['data'] = unserialize($result['data']);
            $results[$result['transaction_id']] = $result;
        }

        $this->hook->fire('transactions', $results);
        return $results;
    }

    /**
     * Adds a transaction
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.transaction.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'created' => (int) $data['created'],
            'order_id' => (int) $data['order_id'],
            'payment_service' => $data['payment_service'],
            'service_transaction_id' => $data['service_transaction_id'],
            'data' => empty($data['data']) ? serialize(array()) : serialize((array) $data['data']),
        );

        $data['transaction_id'] = $this->db->insert('transaction', $values);
        $this->hook->fire('add.transaction.after', $data);
        return $data['transaction_id'];
    }

    /**
     * Loads a transaction the database
     * @param integer $transaction_id
     * @return array
     */
    public function get($transaction_id)
    {
        $sql = 'SELECT * FROM transaction WHERE transaction_id=?';
        $sth = $this->db->prepare($sql);
        $sth->execute(array($transaction_id));

        return $sth->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Deletes a transaction
     * @param integer $transaction_id
     * @return boolean
     */
    public function delete($transaction_id)
    {
        $this->hook->fire('delete.transaction.before', $transaction_id);

        if (empty($transaction_id)) {
            return false;
        }

        $conditions = array('transaction_id' => $transaction_id);
        $result = $this->db->delete('transaction', $conditions);

        $this->hook->fire('delete.transaction.after', $transaction_id, $result);
        return (bool) $result;
    }

    /**
     * Updates a transaction
     * @param integer $transaction_id
     * @param array $data
     * @return boolean
     */
    public function update($transaction_id, array $data)
    {
        $this->hook->fire('update.transaction.before', $transaction_id, $data);

        if (empty($transaction_id)) {
            return false;
        }

        $conditions = array('transaction_id' => $transaction_id);
        $result = $this->db->update('transaction', $data, $conditions);

        $this->hook->fire('update.transaction.after', $transaction_id, $data, $result);
        return (bool) $result;
    }

}
