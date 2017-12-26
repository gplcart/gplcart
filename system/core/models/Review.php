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
use gplcart\core\models\Translation as TranslationModel;

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
     * Translation UI model class instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param TranslationModel $translation
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->translation = $translation;
        $this->db = $this->config->getDb();
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

        $result = $this->db->fetch('SELECT * FROM review WHERE review_id=?', array($review_id));
        $this->hook->attach('review.get.after', $review_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of reviews or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = null;
        $this->hook->attach('review.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT r.*, u.name, u.email, COALESCE(NULLIF(pt.title, ""), p.title) AS product_title';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(r.review_id)';
        }

        $sql .= ' FROM review r'
                . ' LEFT JOIN user u ON(r.user_id = u.user_id)'
                . ' LEFT JOIN product p ON(r.product_id = p.product_id)'
                . ' LEFT JOIN product_translation pt ON(r.product_id = pt.product_id AND pt.language=?)'
                . ' WHERE r.review_id IS NOT NULL';

        $conditions = array($options['language']);

        if (isset($options['text'])) {
            $sql .= ' AND r.text LIKE ?';
            $conditions[] = "%{$options['text']}%";
        }

        if (isset($options['product_title'])) {
            $sql .= ' AND (p.title LIKE ? OR (pt.title LIKE ? AND pt.language=?))';
            $conditions[] = "%{$options['product_title']}%";
            $conditions[] = "%{$options['product_title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['user_id'])) {
            $sql .= ' AND r.user_id = ?';
            $conditions[] = (int) $options['user_id'];
        }

        if (isset($options['email'])) {
            $sql .= ' AND u.email = ?';
            $conditions[] = $options['email'];
        }

        if (isset($options['email_like'])) {
            $sql .= ' AND u.email LIKE ?';
            $conditions[] = "%{$options['email_like']}%";
        }

        if (isset($options['product_id'])) {
            $sql .= ' AND r.product_id = ?';
            $conditions[] = (int) $options['product_id'];
        }

        if (isset($options['status'])) {
            $sql .= ' AND r.status = ?';
            $conditions[] = (bool) $options['status'];
        }

        if (isset($options['created'])) {
            $sql .= ' AND r.created = ?';
            $conditions[] = (int) $options['created'];
        }

        if (isset($options['modified'])) {
            $sql .= ' AND r.modified = ?';
            $conditions[] = (int) $options['modified'];
        }

        if (isset($options['user_status'])) {
            $sql .= ' AND u.status = ?';
            $conditions[] = (int) $options['user_status'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('product_id' => 'r.product_id', 'email' => 'u.email',
            'review_id' => 'r.review_id', 'status' => 'r.status', 'created' => 'r.created',
            'text' => 'r.text', 'product_title' => 'p.title');

        if (isset($options['sort']) && isset($allowed_sort[$options['sort']])//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$options['sort']]} {$options['order']}";
        } else {
            $sql .= " ORDER BY r.modified DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'review_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('review.list.after', $options, $result, $this);
        return $result;
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
     * Returns an array of allowed min and max limits for review text
     * @return array
     */
    public function getLimits()
    {
        return array(
            $this->config->get('review_min_length', 10),
            $this->config->get('review_max_length', 1000)
        );
    }

}
