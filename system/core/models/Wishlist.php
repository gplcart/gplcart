<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\User as UserModel;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to user wishlists
 */
class Wishlist extends Model
{

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Constructor
     * @param UserModel $user
     * @param LanguageModel $language
     * @param UrlHelper $url
     */
    public function __construct(UserModel $user, LanguageModel $language,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->user = $user;
        $this->language = $language;
    }

    /**
     * Returns a wishlist
     * @param integer $wishlist_id
     * @return array
     */
    public function get($wishlist_id)
    {
        $this->hook->fire('get.wishlist.before', $wishlist_id);

        $sql = 'SELECT * FROM wishlist WHERE wishlist_id=?';
        $wishlist = $this->db->fetch($sql, array($wishlist_id));

        $this->hook->fire('get.wishlist.after', $wishlist);
        return $wishlist;
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

        $data['created'] = GC_TIME;
        $data['wishlist_id'] = $this->db->insert('wishlist', $data);

        Cache::clearMemory();

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

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Product has not been added to your wishlist')
        );

        if (empty($data)) {
            return $result;
        }

        $href = $this->url->get('wishlist');

        if ($this->exists($data)) {
            $result['message'] = $this->language->text('Product already exists in your <a href="!href">wishlist</a>', array('!href' => $href));
            return $result;
        }

        if (!$this->canAdd($data['user_id'], $data['store_id'])) {
            $vars = array('%limit' => $this->getLimits($data['user_id']));
            $result['message'] = $this->language->text('You\'re exceeding %limit items', $vars);
            return $result;
        }

        $wishlist_id = $this->add($data);

        if (!empty($wishlist_id)) {

            $options = array(
                'user_id' => $data['user_id'],
                'store_id' => $data['store_id']
            );

            $exists = $this->getList($options);

            $result = array(
                'redirect' => '',
                'severity' => 'success',
                'quantity' => count($exists),
                'wishlist_id' => $wishlist_id,
                'message' => $this->language->text('Product has been added to your <a href="!href">wishlist</a>', array('!href' => $href)));
        }

        $this->hook->fire('add.product.wishlist.after', $data, $result);
        return $result;
    }

    /**
     * Removes a product from wishlist and returns an array of result data
     * @param array $data
     * @return array
     */
    public function deleteProduct(array $data)
    {
        $this->hook->fire('delete.product.wishlist.before', $data);

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Product has not been deleted from wishlist')
        );

        if (empty($data)) {
            return $result;
        }

        $deleted = (bool) $this->delete($data);

        if ($deleted) {

            unset($data['product_id']);

            $existing = $this->getList($data);

            $result = array(
                'message' => '',
                'redirect' => '',
                'severity' => 'success',
                'quantity' => count($existing),
                'redirect' => empty($existing) ? 'wishlist' : ''
            );
        }

        $this->hook->fire('delete.product.wishlist.after', $data, $result);
        return $result;
    }

    /**
     * Whether a user can add a product to the wishlist
     * @param integer|string $user_id
     * @param integer $store_id
     * @return boolean
     */
    public function canAdd($user_id, $store_id)
    {
        if ($this->user->isSuperadmin($user_id)) {
            return true; // No limits for superadmin
        }

        $limit = $this->getLimits($user_id);

        if (empty($limit)) {
            return true;
        }

        $conditions = array(
            'user_id' => $user_id,
            'store_id' => $store_id
        );

        $existing = $this->getList($conditions);
        return (count($existing) < $limit);
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
     * @param array $data
     * @return boolean
     */
    public function delete(array $data)
    {
        $this->hook->fire('delete.wishlist.before', $data);

        if (empty($data)) {
            return false;
        }

        $result = (bool) $this->db->delete('wishlist', $data);

        Cache::clearMemory();

        $this->hook->fire('delete.wishlist.after', $data, $result);
        return (bool) $result;
    }

    /**
     * Returns an array of wishlist items
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $items = &Cache::memory(array('wishlist' => $data));

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

        if (isset($data['store_id'])) {
            $sql .= ' AND w.store_id = ?';
            $where[] = (int) $data['store_id'];
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
     * Whether a product ID is in a wishlist
     * @param array $data
     * @return boolean
     */
    public function exists(array $data)
    {
        if (empty($data['product_id'])) {
            return false;
        }

        $product_id = $data['product_id'];

        unset($data['product_id']);

        $items = (array) $this->getList($data);

        foreach ($items as $item) {
            if ($item['product_id'] == $product_id) {
                return true;
            }
        }

        return false;
    }

}
