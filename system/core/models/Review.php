<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to the review system
 */
class Review
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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Language model class instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param LanguageModel $language
     */
    public function __construct(Hook $hook, Database $db, Config $config, LanguageModel $language)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;
        $this->language = $language;
    }

    /**
     * Adds a review
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('review.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $this->db->insert('review', $data);

        $this->hook->attach('review.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Loads a review
     * @param integer $review_id
     * @return array
     */
    public function get($review_id)
    {
        $result = null;
        $this->hook->attach('review.get.before', $review_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM review WHERE review_id=?';
        $result = $this->db->fetch($sql, array($review_id));

        $this->hook->attach('review.get.after', $review_id, $result, $this);
        return $result;
    }

    /**
     * Updates a review
     * @param integer $review_id
     * @param array $data
     * @return boolean
     */
    public function update($review_id, array $data)
    {
        $result = null;
        $this->hook->attach('review.update.before', $review_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $result = $this->db->update('review', $data, array('review_id' => $review_id));

        $this->hook->attach('review.update.after', $review_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Deletes a review
     * @param integer|array $review_id
     * @return boolean
     */
    public function delete($review_id)
    {
        $result = null;
        $this->hook->attach('review.delete.before', $review_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        settype($review_id, 'array');

        $placeholders = rtrim(str_repeat('?,', count($review_id)), ',');
        $sql = "DELETE FROM review WHERE review_id IN($placeholders)";

        $result = (bool) $this->db->run($sql, $review_id)->rowCount();

        $this->hook->attach('review.delete.after', $review_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of reviews or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT r.*, u.name, u.email, COALESCE(NULLIF(pt.title, ""), p.title) AS product_title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(r.review_id)';
        }

        $sql .= ' FROM review r'
                . ' LEFT JOIN user u ON(r.user_id = u.user_id)'
                . ' LEFT JOIN product p ON(r.product_id = p.product_id)'
                . ' LEFT JOIN product_translation pt ON(r.product_id = pt.product_id AND pt.language=?)'
                . ' WHERE r.review_id IS NOT NULL';

        $language = $this->language->getLangcode();
        $conditions = array($language);

        if (isset($data['text'])) {
            $sql .= ' AND r.text LIKE ?';
            $conditions[] = "%{$data['text']}%";
        }

        if (isset($data['product_title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $conditions[] = "%{$data['product_title']}%";
            $conditions[] = "%{$data['product_title']}%";
            $conditions[] = $language;
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND r.user_id = ?';
            $conditions[] = (int) $data['user_id'];
        }

        if (isset($data['email'])) {
            $sql .= ' AND u.email = ?';
            $conditions[] = $data['email'];
        }

        if (isset($data['email_like'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$data['email_like']}%";
        }

        if (isset($data['product_id'])) {
            $sql .= ' AND r.product_id = ?';
            $conditions[] = (int) $data['product_id'];
        }

        if (isset($data['status'])) {
            $sql .= ' AND r.status = ?';
            $conditions[] = (bool) $data['status'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND r.created = ?';
            $conditions[] = (int) $data['created'];
        }

        if (isset($data['modified'])) {
            $sql .= ' AND r.modified = ?';
            $conditions[] = (int) $data['modified'];
        }

        if (isset($data['user_status'])) {
            $sql .= ' AND u.status = ?';
            $conditions[] = (int) $data['user_status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array(
            'product_id' => 'r.product_id', 'email' => 'u.email', 'review_id' => 'r.review_id',
            'status' => 'r.status', 'created' => 'r.created', 'text' => 'r.text', 'product_title' => 'p.title');

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY r.modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'review_id'));
        $this->hook->attach('review.list', $list, $this);
        return $list;
    }

    /**
     * Returns an array of allowed min and max limits for review text
     * @return array
     */
    public function getLimits()
    {
        return array(
            'min' => $this->config->get('review_min_length', 10),
            'max' => $this->config->get('review_max_length', 1000)
        );
    }

}
