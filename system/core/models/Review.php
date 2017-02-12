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
 * Manages basic behaviors and data related to the review system
 */
class Review extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a review
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('review.add.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;
        $data['review_id'] = $this->db->insert('review', $data);

        $this->hook->fire('review.add.after', $data);
        return $data['review_id'];
    }

    /**
     * Loads a review
     * @param integer $review_id
     * @return array
     */
    public function get($review_id)
    {
        $this->hook->fire('review.get.before', $review_id);

        $sql = 'SELECT * FROM review WHERE review_id=?';
        $review = $this->db->fetch($sql, array($review_id));

        $this->hook->fire('review.get.after', $review_id, $review);
        return $review;
    }

    /**
     * Updates a review
     * @param integer $review_id
     * @param array $data
     * @return boolean
     */
    public function update($review_id, array $data)
    {
        $this->hook->fire('review.update.before', $review_id, $data);

        if (empty($review_id)) {
            return false;
        }

        $data['modified'] = GC_TIME;
        $conditions = array('review_id' => $review_id);
        $result = $this->db->update('review', $data, $conditions);

        $this->hook->fire('review.update.after', $review_id, $data, $result);
        return (bool) $result;
    }

    /**
     * Deletes a review
     * @param integer $review_id
     * @return boolean
     */
    public function delete($review_id)
    {
        $this->hook->fire('review.delete.before', $review_id);

        if (empty($review_id)) {
            return false;
        }

        $ids = (array) $review_id;
        $placeholders = rtrim(str_repeat('?,', count($ids)), ',');

        $sql = "DELETE FROM review WHERE review_id IN($placeholders)";
        $result = (bool) $this->db->run($sql, $ids)->rowCount();

        $this->hook->fire('review.delete.after', $review_id, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of reviews or their total number
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT r.*, u.name, u.email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(r.review_id)';
        }

        $sql .= ' FROM review r'
                . ' LEFT JOIN user u ON(r.user_id = u.user_id)'
                . ' WHERE r.review_id > 0';

        $where = array();

        if (isset($data['text'])) {
            $sql .= ' AND r.text LIKE ?';
            $where[] = "%{$data['text']}%";
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND r.user_id = ?';
            $where[] = (int) $data['user_id'];
        }

        if (isset($data['email'])) {
            $sql .= ' AND u.email = ?';
            $where[] = $data['email'];
        }

        if (isset($data['product_id'])) {
            $sql .= ' AND r.product_id = ?';
            $where[] = (int) $data['product_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND r.status = ?';
            $where[] = (bool) $data['status'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND r.created = ?';
            $where[] = (int) $data['created'];
        }

        if (isset($data['modified'])) {
            $sql .= ' AND r.modified = ?';
            $where[] = (int) $data['modified'];
        }

        if (isset($data['user_status'])) {
            $sql .= ' AND u.status = ?';
            $where[] = (int) $data['user_status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array(
            'product_id' => 'r.product_id', 'email' => 'u.email', 'review_id' => 'r.review_id',
            'status' => 'r.status', 'created' => 'r.created', 'text' => 'r.text');

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY r.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $list = $this->db->fetchAll($sql, $where, array('index' => 'review_id'));
        $this->hook->fire('review.list', $list);

        return $list;
    }

    /**
     * Returns an array of allowed min and max limits for review text
     * @return array
     */
    public function getLimits()
    {
        $limits = array(
            'min' => $this->config->get('review_min_length', 10),
            'max' => $this->config->get('review_max_length', 1000)
        );

        return $limits;
    }

}
