<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\models\User as UserModel,
    gplcart\core\models\Language as LanguageModel;
use gplcart\core\helpers\Url as UrlHelper;

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
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
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
        $result = null;
        $this->hook->attach('wishlist.get.before', $wishlist_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT * FROM wishlist WHERE wishlist_id=?';
        $result = $this->db->fetch($sql, array($wishlist_id));

        $this->hook->attach('wishlist.get.after', $wishlist_id, $result, $this);
        return $result;
    }

    /**
     * Adds a product to a wishlist
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('wishlist.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = GC_TIME;
        $result = $this->db->insert('wishlist', $data);

        gplcart_static_clear();

        $this->hook->attach('wishlist.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Adds a product to a wishlist and returns an array of result data
     * @param array $data
     * @return array
     */
    public function addProduct(array $data)
    {
        $result = array();
        $this->hook->attach('wishlist.add.product.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Unable to add to wishlist')
        );

        $href = $this->url->get('wishlist');

        if ($this->exists($data)) {
            $result['message'] = $this->language->text('Product already in your <a href="@url">wishlist</a>', array('@url' => $href));
            return $result;
        }

        if (!$this->canAdd($data['user_id'], $data['store_id'])) {
            $vars = array('%num' => $this->getLimit($data['user_id']));
            $result['message'] = $this->language->text('You\'re exceeding %num items', $vars);
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
                'message' => $this->language->text('Product has been added to your <a href="@url">wishlist</a>', array('@url' => $href)));
        }

        $this->hook->attach('wishlist.add.product.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Removes a product from wishlist and returns an array of result data
     * @param array $data
     * @return array
     */
    public function deleteProduct(array $data)
    {
        $result = array();
        $this->hook->attach('wishlist.delete.product.before', $data, $result, $this);

        if (!empty($result)) {
            return (array) $result;
        }

        $result = array(
            'redirect' => '',
            'severity' => 'warning',
            'message' => $this->language->text('Unable to delete from wishlist')
        );

        if ($this->delete($data)) {

            unset($data['product_id']);

            $existing = $this->getList($data);

            $result = array(
                'message' => '',
                'severity' => 'success',
                'quantity' => count($existing),
                'redirect' => empty($existing) ? 'wishlist' : ''
            );
        }

        $this->hook->attach('wishlist.delete.product.after', $data, $result, $this);
        return (array) $result;
    }

    /**
     * Whether a user can add a product to wishlist
     * @param integer|string $user_id
     * @param integer $store_id
     * @return boolean
     */
    public function canAdd($user_id, $store_id)
    {
        if ($this->user->isSuperadmin($user_id)) {
            return true; // No limits for superadmin
        }

        $limit = $this->getLimit($user_id);

        if (empty($limit)) {
            return true;
        }

        $conditions = array(
            'user_id' => $user_id,
            'store_id' => $store_id
        );

        $existing = $this->getList($conditions);
        
        return count($existing) < $limit;
    }

    /**
     * Returns a max number of items in the user wishlist
     * @param integer|string $user_id
     * @return integer
     */
    public function getLimit($user_id)
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
        $result = null;
        $this->hook->attach('wishlist.delete.before', $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('wishlist', $data);

        gplcart_static_clear();

        $this->hook->attach('wishlist.delete.after', $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns an array of wishlist items
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $items = &gplcart_static(array(__METHOD__ => $data));

        if (isset($items)) {
            return $items;
        }

        $sql = 'SELECT w.*, u.name AS user_name, u.email';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(w.wishlist_id)';
        }

        $sql .= ' FROM wishlist w'
                . ' LEFT JOIN user u ON(w.user_id = u.user_id)'
                . ' LEFT JOIN product p ON(w.product_id = p.product_id)'
                . ' WHERE w.wishlist_id > 0';

        $where = array();

        if (!empty($data['product_id'])) {
            settype($data['product_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['product_id'])), ',');
            $sql .= " AND w.product_id IN($placeholders)";
            $where = array_merge($where, $data['product_id']);
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

        if (isset($data['product_status'])) {
            $sql .= ' AND p.status = ?';
            $where[] = (int) $data['product_status'];
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
        $this->hook->attach('wishlist.list', $items, $this);

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
