<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Curl as CurlHelper;
use gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\Validator as ValidatorModel;

/**
 * Manages basic behaviors and data related to files
 */
class File extends Model
{

    use \gplcart\core\traits\EntityTranslation;

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
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * CURL class instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

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
     * @param LanguageModel $language
     * @param ValidatorModel $validator
     * @param UrlHelper $url
     * @param CurlHelper $curl
     */
    public function __construct(LanguageModel $language,
            ValidatorModel $validator, UrlHelper $url, CurlHelper $curl)
    {
        parent::__construct();

        $this->url = $url;
        $this->curl = $curl;
        $this->language = $language;
        $this->validator = $validator;
    }

    /**
     * Adds a file to the database
     * @param array $data
     * @return boolean|integer
     */
    public function add(array $data)
    {
        $this->hook->fire('file.add.before', $data);

        if (empty($data)) {
            return false;
        }

        if (empty($data['mime_type'])) {
            $data['mime_type'] = gplcart_file_mime(GC_FILE_DIR . "/{$data['path']}");
        }

        if (empty($data['file_type'])) {
            $data['file_type'] = strtok($data['mime_type'], '/');
        }

        if (empty($data['title'])) {
            $data['title'] = basename($data['path']);
        }

        $data += array('created' => GC_TIME);
        $data['file_id'] = $this->db->insert('file', $data);

        $this->setTranslationTrait($this->db, $data, 'file', false);

        $this->hook->fire('file.add.after', $data);
        return $data['file_id'];
    }

    /**
     * Updates a file
     * @param integer $file_id
     * @param array $data
     */
    public function update($file_id, array $data)
    {
        $this->hook->fire('file.update.before', $file_id, $data);

        $conditions = array('file_id' => $file_id);
        $updated = $this->db->update('file', $data, $conditions);

        $data['file_id'] = $file_id;
        $updated += (int) $this->setTranslationTrait($this->db, $data, 'file');

        $result = ($updated > 0);

        $this->hook->fire('file.update.after', $file_id, $data, $result);
        return $result;
    }

    /**
     * Returns a file from the database
     * @param integer $file_id
     * @param string|null $language
     * @return array
     */
    public function get($file_id, $language = null)
    {
        $this->hook->fire('file.get.before', $file_id);

        $file = $this->db->fetch('SELECT * FROM file WHERE file_id=?', array($file_id));

        $this->attachTranslationTrait($this->db, $file, 'file', $language);

        $this->hook->fire('file.get.after', $file);
        return $file;
    }

