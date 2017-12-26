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
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Language as LanguageModel,
    gplcart\core\models\Validator as ValidatorModel,
    gplcart\core\models\Translation as TranslationModel;
use gplcart\core\helpers\SocketClient as SocketClientHelper;

/**
 * Manages basic behaviors and data related to transfering files
 */
class FileTransfer
{

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
     * File model class instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

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
     * Path of the transferred file
     * @var string
     */
    private $transferred;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param LanguageModel $language
     * @param ValidatorModel $validator
     * @param FileModel $file
     * @param TranslationModel $translation
     * @param SocketClientHelper $socket
     */
    public function __construct(Hook $hook, Config $config, LanguageModel $language,
            ValidatorModel $validator, FileModel $file, TranslationModel $translation,
            SocketClientHelper $socket)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->socket = $socket;

        $this->file = $file;
        $this->language = $language;
        $this->validator = $validator;
        $this->translation = $translation;
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
        $result = $this->transferred = null;
        $this->hook->attach('file.upload.before', $post, $handler, $path, $result, $this);

        if (isset($result)) {
            return $result;
        }

        if (!empty($post['error']) || empty($post['tmp_name']) || !is_uploaded_file($post['tmp_name'])) {
            return $this->translation->text('Unable to upload the file');
        }

        $this->setHandler($handler);
        $this->setDestination($path);

        $result = $this->validate($post['tmp_name'], $post['name']);

        if ($result !== true) {
            unlink($post['tmp_name']);
            return $result;
        }

        try {
            $result = $this->finalize($post['tmp_name'], $post['name'], true);
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

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
        $return = array(
            'errors' => array(),
            'transferred' => array()
        );

        if (!gplcart_file_multi_upload($files)) {
            return $return;
        }

        foreach ($files as $key => $file) {

            $result = $this->upload($file, $handler, $path);

            if ($result === true) {
                $return['transferred'][$key] = $this->getTransferred(true);
                continue;
            }

            $return['errors'][$key] = (string) $result;
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
        $result = $this->transferred = null;
        $this->hook->attach('file.download.before', $url, $handler, $path, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $this->setHandler($handler);
        $this->setDestination($path);

        try {
            $temp = $this->writeTempFile($url);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }

        try {
            $this->validateHandler($temp);
        } catch (\Exception $ex) {
            unlink($temp);
            return $ex->getMessage();
        }

        try {
            $result = $this->finalize($temp, $this->destination, false);
        } catch (\Exception $ex) {
            $result = $ex->getMessage();
        }

        $this->hook->attach('file.download.after', $url, $handler, $temp, $result, $this);
        return $result;
    }

    /**
     * Writes a temporary file from a remote file
     * @param string $url
     * @return string
     * @throws \RuntimeException
     */
    protected function writeTempFile($url)
    {
        $temp = gplcart_file_tempname();

        $fh = fopen($temp, "w");

        if (!is_resource($fh)) {
            throw new \RuntimeException($this->translation->text('Failed to open temporary file'));
        }

        $response = $this->socket->request($url);
        fwrite($fh, $response['data']);
        fclose($fh);

        return $temp;
    }

    /**
     * Finalize file transfer
     * @param string $temp
     * @param string $to
     * @param bool $upload
     * @return boolean
     */
    protected function finalize($temp, $to, $upload)
    {
        if (!isset($this->destination)) {
            $this->transferred = $temp;
            return true;
        }

        $directory = gplcart_file_absolute(gplcart_file_relative($this->destination));
        $pathinfo = $upload ? pathinfo($to) : pathinfo($directory);

        if ($upload) {
            $filename = $this->prepareFileName($pathinfo['filename'], $pathinfo['extension']);
        } else {
            $filename = $pathinfo['basename'];
            $directory = $pathinfo['dirname'];
        }

        if (!file_exists($directory) && !mkdir($directory, 0775, true)) {
            unlink($temp);
            throw new \RuntimeException($this->translation->text('Unable to create @name', array('@name' => $directory)));
        }

        $destination = "$directory/$filename";

        if ($upload) {
            $destination = gplcart_file_unique($destination);
        }

        $copied = copy($temp, $destination);
        unlink($temp);

        if (!$copied) {
            $vars = array('@source' => $temp, '@destination' => $destination);
            throw new \RuntimeException($this->translation->text('Unable to move @source to @destination', $vars));
        }

        chmod($destination, 0644);
        $this->transferred = $destination;
        return true;
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
            return $this->translation->text('Unknown filename');
        }

        if (empty($pathinfo['extension'])) {
            return $this->translation->text('Unknown file extension');
        }

        if ($this->handler === false) {
            return true;
        }

        if (!isset($this->handler)) {
            try {
                $this->setHandlerByExtension($pathinfo['extension']);
            } catch (\Exception $ex) {
                return $ex->getMessage();
            }
        }

        try {
            return $this->validateHandler($path, $pathinfo['extension']);
        } catch (\Exception $ex) {
            return $ex->getMessage();
        }
    }

    /**
     *
     * Validates a file using a validator
     * @param string $file
     * @param string|null $extension
     * @return bool
     * @throws \RuntimeException
     */
    protected function validateHandler($file, $extension = null)
    {
        if (empty($this->handler['validator'])) {
            throw new \RuntimeException($this->translation->text('Unknown handler'));
        }

        if (!empty($this->handler['extensions']) && isset($extension) && !in_array($extension, $this->handler['extensions'])) {
            throw new \RuntimeException($this->translation->text('Unsupported file extension'));
        }

        if (isset($this->handler['filesize']) && filesize($file) > $this->handler['filesize']) {
            throw new \RuntimeException($this->translation->text('File size exceeds %num bytes', array('%num' => $this->handler['filesize'])));
        }

        $result = $this->validator->run($this->handler['validator'], $file, $this->handler);

        if ($result !== true) {
            throw new \RuntimeException($result);
        }

        return true;
    }

    /**
     * Sets the current transfer handler
     * @param mixed $id
     *  - string: load by validator ID
     *  - false: disable validator at all,
     *  - null: detect validator by file extension
     * @return $this
     */
    public function setHandler($id)
    {
        if (is_string($id)) {
            $this->handler = $this->file->getHandler($id);
        } else {
            $this->handler = $id;
        }

        return $this;
    }

    /**
     * Find and set handler by a file extension
     * @param string $extension
     * @return array
     * @throws \RuntimeException
     */
    protected function setHandlerByExtension($extension)
    {
        if (!in_array($extension, $this->file->supportedExtensions())) {
            throw new \RuntimeException($this->translation->text('Unsupported file extension'));
        }

        return $this->handler = $this->file->getHandler(".$extension");
    }

    /**
     * Sets path to the file final destination
     * @param string $path
     * @return $this
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
        return $relative ? gplcart_file_relative($this->transferred) : $this->transferred;
    }

}
