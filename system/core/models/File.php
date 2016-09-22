<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\classes\Url;
use core\classes\Curl;
use core\classes\Tool;
use core\classes\Cache;
use core\models\Language as ModelsLanguage;
use core\models\Validator as ModelsValidator;

/**
 * Manages basic behaviors and data related to files
 */
class File extends Model
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Validator model instance
     * @var \core\models\Validator $validator
     */
    protected $validator;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Upload directory
     * @var string
     */
    protected $path = '';

    /**
     * CURL class instance
     * @var \core\classes\Curl $curl
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
     * @param ModelsLanguage $language
     * @param ModelsValidator $validator
     * @param Url $url
     * @param Curl $curl
     */
    public function __construct(ModelsLanguage $language,
            ModelsValidator $validator, Url $url, Curl $curl)
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
        $this->hook->fire('add.file.before', $data);

        if (empty($data)) {
            return false;
        }

        if (empty($data['mime_type'])) {
            $data['mime_type'] = $this->getMimeType(GC_FILE_DIR . '/' . $data['path']);
        }

        if (empty($data['file_type'])) {
            $data['file_type'] = strtok($data['mime_type'], '/');
        }

        if (empty($data['title'])) {
            $data['title'] = basename($data['path']);
        }

        $data += array('created' => GC_TIME);
        $data['file_id'] = $this->db->insert('file', $data);

        $this->setTranslation($data, false);

        $this->hook->fire('add.file.after', $data);
        return $data['file_id'];
    }

    /**
     * Deletes and/or adds file translations
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslation(array $data, $delete = true)
    {
        if (empty($data['translation'])) {
            return false;
        }

        if ($delete) {
            $this->deleteTranslation($data['file_id']);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($data['file_id'], $language, $translation);
        }

        return true;
    }

    /**
     * Deletes file translation(s)
     * @param integer $file_id
     * @param null|string $language
     * @return boolean
     */
    public function deleteTranslation($file_id, $language = null)
    {
        $conditions = array('file_id' => (int) $file_id);

        if (isset($language)) {
            $conditions['language'] = $language;
        }

        return (bool) $this->db->delete('file_translation', $conditions);
    }

    /**
     * Adds a translation to the file
     * @param array $file_id
     * @param string $language
     * @param array $translation
     * @return integer
     */
    public function addTranslation($file_id, $language, array $translation)
    {
        $translation += array(
            'file_id' => $file_id,
            'language' => $language
        );

        return $this->db->insert('file_translation', $translation);
    }

    /**
     * Updates a file
     * @param integer $file_id
     * @param array $data
     */
    public function update($file_id, array $data)
    {
        $this->hook->fire('update.file.before', $file_id, $data);

        $conditions = array('file_id' => $file_id);
        $updated = (int) $this->db->update('file', $data, $conditions);

        $data['file_id'] = $file_id;

        $updated += (int) $this->setTranslation($data);

        $result = ($updated > 0);

        $this->hook->fire('update.file.after', $file_id, $data, $result);
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
        $this->hook->fire('get.file.before', $file_id);

        $file = $this->db->fetch('SELECT * FROM file WHERE file_id=?', array($file_id));
        $this->attachTranslation($file, $language);

        $this->hook->fire('get.file.after', $file);
        return $file;
    }

    /**
     * Adds translations to the file
     * @param array $file
     * @param null|string $language
     */
    protected function attachTranslation(array &$file, $language)
    {
        if (empty($file)) {
            return;
        }

        $file['language'] = 'und';
        $translations = $this->getTranslation($file['file_id']);

        foreach ($translations as $translation) {
            $file['translation'][$translation['language']] = $translation;
        }

        if (isset($language) && isset($file['translation'][$language])) {
            $file = $file['translation'][$language] + $file;
        }
    }

    /**
     * Returns an array of file translations
     * @param integer $file_id
     * @return array
     */
    public function getTranslation($file_id)
    {
        $sql = 'SELECT * FROM file_translation WHERE file_id=?';
        return $this->db->fetchAll($sql, array($file_id));
    }

    /**
     * Deletes a file from the database
     * @param integer $file_id
     * @return boolean
     */
    public function delete($file_id)
    {
        $this->hook->fire('delete.file.before', $file_id);

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

        $this->hook->fire('delete.file.after', $file_id, $deleted);
        return (bool) $deleted;
    }

    /**
     * Wheter a file can be deleted
     * @param integer $file_id
     * @return boolean
     */
    public function canDelete($file_id)
    {
        $sql = 'SELECT'
                . ' NOT EXISTS (SELECT file_id FROM field_value WHERE file_id=:id)'
                . ' AND NOT EXISTS (SELECT file_id FROM option_combination WHERE file_id=:id)';

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

        $moved = $this->move($tempname, $file);

        if ($moved !== true) {
            return $moved;
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
            return $this->language->text('File size exceeds %s bytes', array('%s' => $this->handler['filesize']));
        }

        $result = $this->validator->check($this->handler['validator'], $path, $this->handler);

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
            // Prepend the dot to each extension in the array
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

        if (0 !== strpos($name, '.')) {
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
     * @return \core\models\File
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
     * @return \core\models\File
     */
    public function setUploadPath($path)
    {
        $this->path = trim($path, '/');
        return $this;
    }

    /**
     * Returns path of uploaded file
     * @return string
     */
    public function getUploadedFile()
    {
        return $this->uploaded;
    }

    /**
     * Returns an array of files
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $files = &Cache::memory('files.' . md5(json_encode($data)));

        if (isset($files)) {
            return $files;
        }

        $sql = 'SELECT f.*,';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.file_id),';
        }
        
        $language = 'und';
        //$this->language->current();
        $where = array($language);

        $sql .= 'COALESCE(NULLIF(ft.title, ""), f.title) AS title'
                . ' FROM file f'
                . ' LEFT JOIN file_translation ft ON(ft.file_id = f.file_id AND ft.language=?)';

        if (!empty($data['file_id'])) {
            $ids = (array) $data['file_id'];
            $placeholders = rtrim(str_repeat('?,', count($ids)), ',');
            $sql .= ' WHERE f.file_id IN(' . $placeholders . ')';
            $where = array_merge($where, $ids);
        } else {
            $sql .= ' WHERE f.file_id > 0';
        }

        if (isset($data['title'])) {
            $sql .= ' AND (f.title LIKE ? OR (ft.title LIKE ? AND ft.language=?))';
            $where[] = "%{$data['title']}%";
            $where[] = "%{$data['title']}%";
            $where[] = $language;
        }

        if (isset($data['created'])) {
            $sql .= ' AND f.created = ?';
            $where[] = (int) $data['created'];
        }

        if (isset($data['id_key'])) {
            $sql .= ' AND f.id_key = ?';
            $where[] = $data['id_key'];
        }

        if (!empty($data['id_value'])) {
            $id_values = (array) $data['id_value'];
            $placeholders = rtrim(str_repeat('?, ', count($id_values)), ', ');
            $sql .= " AND f.id_value IN($placeholders)";
            $where = array_merge($where, $id_values);
        }

        if (isset($data['language'])) {
            $sql .= ' AND ft.language = ?';
            $where[] = $data['language'];
        }

        if (isset($data['path'])) {
            $sql .= ' AND f.path LIKE ?';
            $where[] = "%{$data['path']}%";
        }

        if (isset($data['mime_type'])) {
            $sql .= ' AND f.mime_type LIKE ?';
            $where[] = "%{$data['mime_type']}%";
        }

        if (isset($data['file_type'])) {
            $sql .= ' AND f.file_type = ?';
            $where[] = $data['file_type'];
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('title' => 'title', 'path' => 'f.path',
            'file_id' => 'f.file_id','created' => 'f.created',
            'weight' => 'f.weight', 'mime_type' => 'f.mime_type');

        if (isset($data['sort']) && isset($allowed_sort[$data['sort']])
                && isset($data['order']) && in_array($data['order'], $allowed_order)) {
            $sql .= " ORDER BY {$allowed_sort[$data['sort']]} {$data['order']}";
        } else {
            $sql .= " ORDER BY f.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $files = $this->db->fetchAll($sql, $where, array('index' => 'file_id'));
        $this->hook->fire('files', $files);
        return $files;
    }

    /**
     * Creates relative path from full server path
     * @param string $server_path
     * @return string
     */
    public function path($server_path)
    {
        return trim(str_replace(GC_FILE_DIR, '', $server_path), "/");
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

        if ($this->config->get('file_upload_translit', 1) && preg_match('/[^A-Za-z0-9_.-]/', $filename)) {
            $filename = $this->language->translit($filename, null);
        }

        if (empty($this->path) && !empty($this->handler['path'])) {
            $this->path = $this->handler['path'];
        }

        $destination = GC_UPLOAD_DIR;

        if (!empty($this->path)) {
            $destination = GC_FILE_DIR . '/' . trim($this->path, '/');
        }

        if (!file_exists($destination) && !mkdir($destination, 0644, true)) {
            return $this->language->text('Unable to create upload directory !name', array('!name' => $destination));
        }

        $rand = Tool::randomString(6);
        $destination = "$destination/$filename-$rand.$extension";

        $copied = copy($tempname, $destination);
        unlink($tempname);

        if (!$copied) {
            return $this->language->text('Unable to move file !name to !dest', array(
                        '!name' => $tempname, '!dest' => $destination));
        }

        if (strpos($destination, GC_PRIVATE_DIR) !== false) {
            chmod($destination, 0640);
        } else {
            chmod($destination, 0644);
        }

        $this->uploaded = $destination;
        return true;
    }

    /**
     * Returns file's MIME type
     * @param string $file
     * @return string
     */
    public function getMimeType($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);

        return $mimetype;
    }

}
