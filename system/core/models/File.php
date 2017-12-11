<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Config,
    gplcart\core\Hook,
    gplcart\core\Database;
use gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\Validator as ValidatorModel,
    gplcart\core\models\Translation as TranslationModel;
use gplcart\core\traits\Translation as TranslationTrait;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\SocketClient as SocketClientHelper;

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
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * Translation model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Socket client class instance
     * @var \gplcart\core\helpers\SocketClient $socket
     */
    protected $socket;

    /**
     * Transfer file destination
     * @var string
     */
    protected $destination;

    /**
     * The current handler
     * @var mixed
     */
    protected $handler;

    /**
     * Path of a transferred file
     * @var string
     */
    private $transferred;

    /**
     * The last error
     * @var string
     */
    protected $error;

    /**
     * @param Hook $hook
     * @param Database $db
     * @param Config $config
     * @param LanguageModel $language
     * @param ValidatorModel $validator
     * @param TranslationModel $translation
     * @param UrlHelper $url
     * @param SocketClientHelper $socket
     */
    public function __construct(Hook $hook, Database $db, Config $config, LanguageModel $language,
            ValidatorModel $validator, TranslationModel $translation, UrlHelper $url,
            SocketClientHelper $socket)
    {
        $this->db = $db;
        $this->hook = $hook;
        $this->config = $config;

        $this->url = $url;
        $this->socket = $socket;
        $this->language = $language;
        $this->validator = $validator;
        $this->translation = $translation;
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

        $data['created'] = GC_TIME;
        $result = $data['file_id'] = $this->db->insert('file', $data);

        $this->setTranslations($data, $this->translation, 'file', false);

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

        $updated = $this->db->update('file', $data, array('file_id' => $file_id));
        $data['file_id'] = $file_id;
        $updated += (int) $this->setTranslations($data, $this->translation, 'file');

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
        $this->attachTranslations($result, $this->translation, 'file', $language);

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
     * Sets the current transfer handler
     * @param mixed $id
     *  - string: load by validator ID
     *  - false: disable validator at all,
     *  - null: detect validator by file extension
     * @return \gplcart\core\models\File
     */
    public function setHandler($id)
    {
        if (is_string($id)) {
            $this->handler = $this->getHandler($id);
        } else {
            $this->handler = $id;
        }

        return $this;
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

        $sql = 'SELECT f.*,';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.file_id),';
        }

        $language = 'und';
        $params = array($language);

        $sql .= 'COALESCE(NULLIF(ft.title, ""), f.title) AS title'
                . ' FROM file f'
                . ' LEFT JOIN file_translation ft ON(ft.file_id = f.file_id AND ft.language=?)';

        if (!empty($data['file_id'])) {
            settype($data['file_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['file_id'])), ',');
            $sql .= " WHERE f.file_id IN($placeholders)";
            $params = array_merge($params, $data['file_id']);
        } else {
            $sql .= ' WHERE f.file_id IS NOT NULL';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (f.title LIKE ? OR (ft.title LIKE ? AND ft.language=?))';
            $params[] = "%{$data['title']}%";
            $params[] = "%{$data['title']}%";
            $params[] = $language;
        }

        if (isset($data['created'])) {
            $sql .= ' AND f.created = ?';
            $params[] = (int) $data['created'];
        }

        if (isset($data['entity'])) {
            $sql .= ' AND f.entity = ?';
            $params[] = $data['entity'];
        }

        if (!empty($data['entity_id'])) {
            settype($data['entity_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['entity_id'])), ',');
            $sql .= " AND f.entity_id IN($placeholders)";
            $params = array_merge($params, $data['entity_id']);
        }

        if (isset($data['language'])) {
            $sql .= ' AND ft.language = ?';
            $params[] = $data['language'];
        }

        if (isset($data['path'])) {
            $sql .= ' AND f.path LIKE ?';
            $params[] = "%{$data['path']}%";
        }

        if (isset($data['mime_type'])) {
            $sql .= ' AND f.mime_type LIKE ?';
            $params[] = "%{$data['mime_type']}%";
        }

        if (isset($data['file_type'])) {
            $sql .= ' AND f.file_type = ?';
            $params[] = $data['file_type'];
        }

        // This is to prevent errors when sql_mode=only_full_group_by
        $sql .= ' GROUP BY f.file_id, ft.title';

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array(
            'title' => 'title', 'path' => 'f.path',
            'file_id' => 'f.file_id', 'created' => 'f.created',
            'weight' => 'f.weight', 'mime_type' => 'f.mime_type',
            'entity' => 'f.entity', 'entity_id' => 'f.entity_id'
        );

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])//
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY f.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $params);
        }

        $files = $this->db->fetchAll($sql, $params, array('index' => 'file_id'));

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
     * Creates a relative path from a server path
     * @param string $absolute
     * @return string
     */
    public function path($absolute)
    {
        return gplcart_file_relative($absolute);
    }

    /**
     * Creates a file URL from a path
     * @param string $path
     * @param bool $absolute
     * @return string
     */
    public function url($path, $absolute = false)
    {
        return $this->url->get('files/' . trim($path, "/"), array(), $absolute, true);
    }

    /**
     * Deletes a file from disk
     * @param array $file
     * @return boolean
     */
    public function deleteFromDisk(array $file)
    {
        if (empty($file['path'])) {
            return false;
        }

        $path = gplcart_file_absolute($file['path']);
        return file_exists($path) ? unlink($path) : false;
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
     * Uploads a file
     * @param array $post
     * @param null|string|false $handler
     * @param string|null $path
     * @return mixed
     */
    public function upload($post, $handler, $path = null)
    {
        $this->error = null;
        $this->transferred = null;

        $result = null;
        $this->hook->attach('file.upload.before', $post, $handler, $path, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!empty($post['error']) || empty($post['tmp_name']) || !is_uploaded_file($post['tmp_name'])) {
            return $this->error = $this->language->text('Unable to upload the file');
        }

        $this->setHandler($handler);
        $this->setDestination($path);

        if ($this->validate($post['tmp_name'], $post['name']) !== true) {
            unlink($post['tmp_name']);
            return $this->error;
        }

        if (!$this->finalizeTransfer($post['tmp_name'], $post['name'], true)) {
            return $this->error;
        }

        $result = true;
        $this->hook->attach('file.upload.after', $post, $handler, $path, $result, $this);
        return $result;
    }

    /**
     * Multiple file upload
     * @param array $files
     * @param null|string|false $handler
     * @param string|null $path
     * @return array
     */
    public function uploadMultiple($files, $handler, $path = null)
    {
        $return = array('transferred' => array(), 'errors' => array());

        if (!gplcart_file_multi_upload($files)) {
            return $return;
        }

        foreach ($files as $key => $file) {
            $result = $this->upload($file, $handler, $path);
            if ($result === true) {
                $return['transferred'][$key] = $this->getTransferred(true);
            } else {
                $return['errors'][$key] = (string) $result;
            }
        }

        return $return;
    }

    /**
     * Downloads a file from a remote URL
     * @param string $url
     * @param null|false|string $handler
     * @param string|null $path
     * @return mixed
     */
    public function download($url, $handler, $path = null)
    {
        $this->error = null;
        $this->transferred = null;

        $result = null;
        $this->hook->attach('file.download.before', $url, $handler, $path, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $temp = $this->writeTempFile($url);

        if (empty($temp)) {
            return $this->error;
        }

        $this->setHandler($handler);
        $this->setDestination($path);

        if (!$this->validateHandler($temp)) {
            unlink($temp);
            return $this->error;
        }

        if (!$this->finalizeTransfer($temp, $this->destination, false)) {
            return $this->error;
        }

        $result = true;
        $this->hook->attach('file.download.after', $url, $handler, $temp, $result, $this);
        return $result;
    }

    /**
     * Writes a temporary file from a remote file
     * @param string $url
     * @return string|false
     */
    protected function writeTempFile($url)
    {
        try {
            $response = $this->socket->request($url);
        } catch (\Exception $ex) {
            $this->error = $ex->getMessage();
            return false;
        }

        $file = gplcart_file_tempname();
        $fh = fopen($file, "w");
        fwrite($fh, $response['data']);
        fclose($fh);
        return $file;
    }

    /**
     * Finalize file transfer
     * @param string $temp
     * @param string $to
     * @param bool $upload
     * @return boolean
     */
    protected function finalizeTransfer($temp, $to, $upload)
    {
        if (!isset($this->destination)) {
            $this->transferred = $temp;
            return true;
        }

        $directory = gplcart_file_absolute($this->path($this->destination));
        $pathinfo = $upload ? pathinfo($to) : pathinfo($directory);

        if ($upload) {
            $filename = $this->prepareFileName($pathinfo['filename'], $pathinfo['extension']);
        } else {
            $filename = $pathinfo['basename'];
            $directory = $pathinfo['dirname'];
        }

        if (!file_exists($directory) && !mkdir($directory, 0775, true)) {
            unlink($temp);
            $this->error = $this->language->text('Unable to create @name', array('@name' => $directory));
            return false;
        }

        $destination = "$directory/$filename";

        if ($upload) {
            $destination = gplcart_file_unique($destination);
        }

        if (!$this->moveTemp($temp, $destination)) {
            return $this->error;
        }

        chmod($destination, 0644);
        $this->transferred = $destination;
        return true;
    }

    /**
     * Move a temporary file to its final destination
     * @param string $from
     * @param string $to
     * @return boolean
     */
    protected function moveTemp($from, $to)
    {
        $copied = copy($from, $to);
        unlink($from);

        if ($copied) {
            return true;
        }

        $vars = array('@source' => $from, '@destination' => $to);
        $this->error = $this->language->text('Unable to move @source to @destination', $vars);
        return false;
    }

    /**
     * Sanitize and transliterate a filename
     * @param string $filename
     * @param string $extension
     * @return string
     */
    protected function prepareFileName($filename, $extension)
    {
        if ($this->config->get('file_upload_translit', 1)) {
            $filename = $this->language->translit($filename, null);
        }

        $suffix = gplcart_string_random(6);
        $clean = gplcart_file_sanitize($filename);

        return "$clean-$suffix.$extension";
    }

    /**
     * Validate a file
     * @param string $path
     * @param null|string $filename
     * @return boolean|string
     */
    public function validate($path, $filename = null)
    {
        $pathinfo = isset($filename) ? pathinfo($filename) : pathinfo($path);

        if (empty($pathinfo['filename'])) {
            return $this->error = $this->language->text('Unknown filename');
        }

        if (empty($pathinfo['extension'])) {
            return $this->error = $this->language->text('Unknown file extension');
        }

        if ($this->handler === false) {
            return true;
        }

        if (!isset($this->handler) && !$this->setHandlerByExtension($pathinfo['extension'])) {
            return $this->error;
        }

        if (!$this->validateHandler($path, $pathinfo['extension'])) {
            return $this->error;
        }

        return true;
    }

    /**
     * Find and set handler by a file extension
     * @param string $extension
     * @return boolean
     */
    protected function setHandlerByExtension($extension)
    {
        if (in_array($extension, $this->supportedExtensions())) {
            $this->handler = $this->getHandler(".$extension");
            return true;
        }

        $this->error = $this->language->text('Unsupported file extension');
        return false;
    }

    /**
     * Validates a file using a validator
     * @param string $file
     * @param string|null $extension
     * @return boolean
     */
    protected function validateHandler($file, $extension = null)
    {
        if (empty($this->handler['validator'])) {
            $this->error = $this->language->text('Unknown handler');
            return false;
        }

        if (!empty($this->handler['extensions']) && isset($extension) && !in_array($extension, $this->handler['extensions'])) {
            $this->error = $this->language->text('Unsupported file extension');
            return false;
        }

        if (isset($this->handler['filesize']) && filesize($file) > $this->handler['filesize']) {
            $this->error = $this->language->text('File size exceeds %num bytes', array('%num' => $this->handler['filesize']));
            return false;
        }

        $result = $this->validator->run($this->handler['validator'], $file, $this->handler);

        if ($result === true) {
            return true;
        }

        $this->error = $result;
        return false;
    }

    /**
     * Sets path to the file final destination
     * @param string $path
     * @return \gplcart\core\models\File
     */
    public function setDestination($path)
    {
        $this->destination = $path;
        return $this;
    }

    /**
     * Returns a path to the transferred file
     * @param bool $relative
     * @return string
     */
    public function getTransferred($relative = false)
    {
        return $relative ? $this->path($this->transferred) : $this->transferred;
    }

    /**
     * Returns the last error
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

}
