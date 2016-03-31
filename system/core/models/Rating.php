<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;

class Rating
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * PDO instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * Constructor
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(Hook $hook, Config $config)
    {
        $this->hook = $hook;
        $this->db = $config->db();
    }

    /**
     * Returns an array of rating data for the given product
     * @param integer $product_id
     * @param boolean $load
     * @return array|float
     */
    public function getByProduct($product_id, $load = false)
    {
        $user_id = null;
        $this->hook->fire('get.rating.before', $product_id, $user_id);

        $sth = $this->db->prepare('SELECT rating, votes FROM rating WHERE product_id=:product_id');
        $sth->execute(array(':product_id' => (int) $product_id));
        $result = $load ? $sth->fetch(PDO::FETCH_ASSOC) : $sth->fetchColumn();

        $this->hook->fire('get.rating.after', $product_id, $user_id, $result);

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
        $this->hook->fire('get.rating.before', $product_id, $user_id);

        $user_ids = (array) $user_id;
        $placeholders = rtrim(str_repeat('?, ', count($user_ids)), ', ');

        $sql = "SELECT * FROM rating_user WHERE user_id IN($placeholders) AND product_id=?";
        $sth = $this->db->prepare($sql);
        $sth->execute(array_merge($user_ids, array($product_id)));

        $ratings = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $rating) {
            $ratings[$rating['user_id']] = $rating;
        }

        if (!is_array($user_id) && isset($ratings[$user_id])) {
            $ratings = $ratings[$user_id];
        }

        $this->hook->fire('get.rating.after', $product_id, $user_id, $ratings);

        return $ratings;
    }

    /**
     * Sets an rating for the given user and product
     * @param integer $product_id
     * @param string $user_id
     * @param float|integer $rating
     * @return integer
     */
    public function set($product_id, $user_id, $rating)
    {
        $this->hook->fire('set.rating.before', $product_id, $user_id, $rating);

        $this->db->delete('rating_user', array('product_id' => $product_id, 'user_id' => $user_id));

        $this->addUser($product_id, $user_id, $rating);

        return $this->setBayesian($product_id);
    }

    /**
     * Adds a user rating
     * @param integer $product_id
     * @param string $user_id
     * @param float|integer $rating
     * @return boolean|integer
     */
    protected function addUser($product_id, $user_id, $rating)
    {
        if (!$rating) {
            return false;
        }

        $values = array(
            'product_id' => (int) $product_id,
            'user_id' => $user_id,
            'rating' => (float) $rating
        );

        return $this->db->insert('rating_user', $values);
    }

    /**
     * Sets the bayesian rating and votes for the given product
     * @param integer $product_id
     * @return array
     */
    protected function setBayesian($product_id)
    {
        $rating = $this->getBayesian($product_id);

        $sql = "INSERT INTO rating SET rating=:rating, votes=:votes, product_id=:product_id
               ON DUPLICATE KEY UPDATE rating=:rating, votes=:votes";

        $sth = $this->db->prepare($sql);

        $sth->execute(array(
            ':rating' => $rating['bayesian_rating'],
            ':votes' => $rating['this_num_votes'],
            ':product_id' => $product_id));

        return $rating;
    }

    /**
     * Returns an array of rating data for the given product including the bayesian rating
     * @param integer $product_id
     * @return array
     */
    protected function getBayesian($product_id)
    {
        $sql = "
            SELECT *, ROUND((((result.avg_num_votes * result.avg_rating) + (result.this_num_votes * result.this_rating)) / (result.avg_num_votes + result.this_num_votes)), 1) AS bayesian_rating
            FROM (SELECT product_id, (SELECT COUNT(product_id) FROM rating_user) / (SELECT COUNT(DISTINCT product_id) FROM rating_user) AS avg_num_votes,
            (SELECT AVG(rating) FROM rating_user) AS avg_rating, COUNT(product_id) as this_num_votes, AVG(rating) as this_rating FROM rating_user
            WHERE product_id=:product_id GROUP BY product_id) AS result;
        ";

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':product_id' => $product_id));

        return $sth->fetch(PDO::FETCH_ASSOC);
    }
}
