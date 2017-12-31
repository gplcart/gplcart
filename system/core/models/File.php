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
use gplcart\core\models\Translation as TranslationModel,
    gplcart\core\models\TranslationEntity as TranslationEntityModel;
use gplcart\core\traits\Translation as TranslationTrait;

/**
 * Manages basic behaviors and data related to files
 */
class File
{

    use TranslationTrait;

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
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Translation entity model instance
     * @var \gplcart\core\models\TranslationEntity $translation_entity
     */
    protected $translation_entity;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param TranslationModel $translation
     * @param TranslationEntityModel $translation_entity
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation,
            TranslationEntityModel $translation_entity)
    {
        $this->hook = $hook;
        $this->db = $config->getDb();
        $this->translation = $translation;
        $this->translation_entity = $translation_entity;
    }

    /**
     * Adds a file to the database
     * @param array $data
     * @return integer
     */
    public function add(array $data)
    {
        $result = null;
        $this->hook->attach('file.add.before', $data, $result, $this);

        if (isset($result)) {
            return (int) $result;
        }

        if (empty($data['mime_type'])) {
            $data['mime_type'] = mime_content_type(gplcart_file_absolute($data['path']));
        }

        if (empty($data['file_type'])) {
            $data['file_type'] = strtok($data['mime_type'], '/');
        }

        if (empty($data['title'])) {
            $data['title'] = basename($data['path']);
        }

        $data['created'] = $data['modified'] = GC_TIME;
        $result = $data['file_id'] = $this->db->insert('file', $data);

        $this->setTranslations($data, $this->translation_entity, 'file', false);

        $this->hook->attach('file.add.after', $data, $result, $this);
        return (int) $result;
    }

    /**
     * Updates a file
     * @param integer $file_id
     * @param array $data
     * @return boolean
     */
    public function update($file_id, array $data)
    {
        $result = null;
        $this->hook->attach('file.update.before', $file_id, $data, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $data['modified'] = GC_TIME;
        $updated = $this->db->update('file', $data, array('file_id' => $file_id));
        $data['file_id'] = $file_id;
        $updated += (int) $this->setTranslations($data, $this->translation_entity, 'file');

        $result = $updated > 0;
        $this->hook->attach('file.update.after', $file_id, $data, $result, $this);
        return (bool) $result;
    }

    /**
     * Returns a file from the database
     * @param int|array $condition
     * @return array
     */
    public function get($condition)
    {
        $result = null;
        $this->hook->attach('file.get.before', $condition, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!is_array($condition)) {
            $condition = array('file_id' => (int) $condition);
        }

        $condition['limit'] = array(0, 1);
        $list = (array) $this->getList($condition);
        $result = empty($list) ? array() : reset($list);

        $this->hook->attach('file.get.after', $condition, $result, $this);
        return $result;
    }

    /**
     * Returns an array of files or counts them
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $options += array('language' => $this->translation->getLangcode());

        $result = &gplcart_static(gplcart_array_hash(array('file.list' => $options)));

        if (isset($result)) {
            return $result;
        }

        $this->hook->attach('file.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT f.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(f.file_id)';
        }

        $sql .= ' FROM file f'
                . ' LEFT JOIN file_translation ft ON(ft.file_id = f.file_id AND ft.language=?)';

        $conditions = array($options['language']);

        if (!empty($options['file_id'])) {
            settype($options['file_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['file_id'])), ',');
            $sql .= " WHERE f.file_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['file_id']);
        } else {
            $sql .= ' WHERE f.file_id IS NOT NULL';
        }

        if (isset($options['title'])) {
            $sql .= ' AND (f.title LIKE ? OR (ft.title LIKE ? AND ft.language=?))';
            $conditions[] = "%{$options['title']}%";
            $conditions[] = "%{$options['title']}%";
            $conditions[] = $options['language'];
        }

        if (isset($options['created'])) {
            $sql .= ' AND f.created = ?';
            $conditions[] = (int) $options['created'];
        }

        if (isset($options['entity'])) {
            $sql .= ' AND f.entity = ?';
            $conditions[] = $options['entity'];
        }

        if (!empty($options['entity_id'])) {
            settype($options['entity_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['entity_id'])), ',');
            $sql .= " AND f.entity_id IN($placeholders)";
            $conditions = array_merge($conditions, $options['entity_id']);
        }

        if (isset($options['path'])) {
            $sql .= ' AND f.path LIKE ?';
            $conditions[] = "%{$options['path']}%";
        }

        if (isset($options['mime_type'])) {
            $sql .= ' AND f.mime_type LIKE ?';
            $conditions[] = "%{$options['mime_type']}%";
        }

        if (isset($options['file_type'])) {
            $sql .= ' AND f.file_type = ?';
            $conditions[] = $options['file_type'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'path', 'file_id', 'created',
            'weight', 'mime_type', 'entity', 'entity_id');

        if (isset($options['sort']) && in_array($options['sort'], $allowed_sort)//
                && isset($options['order']) && in_array($options['order'], $allowed_order)) {
            $sql .= " ORDER BY f.{$options['sort']} {$options['order']}";
        } else {
            $sql .= " ORDER BY f.modified DESC";
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'file_id'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('file.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Deletes a file from the database
     * @param int|array $condition
     * @param bool $check
     * @return boolean
     */
    public function delete($condition, $check = true)
    {
        $result = null;
        $this->hook->attach('file.delete.before', $condition, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (!is_array($condition)) {
            $condition = array('file_id' => (int) $condition);
        }

        if ($check && isset($condition['file_id']) && !$this->canDelete($condition['file_id'])) {
            return false;
        }

        $result = (bool) $this->db->delete('file', $condition);

        if ($result && isset($condition['file_id'])) {
            $this->deleteLinked($condition['file_id']);
        }

        $this->hook->attach('file.delete.after', $condition, $check, $result, $this);
        return (bool) $result;
    }

    /**
     * Delete all database records related to the file ID
     * @param int $file_id
     */
    protected function deleteLinked($file_id)
    {
        $this->db->delete('file_translation', array('file_id' => $file_id));
    }

    /**
     * Deletes a file from disk
     * @param array|int $file
     * @return boolean
     */
    public function deleteFromDisk($file)
    {
        if (!is_array($file)) {
            $file = $this->get($file);
        }

        $result = null;
        $this->hook->attach('file.delete.disk.before', $file, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if (empty($file['path'])) {
            return false;
        }

        $path = gplcart_file_absolute($file['path']);

        if (!is_file($path)) {
            return false;
        }

        $result = unlink($path);
        $this->hook->attach('file.delete.disk.after', $file, $result, $this);
        return $result;
    }

    /**
     * Deletes a file both from database and disk
     * @param int|array $file
     * @param bool $check
     * @return array
     */
    public function deleteAll($file, $check = true)
    {
        if (!is_array($file)) {
            $file = $this->get($file);
        }

        if (empty($file['file_id'])) {
            return array('database' => 0, 'disk' => 0);
        }

        $deleted_database = $this->delete($file['file_id'], $check);

        if (empty($deleted_database)) {
            return array('database' => 0, 'disk' => 0);
        }

        $deleted_disk = $this->deleteFromDisk($file);

        if (empty($deleted_disk)) {
            return array('database' => 1, 'disk' => 0);
        }

        return array('database' => 1, 'disk' => 1);
    }

    /**
     * Whether the file can be deleted
     * @param integer $file_id
     * @return boolean
     */
    public function canDelete($file_id)
    {
        $sql = 'SELECT NOT EXISTS (SELECT file_id FROM field_value WHERE file_id=:id)'
                . ' AND NOT EXISTS (SELECT file_id FROM product_sku WHERE file_id=:id)';

        return (bool) $this->db->fetchColumn($sql, array('id' => (int) $file_id));
    }

    /**
     * Returns an array of all supported file extensions
     * @param boolean $dot
     * @return array
     */
    public function supportedExtensions($dot = false)
    {
        $extensions = array();
        foreach ($this->getHandlers() as $handler) {
            if (!empty($handler['extensions'])) {
                $extensions += array_merge($extensions, (array) $handler['extensions']);
            }
        }

        $extensions = array_unique($extensions);

        if ($dot) {
            $extensions = array_map(function ($value) {
                return ".$value";
            }, $extensions);
        }

        return $extensions;
    }

    /**
     * Returns an array of all defined file handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &gplcart_static('file.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = (array) gplcart_config_get(GC_FILE_CONFIG_FILE);
        $this->hook->attach('file.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Returns a handler data
     * @param string $name
     * @return array
     */
    public function getHandler($name)
    {
        $handlers = $this->getHandlers();

        if (strpos($name, '.') !== 0) {
            return isset($handlers[$name]) ? $handlers[$name] : array();
        }

        $extension = ltrim($name, '.');

        foreach ($handlers as $handler) {

            if (empty($handler['extensions'])) {
                continue;
            }

            foreach ((array) $handler['extensions'] as $allowed_extension) {
                if ($extension === $allowed_extension) {
                    return $handler;
                }
            }
        }

        return array();
    }

    /**
     * Returns a array of entities
     * @return array
     */
    public function getEntities()
    {
        return $this->db->fetchColumnAll('SELECT entity FROM file GROUP BY entity');
    }

}
