<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Handler;
use core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to backups
 */
class Backup extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
    }

    /**
     * Returns an array of backups or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT b.*, u.name AS user_name';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(b.backup_id)';
        }

        $sql .= ' FROM backup b'
                . ' LEFT JOIN user u ON(b.user_id = u.user_id)'
                . ' WHERE b.backup_id > 0';

        $where = array();

        if (isset($data['user_id'])) {
            $sql .= ' AND b.user_id = ?';
            $where[] = $data['user_id'];
        }

        if (isset($data['module_id'])) {
            $sql .= ' AND b.module_id = ?';
            $where[] = $data['module_id'];
        }

        if (isset($data['name'])) {
            $sql .= ' AND b.name LIKE ?';
            $where[] = "%{$data['name']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('name', 'user_id', 'version',
            'module_id', 'backup_id', 'type', 'created');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)
        ) {
            $sql .= " ORDER BY b.{$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY b.created DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $results = $this->db->fetchAll($sql, $where, array('index' => 'backup_id'));
        $this->hook->fire('backups', $results);
        return $results;
    }

    /**
     * Adds a backup to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('add.backup.before', $data);

        if (empty($data)) {
            return false;
        }

        $data['created'] = GC_TIME;

        $id = $this->db->insert('backup', $data);
        $this->hook->fire('add.backup.after', $data, $id);
        return $id;
    }

    /**
     * Loads a backup from the database
     * @param integer $id
     * @return array
     */
    public function get($id)
    {
        $sql = 'SELECT * FROM backup WHERE backup_id=?';
        return $this->db->fetch($sql, array($id));
    }

    /**
     * Deletes a backup from disk and database
     * @param integer $id
     * @return boolean
     */
    public function delete($id)
    {
        $this->hook->fire('delete.backup.before', $id);

        if (empty($id)) {
            return false;
        }

        $deleted_file = $this->deleteZip($id);

        if (!$deleted_file) {
            return false;
        }

        $conditions = array('backup_id' => $id);
        $result = $this->db->delete('backup', $conditions);

        $this->hook->fire('delete.backup.after', $id, $result);
        return (bool) $result;
    }

    /**
     * Deletes a backup ZIP archive
     * @param integer $backup_id
     * @return boolean
     */
    protected function deleteZip($backup_id)
    {
        $backup = $this->get($backup_id);
        return unlink(GC_FILE_DIR . "/{$backup['path']}");
    }

    /**
     * Performs backup operation for a given handler
     * @param string $handler_id
     * @param array $data
     * @return boolean|string Returns TRUE on success or an error message
     */
    public function backup($handler_id, $data)
    {
        $handlers = $this->getHandlers();
        return Handler::call($handlers, $handler_id, 'backup', array($data));
    }

    /**
     * Performs restore operation for a given handler
     * @param string $handler_id
     * @param array $data
     * @return boolean|string Returns TRUE on success or an error message
     */
    public function restore($handler_id, $data)
    {
        $handlers = $this->getHandlers();
        return Handler::call($handlers, $handler_id, 'restore', array($data));
    }

    /**
     * Returns an array of backup handlers
     * @return array
     */
    public function getHandlers()
    {
        $handlers = &gplcart_cache('backup.handles');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = $this->getDefaultHandlers();
        $this->hook->fire('backup.handlers', $handlers);
        return $handlers;
    }

    /**
     * Returns a single handler
     * @param string $handler_id
     * @return array
     */
    public function getHandler($handler_id)
    {
        $handlers = $this->getHandlers();
        return empty($handlers[$handler_id]) ? array() : $handlers[$handler_id];
    }

    /**
     * Returns an array of default backup handlers
     * @return array
     */
    protected function getDefaultHandlers()
    {
        $handlers = array();

        $handlers['module'] = array(
            'name' => $this->language->text('Module'),
            'access' => array(
                'backup' => 'backup_module',
                'restore' => 'backup_restore_module'
            ),
            'handlers' => array(
                'backup' => array('core\\handlers\\backup\\Module', 'backup'),
                'restore' => array('core\\handlers\\backup\\Module', 'restore')
        ));

        return $handlers;
    }

}
