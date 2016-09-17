<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Logger;
use core\classes\Cache;
use core\models\User as ModelsUser;
use core\models\Language as ModelsLanguage;

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
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param ModelsUser $user
     * @param ModelsLanguage $language
     * @param Logger $logger
     */
    public function __construct(ModelsUser $user, ModelsLanguage $language,
            Logger $logger)
    {
        parent::__construct();

        $this->user = $user;
        $this->logger = $logger;
        $this->language = $language;
    }

    /**
     * Adds a product to a wishlist
     * @param array $data
     * @return boolean
     */
    public function add(array $data)
    {
        $this->hook->fire('add.wishlist.before', $data);

        if (empty($data)) {
            return false;
        }

        $data += array('created' => GC_TIME);
        $data['wishlist_id'] = $this->db->insert('wishlist', $data);

        $this->hook->fire('add.wishlist.after', $data);
        return $data['wishlist_id'];
    }

    /**
     * Adds a product to a wishlist and returns detailed result
     * @param array $data
     * @return array
     */
    public function addProduct(array $data)
    {
        $this->hook->fire('add.product.wishlist.before', $data);

        if (empty($data)) {
            return array();
        }

        $existing = $this->get($data);

        if (!empty($existing)) {

            $result = array(
                'severity' => 'warning',
                'message' => $this->language->text('Product already exists in your wishlist')
            );

            $this->hook->fire('add.product.wishlist.after', $data, $result);
            return $result;
        }

        if ($this->canAdd($data['user_id'])) {

            $result = array(
                'severity' => 'success',
                'message' => $this->language->text('Product has been added to your wishlist'));

            $data['wishlist_id'] = $this->add($data);

            $this->hook->fire('add.product.wishlist.after', $data, $result);

            $this->logAddToWishlist($data);
            return $result;
        }

        $limit = $this->wishlist->getLimits($data['user_id']);

        $result = array(
            'severity' => 'warning',
            'message' => $this->language->text('Oops, you\'re exceeding %limit items', array(
                '%limit' => $limit))
        );

        $this->hook->fire('add.product.wishlist.after', $data, $result);
        return $result;
    }

    /**
     * Logs adding a product to a wishlist
     * @param array $data
     * @return boolean
     */
    protected function logAddToWishlist(array $data)
    {
        $log = array(
            'message' => 'User %uid added product #%product to wishlist',
            'variables' => array(
                '%product' => $data['product_id'],
                '%uid' => is_numeric($data['user_id']) ? $data['user_id'] : '**anonymous**'
            )
        );

        return $this->logger->log('wishlist', $log);
    }

    /**
     * Whether a user can add a product to the wishlist
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

        if (!is_numeric($user_id)) {
            return $limit; // Anonymous
        }

        $user = $this->user->get($user_id);

        if (empty($user['status'])) {
            return $limit; // Missing user
        }

        return $this->config->get("wishlist_limit_{$user['role_id']}", 20);
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

        if (isset($user_id)) {
            $result = $this->deleteByUser($user_id);
        } else {
            $result = $this->deleteMultiple($wishlist_id);
        }

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
        $cache_key = 'wishlist.' . md5(json_encode($data));

        $items = &Cache::memory($cache_key);

        if (isset($items)) {
            return $items;
        }

        $sql = 'SELECT w.*, u.name AS user_name, u.email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(w.wishlist_id)';
        }

        $sql .= ' FROM wishlist w'
                . ' LEFT JOIN user u ON(w.user_id = u.user_id)'
                . ' WHERE w.wishlist_id > 0';

        $where = array();

        if (!empty($data['product_id'])) {
            $values = (array) $data['product_id'];
            $placeholders = rtrim(str_repeat('?,', count($values)), ',');

            $sql .= ' AND w.product_id IN(' . $placeholders . ')';
            $where = array_merge($where, $values);
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND w.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND w.created = ?';
            $where[] = $data['created'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('product_id', 'user_id', 'created');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort) && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY w.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY w.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $items = $this->db->fetchAll($sql, $where, array('index' => 'wishlist_id'));
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

        if (is_numeric($condition)) {
            $sql = 'SELECT * FROM wishlist WHERE wishlist_id=?';
            $conditions = array($condition);
        }

        if (is_array($condition)) {

            $sql = 'SELECT wishlist_id'
                    . ' FROM wishlist'
                    . ' WHERE product_id=? AND user_id=?';

            $conditions = array($condition['product_id'], $condition['user_id']);
        }

        $result = array();

        if (isset($sql)) {
            $result = $this->db->fetch($sql, $conditions);
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
        $sql = 'SELECT COUNT(wishlist_id) FROM wishlist WHERE user_id=?';
        return (int) $this->db->fetchColumn($sql, array($user_id));
    }

    /**
     * Deletes wishlist items by a user
     * @param mixed $user_id
     * @return bool
     */
    protected function deleteByUser($user_id)
    {
        return (bool) $this->db->delete('wishlist', array('user_id' => $user_id));
    }

    /**
     * Deletes wishlist item(s) by one or more ID
     * @param integer|array $wishlist_id
     * @return boolean
     */
    protected function deleteMultiple($wishlist_id)
    {
        $ids = (array) $wishlist_id;
        $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
        $sql = "DELETE FROM wishlist WHERE wishlist_id IN($placeholders)";

        return (bool) $this->db->run($sql, $ids)->rowCount();
    }

}
