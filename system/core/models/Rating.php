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
 * Manages basic behaviors and data related to rating system
 */
class Rating extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Returns an array of rating data for a given product
     * @param integer $product_id
     * @return array
     */
    public function getByProduct($product_id)
    {
        $this->hook->fire('rating.get.before', $product_id, $this);
        $result = $this->db->fetch('SELECT rating, votes FROM rating WHERE product_id=?', array($product_id));
        $this->hook->fire('rating.get.after', $product_id, $result, $this);

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
        $this->hook->fire('rating.get.user.before', $product_id, $user_id, $this);

        $user_ids = (array) $user_id;
        $placeholders = rtrim(str_repeat('?,', count($user_ids)), ',');

        $sql = "SELECT * FROM rating_user WHERE user_id IN($placeholders) AND product_id=?";

        $conditions = array_merge($user_ids, array($product_id));
        $ratings = $this->db->fetchAll($sql, $conditions, array('index' => 'user_id'));

        if (!is_array($user_id) && isset($ratings[$user_id])) {
            $ratings = $ratings[$user_id];
        }

        $this->hook->fire('rating.get.user.after', $product_id, $user_id, $ratings, $this);
        return $ratings;
    }

    /**
     * Sets a rating for the given user and product
     * @param array $data
     * @return boolean|integer
     */
    public function set(array $data)
    {
        $this->hook->fire('rating.set.before', $data, $this);

        if (empty($data)) {
            return false;
        }

        $conditions = array(
            'user_id' => $data['user_id'],
            'product_id' => $data['product_id']
        );

        $this->db->delete('rating_user', $conditions);

        $this->addByUser($data);

        $result = $this->setBayesian($data['product_id']);

        $this->hook->fire('rating.set.after', $data, $result, $this);
        return $result;
    }

    /**
     * Adds a user rating
     * @param array $data
     * @return boolean
     */
    protected function addByUser(array $data)
    {
        $this->hook->fire('rating.add.user.before', $data, $this);

        if (empty($data)) {
            return false;
        }

        $result = (bool) $this->db->insert('rating_user', $data);
        $this->hook->fire('rating.add.user.after', $data, $result, $this);

        return $result;
    }

    /**
     * Sets bayesian rating and votes for the given product
     * @param integer $product_id
     * @return array
     */
    protected function setBayesian($product_id)
    {
        $rating = $this->getBayesian($product_id);

        $sql = 'INSERT INTO rating'
                . ' SET rating=:rating, votes=:votes, product_id=:product_id'
                . ' ON DUPLICATE KEY UPDATE rating=:rating, votes=:votes';

        $params = array(
            'product_id' => $product_id,
            'votes' => $rating['this_num_votes'],
            'rating' => $rating['bayesian_rating']
        );

        $this->db->run($sql, $params);
        return $rating;
    }

    /**
     * Returns an array of rating data for the given product including the bayesian rating
     * @param integer $product_id
     * @return array
     */
    protected function getBayesian($product_id)
    {
        $sql = 'SELECT *,'
                . ' ROUND((((result.avg_num_votes * result.avg_rating)'
                . ' + (result.this_num_votes * result.this_rating))'
                . ' / (result.avg_num_votes + result.this_num_votes)), 1) AS bayesian_rating'
                . ' FROM (SELECT product_id,'
                . ' (SELECT COUNT(product_id) FROM rating_user)'
                . ' / (SELECT COUNT(DISTINCT product_id) FROM rating_user) AS avg_num_votes,'
                . ' (SELECT AVG(rating) FROM rating_user) AS avg_rating,'
                . ' COUNT(product_id) as this_num_votes,'
                . ' AVG(rating) AS this_rating'
                . ' FROM rating_user'
                . ' WHERE product_id=?'
                . ' GROUP BY product_id) AS result;';

        return $this->db->fetch($sql, array($product_id));
    }

}
