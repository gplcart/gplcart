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
     * Upload directory
     * @var string
     */
    protected $path = '';

    /**
     * CURL class instance
     * @var \gplcart\core\helpers\Curl $curl
     */
    protected $curl;

    /**
     * Current handler
     * @var array
     */
    protected $handler;

    /**
     * Path of a uploaded file
     * @var string
     */
    private $uploaded;

    /**
     * Constructor
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
     * Uploads a file
     * @param array $postfile
     * @return boolean|string
     */
    public function upload($postfile)
    {
        $this->hook->fire('file.upload.before', $postfile);

        if (empty($postfile)) {
            return $this->language->text('Nothing to upload');
        }

        if (!empty($postfile['error']) || empty($postfile['tmp_name']) || empty($postfile['name'])) {
            return $this->language->text('Unable to upload the file');
        }

        $tempname = $postfile['tmp_name'];
        $file = $postfile['name'];

        if (!is_uploaded_file($tempname)) {
            return $this->language->text('Unable to upload the file');
        }

        $valid = $this->validate($tempname, $file);

        if ($valid !== true) {
            return $valid;
        }

        $result = $this->move($tempname, $file);

        if ($result !== true) {
            return $result;
        }

        $this->hook->fire('file.upload.after', $postfile, $this->uploaded);
        return true;
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
            return $this->language->text('Unknown filename');
        }

        if (empty($pathinfo['extension'])) {
            return $this->language->text('Unknown file extension');
        }

        $extension = $pathinfo['extension'];

        if (isset($this->handler['extensions']) && !in_array($extension, $this->handler['extensions'])) {
            return $this->language->text('Unsupported file extension');
        }

        if (!isset($this->handler)) {
            $supported_extensions = $this->supportedExtensions();

            if (!in_array($extension, $supported_extensions)) {
                return $this->language->text('Unsupported file extension');
            }

            $this->handler = $this->getHandler(".$extension");
        }

        if (empty($this->handler)) {
            return $this->language->text('Missing handler');
        }

        if (empty($this->handler['validator'])) {
            return $this->language->text('Missing validator');
        }

        if (isset($this->handler['filesize']) && filesize($path) > $this->handler['filesize']) {
            return $this->language->text('File size exceeds %num bytes', array('%num' => $this->handler['filesize']));
        }

        $result = $this->validator->run($this->handler['validator'], $path, $this->handler);

        if ($result === true) {
            return true;
        }

        return $result; // Error
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

        // Remove repeating extensions
        $extensions = array_unique($extensions);

        if ($dot) {
            // Prepend a dot to the each extension in the array
            $extensions = array_map(function ($value) {
                return ".$value";
            }, $extensions);
        }

        return $extensions;
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
     * Sets the current handler
     * @param string $id
     * @return \gplcart\core\models\File
     */
    public function setHandler($id)
    {
        $this->handler = $this->getHandler($id);
        return $this;
    }

    /**
     * Downloads a file from the remoted URL
     * @param string $url
     * @return boolean|string
     */
    public function wget($url)
    {
        $this->hook->fire('file.download.before', $url);

        if (empty($url)) {
            return false;
        }

        $header = $this->curl->header($url);

        if (!isset($header['download_content_length'])) {
            return $this->language->text('Unknown filesize');
        }

        $remote = $this->curl->get($url);
        $tempname = tempnam(sys_get_temp_dir(), 'DWN');
        $handle = fopen($tempname, "w");

        fwrite($handle, $remote);
        fclose($handle);

        $validation_result = $this->validate($tempname, $url);

        if ($validation_result !== true) {
            unlink($tempname);
            return $validation_result;
        }

        $move_result = $this->move($tempname, $url);

        if ($move_result !== true) {
            return $move_result;
        }

        $this->hook->fire('file.download.after', $url, $tempname);
        return true;
    }

    /**
     * Sets a upload destination
     * @param string $path
     * @return \gplcart\core\models\File
     */
    public function setUploadPath($path)
    {
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * Returns path of uploaded file
     * @param bool $relative
     * @return string
     */
    public function getUploadedFile($relative = false)
    {
        if ($relative) {
            return $this->path($this->uploaded);
        }

        return $this->uploaded;
    }

    /**
     * Returns an array of files
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $files = &Cache::memory(array('files' => $data));

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
        if (substr($absolute, 0, strlen(GC_FILE_DIR)) == GC_FILE_DIR) {
            return trim(substr($absolute, strlen(GC_FILE_DIR)), '/');
        }
        return $absolute;
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
     * Returns an array of all defined file handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &Cache::memory('file.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['image'] = array(
            'extensions' => array('jpg', 'jpeg', 'gif', 'png'),
            'path' => 'image/upload/common',
            'filesize' => null,
            'validator' => 'image'
        );

        $handlers['p12'] = array(
            'extensions' => array('p12'),
            'path' => 'private/certificates',
            'validator' => 'p12'
        );

        $handlers['json'] = array(
            'extensions' => array('json'),
            'path' => 'upload',
            'validator' => 'json'
        );

        $handlers['csv'] = array(
            'extensions' => array('csv'),
            'path' => 'image/upload/common',
            'validator' => 'csv'
        );

        $handlers['zip'] = array(
            'extensions' => array('zip'),
            'path' => 'upload',
            'validator' => 'zip'
        );

        $this->hook->fire('file.handlers', $handlers);

        return $handlers;
    }

    /**
     * Moview a file from temporary to final destination
     * @param string $tempname
     * @param string $source
     * @return boolean|string
     */
    protected function move($tempname, $source)
    {
        $pathinfo = pathinfo(strtok($source, '?'));
        $filename = preg_replace('/[^A-Za-z0-9.]/', '', $pathinfo['filename']);
        $extension = $pathinfo['extension'];

        if ($this->config->get('file_upload_translit', 1) && preg_match('/[^A-Za-z0-9_.-]/', $filename) === 1) {
            $filename = $this->language->translit($filename, null);
        }

        if (empty($this->path) && !empty($this->handler['path'])) {
            $this->path = $this->handler['path'];
        }

        $destination = GC_UPLOAD_DIR;

        if (!empty($this->path)) {
            if (strpos($this->path, GC_ROOT_DIR) === 0) {
                $destination = $this->path;
            } else {
                $destination = GC_FILE_DIR . '/' . trim($this->path, '/');
            }
        }

        if (!file_exists($destination) && !mkdir($destination, 0644, true)) {
            return $this->language->text('Unable to create upload directory @name', array('@name' => $destination));
        }

        $rand = gplcart_string_random(6);
        $destination = "$destination/$filename-$rand.$extension";

        $copied = copy($tempname, $destination);
        unlink($tempname);

        if (!$copied) {
            $vars = array('@source' => $tempname, '@destination' => $destination);
            return $this->language->text('Unable to move @source to @destination', $vars);
        }

        if (strpos($destination, GC_PRIVATE_DIR) !== false) {
            chmod($destination, 0640);
        } else {
            chmod($destination, 0644);
        }

        $this->uploaded = $destination;
        return true;
    }

}
