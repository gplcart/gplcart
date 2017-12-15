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
     * @param integer $file_id
     * @param string|null $language
     * @return array
     */
    public function get($file_id, $language = null)
    {
        $result = null;
        $this->hook->attach('file.get.before', $file_id, $language, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->db->fetch('SELECT * FROM file WHERE file_id=?', array($file_id));
        $this->attachTranslations($result, $this->translation_entity, 'file', $language);

        $this->hook->attach('file.get.after', $file_id, $language, $result, $this);
        return $result;
    }

    /**
     * Deletes a file from the database
     * @param integer $file_id
     * @param bool $check
     * @return boolean
     */
    public function delete($file_id, $check = true)
    {
        $result = null;
        $this->hook->attach('file.delete.before', $file_id, $check, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        if ($check && !$this->canDelete($file_id)) {
            return false;
        }

        $conditions = array('file_id' => $file_id);
        $result = (bool) $this->db->delete('file', $conditions);

        if ($result) {
            $this->db->delete('file_translation', $conditions);
        }

        $this->hook->attach('file.delete.after', $file_id, $check, $result, $this);
        return (bool) $result;
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

        return (bool) $this->db->fetchColumn($sql, array('id' => $file_id));
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
     * Returns an array of files or counts them
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $files = &gplcart_static(gplcart_array_hash(array('file.list' => $data)));

        if (isset($files)) {
            return $files;
        }

        $sql = 'SELECT f.*, COALESCE(NULLIF(ft.title, ""), f.title) AS title';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.file_id)';
        }

        $sql .= ' FROM file f'
                . ' LEFT JOIN file_translation ft ON(ft.file_id = f.file_id AND ft.language=?)';

        $language = $this->translation->getLangcode();
        $conditions = array($language);

        if (!empty($data['file_id'])) {
            settype($data['file_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['file_id'])), ',');
            $sql .= " WHERE f.file_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['file_id']);
        } else {
            $sql .= ' WHERE f.file_id IS NOT NULL';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (f.title LIKE ? OR (ft.title LIKE ? AND ft.language=?))';
            $conditions[] = "%{$data['title']}%";
            $conditions[] = "%{$data['title']}%";
            $conditions[] = $language;
        }

        if (isset($data['created'])) {
            $sql .= ' AND f.created = ?';
            $conditions[] = (int) $data['created'];
        }

        if (isset($data['entity'])) {
            $sql .= ' AND f.entity = ?';
            $conditions[] = $data['entity'];
        }

        if (!empty($data['entity_id'])) {
            settype($data['entity_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['entity_id'])), ',');
            $sql .= " AND f.entity_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['entity_id']);
        }

        if (isset($data['language'])) {
            $sql .= ' AND ft.language = ?';
            $conditions[] = $data['language'];
        }

        if (isset($data['path'])) {
            $sql .= ' AND f.path LIKE ?';
            $conditions[] = "%{$data['path']}%";
        }

        if (isset($data['mime_type'])) {
            $sql .= ' AND f.mime_type LIKE ?';
            $conditions[] = "%{$data['mime_type']}%";
        }

        if (isset($data['file_type'])) {
            $sql .= ' AND f.file_type = ?';
            $conditions[] = $data['file_type'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title', 'path', 'file_id', 'created',
            'weight', 'mime_type', 'entity', 'entity_id');

        if (isset($data['sort']) && in_array($data['sort'], $allowed_sort)//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY f.{$data['sort']} {$data['order']}";
        } else {
            $sql .= " ORDER BY f.modified DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $conditions);
        }

        $files = $this->db->fetchAll($sql, $conditions, array('index' => 'file_id'));
        $this->hook->attach('file.list', $files, $this);
        return $files;
    }

    /**
     * Returns a array of entities
     * @return array
     */
    public function getEntities()
    {
        return $this->db->fetchColumnAll('SELECT entity FROM file GROUP BY entity');
    }

    /**
     * Deletes a file from disk
     * @param array $file
     * @return boolean
     */
    public function deleteFromDisk(array $file)
    {
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
     * @param integer|array $file
     * @param bool $check
     * @return array
     */
    public function deleteAll($file, $check = true)
    {
        if (is_numeric($file)) {
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
     * Deletes multiple files
     * @param array $options
     * @return bool
     */
    public function deleteMultiple(array $options)
    {
        $deleted = $count = 0;
        foreach ((array) $this->getList($options) as $file) {
            $count ++;
            $deleted += (int) $this->delete($file['file_id']);
        }

        return $count && $deleted == $count;
    }

    /**
     * Creates a relative path from a server path
     * @param string $absolute
     * @return string
     */
    public function path($absolute)
    {
        return gplcart_file_relative($absolute);
    }

}
