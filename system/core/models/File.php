<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use core\Hook;
use core\Config;
use core\Handler;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Url as ClassesUrl;
use core\classes\Curl as ClassesCurl;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to files
 */
class File
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Database class instance
     * @var \core\classes\Database $db
     */
    protected $db;

    /**
     * An upload directory
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
     * @param ClassesUrl $url
     * @param ClassesCurl $curl
     * @param Hook $hook
     * @param Config $config
     */
    public function __construct(ModelsLanguage $language, ClassesUrl $url,
            ClassesCurl $curl, Hook $hook, Config $config)
    {
        $this->url = $url;
        $this->curl = $curl;
        $this->hook = $hook;
        $this->config = $config;
        $this->language = $language;
        $this->db = $this->config->getDb();
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
            $data['mime_type'] = $this->getMimetype(GC_FILE_DIR . '/' . $data['path']);
        }

        if (empty($data['file_type'])) {
            $data['file_type'] = strtok($data['mime_type'], '/');
        }

        $file_id = $this->db->insert('file', array(
            'created' => GC_TIME,
            'path' => $data['path'],
            'id_key' => !empty($data['id_key']) ? $data['id_key'] : '',
            'file_type' => $data['file_type'],
            'mime_type' => $data['mime_type'],
            'id_value' => !empty($data['id_value']) ? (int) $data['id_value'] : 0,
            'title' => !empty($data['title']) ? $data['title'] : '',
            'weight' => isset($data['weight']) ? (int) $data['weight'] : 0,
            'description' => !empty($data['description']) ? $data['description'] : ''
        ));

        if (!empty($data['translation'])) {
            $this->setTranslations($file_id, $data, false);
        }

        $this->hook->fire('add.file.after', $data, $file_id);
        return $file_id;
    }

    /**
     * Returns file's MIME type
     * @param string $file
     * @return string
     */
    public function getMimetype($file)
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);
        return $mimetype;
    }

    /**
     * Adds a translation
     * @param integer $file_id
     * @param string $language
     * @param array $translation
     * @return boolean
     */
    public function addTranslation($file_id, $language, array $translation)
    {
        $values = array(
            'file_id' => (int) $file_id,
            'language' => $language,
            'title' => !empty($translation['title']) ? $translation['title'] : '',
            'description' => !empty($translation['description']) ? $translation['description'] : ''
        );

        return (bool) $this->db->insert('file_translation', $values);
    }

    /**
     * Updates a file
     * @param integer $file_id
     * @param array $data
     */
    public function update($file_id, array $data)
    {
        $this->hook->fire('update.file.before', $file_id, $data);

        $values = array();

        if (isset($data['weight'])) {
            $values['weight'] = (int) $data['weight'];
        }

        if (!empty($data['path'])) {
            $values['path'] = $data['path'];
        }

        if (isset($data['title'])) {
            $values['title'] = $data['title'];
        }

        if (isset($data['mime_type'])) {
            $values['mime_type'] = $data['mime_type'];
        }

        if (isset($data['description'])) {
            $values['description'] = $data['description'];
        }

        if (!empty($data['translation'])) {
            $this->setTranslations($file_id, $data);
        }

        if (!empty($values)) {
            $this->db->update('file', $values, array('file_id' => $file_id));
        }

        $this->hook->fire('update.file.after', $file_id, $data);
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

        $sth = $this->db->prepare('SELECT * FROM file WHERE file_id=:file_id');
        $sth->execute(array(':file_id' => (int) $file_id));

        $file = $sth->fetch(PDO::FETCH_ASSOC);

        if (!empty($file)) {
            $file['language'] = 'und';
            $file['translation'] = $this->getTranslations($file_id);

            if (isset($language) && isset($file['translation'][$language])) {
                $file = $file['translation'][$language] + $file;
            }
        }

        $this->hook->fire('get.file.after', $file_id, $file);
        return $file;
    }

    /**
     * Returns file translations
     * @param integer $file_id
     * @return array
     */
    public function getTranslations($file_id)
    {
        $sth = $this->db->prepare('SELECT * FROM file_translation WHERE file_id=:file_id');
        $sth->execute(array(':file_id' => (int) $file_id));

        $translations = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $translation) {
            $translations[$translation['language']] = $translation;
        }

        return $translations;
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

        $this->db->delete('file', array('file_id' => $file_id));
        $this->db->delete('file_translation', array('file_id' => $file_id));

        $this->hook->fire('delete.file.after', $file_id);
        return true;
    }

    /**
     * Wheter a file can be deleted
     * @param integer $file_id
     * @return boolean
     */
    public function canDelete($file_id)
    {
        $sql = '
            SELECT
            NOT EXISTS (SELECT file_id FROM field_value WHERE file_id=:file_id) AND
            NOT EXISTS (SELECT file_id FROM option_combination WHERE file_id=:file_id)';

        $sth = $this->db->prepare($sql);
        $sth->execute(array(':file_id' => $file_id));

        return (bool) $sth->fetchColumn();
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
            return false;
        }

        if (!empty($postfile['error']) || empty($postfile['tmp_name']) || empty($postfile['name'])) {
            return $this->language->text('Unable to upload the file');
        }

        $tempname = $postfile['tmp_name'];
        $file = $postfile['name'];

        if (!is_uploaded_file($tempname)) {
            return $this->language->text('Unable to upload the file');
        }

        $validation_result = $this->validate($tempname, $file);

        if ($validation_result !== true) {
            return $validation_result;
        }

        $move_result = $this->move($tempname, $file);

        if ($move_result !== true) {
            return $move_result;
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

        if (isset($this->handler['filesize']) && filesize($path) > $this->handler['filesize']) {
            return $this->language->text('File size exceeds %s bytes', array('%s' => $this->handler['filesize']));
        }

        $validation_result = Handler::call($this->handler, null, 'validator', array($path, $this->handler));

        if ($validation_result !== true) {
            return $validation_result ? $validation_result : $this->language->text('Failed to validate the file');
        }

        return true;
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
    public function download($url)
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
     * Recursive deletes files and directories
     * @param string $directory
     * @return boolean
     */
    public function deleteDirecoryRecursive($directory)
    {
        if (!file_exists($directory)) {
            return false;
        }

        if (!is_dir($directory)) {
            return false;
        }

        foreach (scandir($directory) as $object) {
            if ($object == '.' || $object == '..') {
                continue;
            }

            $path = $directory . '/' . $object;
            if (is_dir($path)) {
                $this->deleteDirecoryRecursive($path);
                continue;
            }

            unlink($path);
        }

        return rmdir($directory);
    }

    /**
     * Returns an array of files
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $files = &Cache::memory('files.' . md5(serialize($data)));

        if (isset($files)) {
            return $files;
        }

        $files = array();

        $sql = 'SELECT f.*,';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(f.file_id),';
        }

        $sql .= '
            COALESCE(NULLIF(ft.title, ""), f.title) AS title
            FROM file f
            LEFT JOIN file_translation ft ON(ft.file_id = f.file_id AND ft.language=?)
            WHERE f.file_id > 0';

        $language = 'und';
        //$this->language->current();

        $where = array($language);

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

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'title':
                    $field = 'title';
                    break;
                case 'path':
                    $field = 'f.path';
                    break;
                case 'created':
                    $field = 'f.created';
                    break;
                case 'weight':
                    $field = 'f.weight';
                    break;
                case 'mime_type':
                    $field = 'f.mime_type';
            }

            if (isset($field)) {
                $sql .= " ORDER BY $field {$data['order']}";
            }
        } else {
            $sql .= " ORDER BY f.created DESC";
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $file) {
            $files[$file['file_id']] = $file;
        }

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
     * Deletes and/or adds file translations
     * @param integer $file_id
     * @param array $data
     * @param boolean $delete
     * @return boolean
     */
    protected function setTranslations($file_id, array $data, $delete = true)
    {
        if ($delete) {
            $this->deleteTranslation($file_id);
        }

        foreach ($data['translation'] as $language => $translation) {
            $this->addTranslation($file_id, $language, $translation);
        }

        return true;
    }

    /**
     * Deletes file translation(s)
     * @param integer $file_id
     * @param null|string $language
     * @return boolean
     */
    protected function deleteTranslation($file_id, $language = null)
    {
        $where = array('file_id' => (int) $file_id);

        if (isset($language)) {
            $where['language'] = $language;
        }

        return (bool) $this->db->delete('file_translation', $where);
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

        $handlers = array(
            'image' => array(
                'extensions' => array('jpg', 'jpeg', 'gif', 'png'),
                'path' => 'image/upload/common',
                'filesize' => null,
                'mime_types' => array(),
                'handlers' => array(
                    'validator' => array('core\\handlers\\file\\Validator', 'image')
                )
            ),
            'p12' => array(
                'extensions' => array('p12'),
                'path' => 'private/certificates',
                'handlers' => array(
                    'validator' => array('core\\handlers\\file\\Validator', 'p12')
                )
            ),
            'csv' => array(
                'extensions' => array('csv'),
                'path' => 'image/upload/common',
                'handlers' => array(
                    'validator' => array('core\\handlers\\file\\Validator', 'csv')
                )
            ),
            'zip' => array(
                'extensions' => array('zip'),
                'path' => 'upload',
                'handlers' => array(
                    'validator' => array('core\\handlers\\file\\Validator', 'zip')
                )
            ),
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

}
