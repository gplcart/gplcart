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
use core\models\User;
use core\Hook;
use core\classes\Cache;
use core\Config;

class Bookmark
{

    /**
     * User model instance
     * @var \core\models\User $user
     */
    protected $user;

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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param User $user
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(User $user, Hook $hook, Config $config)
    {
        $this->user = $user;
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->db();
    }

    /**
     * Adds a bookmark to the database
     * @param array $data
     * @param boolean $check
     * @return boolean
     */
    public function add(array $data, $check = false)
    {
        $this->hook->fire('add.bookmark.before', $data);

        if (empty($data)) {
            return false;
        }

        $values = array(
            'id_key' => !empty($data['id_key']) ? $data['id_key'] : '',
            'id_value' => !empty($data['id_value']) ? (int) $data['id_value'] : 0,
            'user_id' => $data['user_id'], // number or string
            'title' => !empty($data['title']) ? $data['title'] : '',
            'created' => !empty($data['created']) ? (int) $data['created'] : GC_TIME,
            'url' => !empty($data['url']) ? $data['url'] : ''
        );

        if ($check && !$this->canAdd($data['user_id'])) {
            return false; // Limits reached
        }

        if ($check && $this->get($values)) {
            return false; // Already exists
        }

        $bookmark_id = $this->db->insert('bookmark', $values);
        $this->hook->fire('add.bookmark.after', $data, $bookmark_id);
        return $bookmark_id;
    }

    /**
     * Whether a user can add a bookmark
     * @param integer $user_id
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
        $limit = $this->config->get('bookmark_limit', 20);

        if (is_numeric($user_id)) {

            $user = $this->user->get($user_id);
            if (empty($user['status'])) {
                return $limit; // Missing / blocked user
            }

            $limit = $this->config->get("bookmark_limit_{$user['role_id']}", 20);
        }

        return $limit;
    }

    /**
     * Returns a number of bookmarks for a given user
     * @param integer|string $user_id
     * @return integer
     */
    protected function countByUser($user_id)
    {
        $sth = $this->db->prepare('SELECT COUNT(bookmark_id) FROM bookmark WHERE user_id=:user_id');
        $sth->execute(array(':user_id' => $user_id));
        return (int) $sth->fetchColumn();
    }

    /**
     * Deletes a bookmark
     * @param integer $bookmark_id
     * @param mixed $user_id
     * @return boolean
     */
    public function delete($bookmark_id, $user_id = null)
    {
        $this->hook->fire('delete.bookmark.before', $bookmark_id, $user_id);

        if (empty($bookmark_id)) {
            return false;
        }

        $result = isset($user_id) ? $this->deleteByUser($user_id) : $this->deleteMultiple($bookmark_id);
        $this->hook->fire('delete.bookmark.after', $bookmark_id, $user_id, $result);
        return (bool) $result;
    }

    /**
     * Deletes bookmarks by a user
     * @param mixed $user_id
     * @return integer
     */
    protected function deleteByUser($user_id)
    {
        return $this->db->delete('bookmark', array('user_id' => $user_id));
    }

    /**
     * Deletes bookmark(s) by one or more bookmark ID
     * @param integer|array $bookmark_id
     * @return integer
     */
    protected function deleteMultiple($bookmark_id)
    {
        $bookmark_ids = (array) $bookmark_id;
        $placeholders = rtrim(str_repeat('?, ', count($bookmark_ids)), ', ');
        $sth = $this->db->prepare("DELETE FROM bookmark WHERE bookmark_id IN($placeholders)");
        $sth->execute($bookmark_ids);
        return $sth->rowCount();
    }

    /**
     * Returns an array of bookmarks
     * @param array $data
     * @return array
     */
    public function getList(array $data = array())
    {
        $cache_key = 'bookmarks.' . md5(serialize($data));

        $bookmarks = &Cache::memory($cache_key);

        if (isset($bookmarks)) {
            return $bookmarks;
        }

        $bookmarks = array();

        $sql = 'SELECT b.*, u.name AS user_name, u.email ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(b.bookmark_id) ';
        }

        $sql .= '
            FROM bookmark b
            LEFT JOIN user u ON(b.user_id = u.user_id)
            WHERE bookmark_id > 0';

        $where = array();

        if (isset($data['type'])) {
            if ($data['type'] == 'product') {
                $sql .= ' AND b.id_key = ? AND b.id_value > 0';
                $where[] = 'product_id';
            } else if ($data['type'] === 'url') {
                $sql .= ' AND LENGTH(b.id_key) = 0';
            }
        }

        if (!empty($data['id_key'])) {
            $sql .= ' AND b.id_key = ?';
            $where[] = $data['id_key'];
        }

        if (!empty($data['id_value'])) {
            $values = (array) $data['id_value'];
            $placeholders = rtrim(str_repeat('?, ', count($values)), ', ');
            $sql .= ' AND b.id_value IN(' . $placeholders . ')';
            $where = array_merge($where, $values);
        }

        if (isset($data['user_id'])) {
            $sql .= ' AND b.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['created'])) {
            $sql .= ' AND b.created = ?';
            $where[] = (int) $data['created'];
        }

        if (!empty($data['title'])) {
            $sql .= ' AND b.title LIKE ?';
            $where[] = "%{$data['title']}%";
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {

            $allowed_sort = array('id_value', 'user_id', 'created', 'title');

            if (in_array($data['sort'], $allowed_sort, true)) {
                $sql .= " ORDER BY b.{$data['sort']} {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY b.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $bookmark) {
            $bookmarks[$bookmark['bookmark_id']] = $bookmark;
        }

        $this->hook->fire('bookmarks', $bookmarks);
        return $bookmarks;
    }

    /**
     * Returns a bookmark from the database
     * @param integer|array $condition
     * @return array
     */
    public function get($condition)
    {
        $this->hook->fire('get.bookmark.before', $condition);

        $result = array();

        if (is_numeric($condition)) {
            $sth = $this->db->prepare('SELECT * FROM bookmark WHERE bookmark_id=:bookmark_id');
            $sth->execute(array(':bookmark_id' => $condition));
            $result = $sth->fetch(PDO::FETCH_ASSOC);
        }

        if (is_array($condition)) {

            $sql = '
            SELECT bookmark_id
            FROM bookmark 
            WHERE id_key=:id_key AND id_value=:id_value AND user_id=:user_id AND url=:url';

            $sth = $this->db->prepare($sql);

            $sth->execute(array(
                ':id_key' => $condition['id_key'],
                ':id_value' => $condition['id_value'],
                ':user_id' => $condition['user_id'],
                ':url' => $condition['url']
            ));

            $result = $sth->fetch(PDO::FETCH_ASSOC);
        }

        $this->hook->fire('get.bookmark.after', $condition, $result);
        return $result;
    }
    
    /**
     * Whether an ID is bookmarked
     * @param integer $id_value
     * @param array $conditions
     * @return boolean
     */
    public function exists($id_value, $conditions)
    {
        foreach ($this->getList($conditions) as $item) {
            if ($item['id_value'] == $id_value) {
                return true;
            }
        }

        return false;
    }

}