    /**
     * Deletes a file from the database
     * @param integer $file_id
     * @return boolean
     */
    public function delete($file_id)
    {
        $this->hook->fire('file.delete.before', $file_id);

        if (empty($file_id)) {
            return false;
        }

        if (!$this->canDelete($file_id)) {
            return false;
        }

        $conditions = array('file_id' => $file_id);
        $deleted = (bool) $this->db->delete('file', $conditions);

        if ($deleted) {
            $this->db->delete('file_translation', $conditions);
        }

        $this->hook->fire('file.delete.after', $file_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Deletes multiple files
     * @param array $options
     */
    public function deleteMultiple($options)
    {
        $deleted = 0;
        foreach ((array) $this->getList($options) as $file) {
            $deleted += (int) $this->delete($file['file_id']);
        }
        return $deleted > 0;
    }

    /**
     * Wheter a file can be deleted
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
        $handlers = &Cache::memory(__METHOD__);

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['image'] = array(
            'extensions' => array('jpg', 'jpeg', 'gif', 'png'),
            'validator' => 'image'
        );

        $handlers['json'] = array(
            'extensions' => array('json'),
            'validator' => 'json'
        );

        $handlers['csv'] = array(
            'extensions' => array('csv'),
            'validator' => 'csv'
        );

        $handlers['zip'] = array(
            'extensions' => array('zip'),
            'validator' => 'zip'
        );

        $this->hook->fire('file.handlers', $handlers);

        return $handlers;
    }

    /**
     * Returns a handler by a given name
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
     * Sets the current tranfer handler
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
     * Returns an array of files
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $files = &Cache::memory(array(__METHOD__ => $data));

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
            $ids = (array) $data['file_id'];
            $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
            $sql .= ' WHERE f.file_id IN(' . $placeholders . ')';
            $params = array_merge($params, $ids);
        } else {
            $sql .= ' WHERE f.file_id > 0';
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

        if (isset($data['id_key'])) {
            $sql .= ' AND f.id_key = ?';
            $params[] = $data['id_key'];
        }

        if (!empty($data['id_value'])) {
            $id_values = (array) $data['id_value'];
            $placeholders = rtrim(str_repeat('?,', count($id_values)), ',');
            $sql .= " AND f.id_value IN($placeholders)";
            $params = array_merge($params, $id_values);
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

        $allowed_order = array('asc', 'desc');

        $allowed_sort = array('title' => 'title', 'path' => 'f.path',
            'file_id' => 'f.file_id', 'created' => 'f.created',
            'weight' => 'f.weight', 'mime_type' => 'f.mime_type');

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
        $this->hook->fire('file.list', $files);
        return $files;
    }

    /**
     * Creates relative path from full server path
     * @param string $absolute
     * @return string
     */
    public function path($absolute)
    {
        return gplcart_file_relative_path($absolute);
    }

    /**
     * Creates file URL from path
     * @param string $path
     * @param bool $absolute
     * @return string
     */
    public function url($path, $absolute = false)
    {
        return $this->url->get('files/' . trim($path, "/"), array(), $absolute, true);
    }

    /**
     * Deletes a file from the disk
     * @param array $file
     * @return boolean
     */
    public function deleteFromDisk(array $file)
    {
        if (empty($file['path'])) {
            return false;
        }

        return unlink(GC_FILE_DIR . '/' . $file['path']);
    }

    /**
     * Deletes a file both from database and disk
     * @param integer|array $file
     * @return array
     */
    public function deleteAll($file)
    {
        if (is_numeric($file)) {
            $file = $this->get($file);
        }

        if (empty($file['file_id'])) {
            return array('database' => 0, 'disk' => 0);
        }

        $deleted_database = $this->delete($file['file_id']);

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
     * @param string $path
     * @return mixed
     */
    public function upload($post, $handler, $path)
    {
        $this->hook->fire('file.upload.before', $post, $handler, $path, $this);

        $this->error = null;
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

        $this->hook->fire('file.upload.after', $post, $this);
        return true;
    }

    /**
     * Downloads a file from a remote URL
     * @param string $url
     * @param null|false|string $handler
     * @param string $path
     * @return mixed
     */
    public function download($url, $handler, $path)
    {
        $this->hook->fire('file.download.before', $url, $handler, $path, $this);

        $this->error = '';
        if (empty($url)) {
            return $this->language->text('Nothing to download');
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

        $this->hook->fire('file.download.after', $url, $temp, $this);
        return true;
    }

    /**
     * Writes a temporary file from a remote file
     * @param string $url
     * @return string|false
     */
    protected function writeTempFile($url)
    {
        $content = $this->curl->get($url);
        $error = $this->curl->getError();

        if (!empty($error)) {
            $this->error = $error;
            return false;
        }

        $file = tempnam(sys_get_temp_dir(), 'DWN');
        $fh = fopen($file, "w");
        fwrite($fh, $content);
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
        $directory = GC_FILE_DIR . '/' . $this->path($this->destination);
        $pathinfo = $upload ? pathinfo($to) : pathinfo($directory);

        if ($upload) {
            $filename = $this->getSecureFileName($pathinfo['filename'], $pathinfo['extension']);
        } else {
            $filename = $pathinfo['basename'];
            $directory = $pathinfo['dirname'];
        }

        if (!file_exists($directory) && !mkdir($directory, 0644, true)) {
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

        $this->chmod($destination);
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
     * Clean up file name
     * @param string $filename
     * @return string
     */
    protected function cleanFileName($filename)
    {
        $clean = preg_replace('/[^A-Za-z0-9.]/', '', $filename);
        if ($this->config->get('file_upload_translit', 1) && preg_match('/[^A-Za-z0-9_.-]/', $clean) === 1) {
            $clean = $this->language->translit($clean, null);
        }
        return $clean;
    }

    /**
     * Build a secure filename
     * @param string $filename
     * @param string $extension
     * @return string
     */
    protected function getSecureFileName($filename, $extension)
    {
        $suffix = gplcart_string_random(6);
        $clean = $this->cleanFileName($filename);
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

        if (!$this->validateHandler($path)) {
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
     * @return boolean
     */
    protected function validateHandler($file)
    {
        if (empty($this->handler['validator'])) {
            $this->error = $this->language->text('Missing validator');
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
        if ($relative) {
            return $this->path($this->transferred);
        }
        return $this->transferred;
    }

    /**
     * Returns the last error
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set directory permissions depending on public/private access
     * @param string $file
     * @return boolean
     */
    protected function chmod($file)
    {
        if (strpos($file, GC_PRIVATE_DIR) === 0) {
            return chmod($file, 0640);
        }
        return chmod($file, 0644);
    }

}
