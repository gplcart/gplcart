<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config;
use gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to bookmarks
 */
class Bookmark
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
     * Loads a bookmark from the database
     * @param array|int $condition
     * @return array
     */
    public function get(array $condition)
    {
        $result = null;
        $this->hook->attach('bookmark.get.before', $condition, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        if (!is_array($condition)) {
            $condition = array('bookmark_id' => $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('bookmark.get.after', $condition, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of bookmarks or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('bookmark.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(bookmark_id)';
        }

        $sql .= ' FROM bookmark WHERE bookmark_id IS NOT NULL';

        $conditions = array();

        if (isset($options['user_id'])) {
            $sql .= ' AND user_id = ?';
            $conditions[] = $options['user_id'];
        }

        if (isset($options['path'])) {
            $sql .= ' AND path = ?';
            $conditions[] = $options['path'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('created', 'path', 'title', 'user_id');

        if (isset($options['sort'])
            && in_array($options['sort'], $allowed_sort)//
            && isset($options['order'])
            && in_array($options['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY created DESC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'path'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('bookmark.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Adds a bookmark
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('bookmark.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        $data['created'] = GC_TIME;
        $result = $this->db->insert('bookmark', $data);
        $this->hook->attach('bookmark.add.after', $data, $result, $this);

        return (int) $result;
    }

    /**
     * Deletes a bookmark
     * @param array|int $condition
     * @return boolean
     */
    public function delete($condition)
    {
        $result = null;
        $this->hook->attach('bookmark.delete.before', $condition, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('bookmark_id' => $condition);
        }

        $result = (bool) $this->db->delete('bookmark', $condition);
        $this->hook->attach('bookmark.delete.after', $condition, $result, $this);

        return (bool) $result;
    }

    /**
     * Add a bookmark if it doesn't exists for the path
     * @param string $path
     * @param array $data
     * @return boolean
     */
    public function set($path, array $data)
    {
        $data += array('path' => $path);

        $bookmark = $this->get($data);

        return empty($bookmark) ? (bool) $this->add($data) : false;
    }

}
