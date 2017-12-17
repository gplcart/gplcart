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
     * Returns an array of bookmarks or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(bookmark_id)';
        }

        $sql .= ' FROM bookmark WHERE bookmark_id IS NOT NULL';

        $conditions = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND user_id = ?';
            $conditions[] = $data['user_id'];
        }

        if (isset($data['path'])) {
            $sql .= ' AND path = ?';
            $conditions[] = $data['path'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('created', 'path', 'title', 'user_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $list = $this->db->fetchAll($sql, $conditions, array('index' => 'path'));
        $this->hook->attach('bookmark.list', $list, $this);
        return $list;
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
     * Loads a bookmark from the database
     * @param array $conditions
     * @return array
     */
    public function get(array $conditions)
    {
        $result = null;
        $this->hook->attach('bookmark.get.before', $conditions, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $list = $this->getList($conditions);

        $result = array();
        if (is_array($list) && count($list) == 1) {
            $result = reset($list);
        }

        $this->hook->attach('bookmark.get.after', $conditions, $result, $this);
        return (array) $result;
    }

    /**
     * Deletes a bookmark
     * @param array $conditions
     * @return boolean
     */
    public function delete(array $conditions)
    {
        $result = null;
        $this->hook->attach('bookmark.delete.before', $conditions, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = (bool) $this->db->delete('bookmark', $conditions);
        $this->hook->attach('bookmark.delete.after', $conditions, $result, $this);
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
