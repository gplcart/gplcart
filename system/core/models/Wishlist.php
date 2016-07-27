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
use core\classes\Cache;
use core\models\User as ModelsUser;

/**
 * Manages basic behaviors and data related to user wishlists
 */
class Wishlist extends Model
{

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param ModelsUser $user
     */
    public function __construct(ModelsUser $user)
    {
        parent::__construct();

        $this->user = $user;
    }

    /**
     * Adds a product to a wishlist
     * @param array $data
     * @param boolean $check
     * @return boolean
     */
    public function add(array $data, $check = false)
    {
        $this->hook->fire('add.wishlist.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'product_id' => (int) $data['product_id'],
            'user_id' => $data['user_id'], // number or string
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME
        );

        if ($check && !$this->canAdd($data['user_id'])) {
            return false; // Limits reached
        }

        if ($check && $this->get($values)) {
            return false; // Already exists
        }

        $wishlist_id = $this->db->insert('wishlist', $values);
        $this->hook->fire('add.wishlist.after', $data, $wishlist_id);
        return $wishlist_id;
    }

    /**
     * Whether a user can add a product to wishlist
     * @param integer|null $user_id
     * @return boolean
     */
    public function canAdd($user_id = null)
    {
        if (!isset($user_id)) {
            $user_id = $this->user->id();
        }

        if ($this->user->isSuperadmin($user_id)) {
            return true; // No limits for superadmin
        }

        $limit = $this->getLimits($user_id);

        if (empty($limit)) {
            return true;
        }

        return ($this->countByUser($user_id) < $limit);
    }

    /**
     * Returns max allowed number of items in the user wishlist
     * @param integer|string $user_id
     * @return integer
     */
    public function getLimits($user_id)
    {
        $limit = $this->config->get('wishlist_limit', 20);

        if (is_numeric($user_id)) {
            $user = $this->user->get($user_id);
            if (empty($user['status'])) {
                return $limit; // Missing / blocked user
            }

            $limit = $this->config->get("wishlist_limit_{$user['role_id']}", 20);
        }

        return $limit;
    }

    /**
     * Deletes a wishlist item
     * @param integer $wishlist_id
     * @param mixed $user_id
     * @return boolean
     */
    public function delete($wishlist_id, $user_id = null)
    {
        $this->hook->fire('delete.wishlist.before', $wishlist_id, $user_id);

        if (empty($wishlist_id)) {
            return false;
        }

        $result = isset($user_id) ? $this->deleteByUser($user_id) : $this->deleteMultiple($wishlist_id);
        $this->hook->fire('delete.wishlist.after', $wishlist_id, $user_id, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of wishlist items
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $cache_key = 'wishlist.' . md5(serialize($data));

        $items = &Cache::memory($cache_key);

        if (isset($items)) {
            return $items;
        }

        $items = array();

        $sql = 'SELECT w.*, u.name AS user_name, u.email ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(w.wishlist_id) ';
        }

        $sql .= '
            FROM wishlist w
            LEFT JOIN user u ON(w.user_id = u.user_id)
            WHERE w.wishlist_id > 0';

        $where = array();

        if (!empty($data['product_id'])) {
            $values = (array) $data['product_id'];
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql .= ' AND w.product_id IN(' . $placeholders . ')';
            $where = array_merge($where, $values);
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND w.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND w.created = ?';
            $where[] = (int) $data['created'];
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            $allowed_sort = array('product_id', 'user_id', 'created');

            if (in_array($data['sort'], $allowed_sort, true)) {
                $sql .= " ORDER BY w.{$data['sort']} {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY w.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return (int) $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $item) {
            $items[$item['wishlist_id']] = $item;
        }

        $this->hook->fire('wishlist', $items);

        return $items;
    }

    /**
     * Returns a wishlist item from the database
     * @param integer|array $condition
     * @return array
     */
    public function get($condition)
    {
        $this->hook->fire('get.wishlist.before', $condition);

        $result = array();

        if (is_numeric($condition)) {
            $sth = $this->db->prepare('SELECT * FROM wishlist WHERE wishlist_id=:wishlist_id');
            $sth->execute(array(':wishlist_id' => $condition));
            $result = $sth->fetch(PDO::FETCH_ASSOC);
        }

        if (is_array($condition)) {

            $sql = 'SELECT wishlist_id'
                    . ' FROM wishlist'
                    . ' WHERE product_id=:product_id AND user_id=:user_id';

            $sth = $this->db->prepare($sql);

            $sth->execute(array(
                ':product_id' => $condition['product_id'],
                ':user_id' => $condition['user_id']
            ));

            $result = $sth->fetch(PDO::FETCH_ASSOC);
        }

        $this->hook->fire('get.wishlist.after', $condition, $result);
        return $result;
    }

    /**
     * Whether a product ID is in a wishlist
     * @param integer $product_id
     * @param array $conditions
     * @return boolean
     */
    public function exists($product_id, $conditions)
    {
        foreach ($this->getList($conditions) as $item) {
            if ($item['product_id'] == $product_id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a number of wishlist items for a given user
     * @param integer|string $user_id
     * @return integer
     */
    protected function countByUser($user_id)
    {
        $sth = $this->db->prepare('SELECT COUNT(wishlist_id) FROM wishlist WHERE user_id=:user_id');
        $sth->execute(array(':user_id' => $user_id));
        return (int) $sth->fetchColumn();
    }

    /**
     * Deletes wishlist items by a user
     * @param mixed $user_id
     * @return integer
     */
    protected function deleteByUser($user_id)
    {
        return $this->db->delete('wishlist', array('user_id' => $user_id));
    }

    /**
     * Deletes wishlist item(s) by one or more ID
     * @param integer|array $wishlist_id
     * @return integer
     */
    protected function deleteMultiple($wishlist_id)
    {
        $wishlist_ids = (array) $wishlist_id;
        $placeholders = rtrim(str_repeat('?, ', count($wishlist_ids)), ', ');
        $sth = $this->db->prepare("DELETE FROM wishlist WHERE wishlist_id IN($placeholders)");
        $sth->execute($wishlist_ids);
        
        return $sth->rowCount();
    }

}
