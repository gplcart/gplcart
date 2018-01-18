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
 * Manages basic behaviors and data related to rating system
 */
class Rating
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
     * Returns an array of rating data for a given product
     * @param integer $product_id
     * @return array
     */
    public function getByProduct($product_id)
    {
        $result = null;
        $this->hook->attach('rating.get.before', $product_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT rating, votes FROM rating WHERE product_id=?';
        $result = $this->db->fetch($sql, array($product_id));

        $this->hook->attach('rating.get.after', $product_id, $result, $this);
        return $result;
    }

    /**
     * Returns an array of user voting data for the given user(s)
     * @param integer $product_id
     * @param integer|array $user_id
     * @return array
     */
    public function getByUser($product_id, $user_id)
    {
        $result = null;
        $this->hook->attach('rating.get.user.before', $product_id, $user_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $user_ids = (array) $user_id;
        $conditions = array_merge($user_ids, array($product_id));
        $placeholders = rtrim(str_repeat('?,', count($user_ids)), ',');

        $sql = "SELECT * FROM rating_user WHERE user_id IN($placeholders) AND product_id=?";

        $result = $this->db->fetchAll($sql, $conditions, array('index' => 'user_id'));

        if (!is_array($user_id) && isset($result[$user_id])) {
            $result = $result[$user_id];
        }

        $this->hook->attach('rating.get.user.after', $product_id, $user_id, $result, $this);
        return $result;
    }

    /**
     * Sets a rating for the given user and product
     * @param array $data
     * @return array
     */
    public function set(array $data)
    {
        $result = null;
        $this->hook->attach('rating.set.before', $data, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $conditions = array(
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id']
        );

        $this->db->delete('rating_user', $conditions);

        $this->addByUser($data);

        $result = $this->setBayesian($data);
        $this->hook->attach('rating.set.after', $data, $result, $this);
        return $result;
    }

    /**
     * Adds a user rating
     * @param array $data
     * @return integer
     */
    protected function addByUser(array $data)
    {
        $result = null;
        $this->hook->attach('rating.add.user.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        if (empty($data['rating'])) {
            return 0; // Do not add rating 0 (unvote)
        }

        $result = $this->db->insert('rating_user', $data);
        $this->hook->attach('rating.add.user.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Sets bayesian rating and votes for the given product
     * @param array $data
     * @return array
     */
    protected function setBayesian(array $data)
    {
        $rating = $this->getBayesian($data);

        if (empty($rating['bayesian_rating'])) {
            $this->db->delete('rating', array('product_id' => $data['product_id']));
            return array();
        }

        $sql = 'INSERT INTO rating
                SET rating=:rating, votes=:votes, product_id=:product_id
                ON DUPLICATE KEY UPDATE rating=:rating, votes=:votes';

        $params = array(
            'product_id' => $data['product_id'],
            'votes' => $rating['this_num_votes'],
            'rating' => $rating['bayesian_rating']
        );

        $this->db->run($sql, $params);
        return $rating;
    }

    /**
     * Returns an array of rating data for the given product including the bayesian rating
     * @param array $data
     * @return array
     */
    protected function getBayesian(array $data)
    {
        $sql = "SELECT *,
                @total_votes:= (SELECT COUNT(rating_user_id) FROM rating_user) AS total_votes,
                @total_items:= (SELECT COUNT(product_id) FROM product WHERE status > 0 AND store_id=:sid) AS total_items,
                @avg_num_votes:= (@total_votes / @total_items) AS avg_num_votes,
                @avg_rating:= (SELECT AVG(rating) FROM rating_user) AS avg_rating,
                @this_num_votes:= (SELECT COUNT(rating_user_id) FROM rating_user WHERE product_id=:pid) AS this_num_votes,
                @this_rating:= (SELECT AVG(rating) FROM rating_user WHERE product_id=:pid) AS this_rating,
                ((@avg_num_votes * @avg_rating) + (@this_num_votes * @this_rating) ) / (@avg_num_votes + @this_num_votes) AS bayesian_rating
                FROM rating_user";

        return $this->db->fetch($sql, array('pid' => $data['product_id'], 'sid' => $data['store_id']));
    }

}
