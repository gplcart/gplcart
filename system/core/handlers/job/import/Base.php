<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\job\import;

use gplcart\core\Container;

/**
 * Base class for import handlers
 */
class Base
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Validator model instance
     * @var \gplcart\core\models\Validator $validator
     */
    protected $validator;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * CSV class instance
     * @var \gplcart\core\helpers\Csv $csv
     */
    protected $csv;

    /**
     * An array of errors
     * @var array
     */
    protected $errors;

    /**
     * An array of parsed CSV rows
     * @var array
     */
    protected $rows;

    /**
     * An array of the current job
     * @var array
     */
    protected $job;

    /**
     * An array of the current prepared line data
     * @var array
     */
    protected $data;

    /**
     * Constructor
     */
    public function __construct()
    {
        /* @var $config \gplcart\core\Config */
        $this->config = Container::get('gplcart\\core\\Config');

        /* @var $csv \gplcart\core\helpers\Csv */
        $this->csv = Container::get('gplcart\\core\\helpers\\Csv');

        /* @var $file \gplcart\core\models\File */
        $this->file = Container::get('gplcart\\core\\models\\File');

        /* @var $user \gplcart\core\models\User */
        $this->user = Container::get('gplcart\\core\\models\\User');

        /* @var $language \gplcart\core\models\Language */
        $this->language = Container::get('gplcart\\core\\models\\Language');

        /* @var $validator \gplcart\core\models\Validator */
        $this->validator = Container::get('gplcart\\core\\models\\Validator');
    }

    /**
     * Parses a portion of CSV file
     * @param array $job
     */
    protected function start(array &$job)
    {
        $this->job = &$job;

        // Reset all existing data
        $this->rows = array();
        $this->errors = array();

        $limit = $this->job['data']['limit'];
        $file = $this->job['data']['filepath'];
        $size = $this->job['data']['filesize'];
        $delimiter = $this->job['data']['delimiter'];
        $header = $this->job['data']['operation']['csv']['header'];

        $this->csv->setFile($file, $size)
                ->setLimit($limit)
                ->setHeader($header)
                ->setDelimiter($delimiter);

        if (empty($this->job['context']['offset'])) {
            $this->rows = $this->csv->skipHeader()->parse();
        } else {
            $this->rows = $this->csv->setOffset($this->job['context']['offset'])->parse();
        }

        if (empty($this->rows)) {
            $this->job['status'] = false;
            $this->job['done'] = $this->job['total'];
        } else {
            $this->job['context']['offset'] = $this->csv->getOffset();
        }
    }

    /**
     * Finishes the current iteration
     */
    protected function finish()
    {
        $this->job['done'] = empty($this->job['context']['offset']) ? //
                $this->job['total'] : $this->job['context']['offset'];
        $this->job['errors'] += $this->countErrors();

        $vars = array('@last' => $this->job['context']['line']);
        $this->job['message']['process'] = $this->language->text('Last processed line: @last', $vars);
    }

    /**
     * Returns a total number of errors and logs them
     * @return integer
     */
    protected function countErrors()
    {
        $count = 0;
        foreach ($this->errors as $line => $errors) {
            $errors = array_filter($errors);
            $count += count($errors);
            $this->logErrors($line, $errors);
        }

        return $count;
    }

    /**
     * Logs all errors happened on the line
     * @param integer $line
     * @param array $errors
     * @return boolean
     */
    protected function logErrors($line, array $errors)
    {
        if (empty($this->job['data']['operation']['log']['errors'])) {
            return false;
        }

        $messages = implode(PHP_EOL, $errors);
        $line_message = $this->language->text('Line @num', array('@num' => $line));
        $data = array($line_message, $messages);

        return gplcart_file_csv($this->job['data']['operation']['log']['errors'], $data);
    }

    /**
     * Sets a data to be inserted/updated
     * @param array $row
     */
    protected function prepare(array $row)
    {
        // Advance line counter
        $this->job['context']['line'] ++;

        // Trim and remove empty values
        $this->data = array_filter(array_map('trim', $row));

        $operation = $this->job['data']['operation'];

        // Set add/update mode
        if (isset($this->data[$operation['entity_id']])//
                && $this->data[$operation['entity_id']] !== '') {
            $this->data['update'] = $this->data[$operation['entity_id']];
        }
    }

    /**
     * Validates and prepares a data to be imported
     * @return boolean
     */
    public function validate()
    {
        $operation = $this->job['data']['operation'];

        // Check access
        if (empty($this->data['update'])//
                && !$this->user->access($operation['access']['add'])) {
            $this->setError($this->language->text('No access to add the entity'));
            return false;
        }

        if (!empty($this->data['update']) && !$this->user->access($operation['access']['update'])) {
            $this->setError($this->language->text('No access to update the entity'));
            return false;
        }

        // Validate the entity
        $result = $this->validator->run($operation['validator'], $this->data);

        if ($result === true) {
            return true;
        }

        // Errors can be nested, so we flatten them into a simple array
        $errors = gplcart_array_flatten((array) $result);

        $this->setError($errors);
        return false;
    }

    /**
     * Sets a error
     * @param string|array $error
     */
    protected function setError($error)
    {
        $errors = (array) $error;
        $line = $this->job['context']['line'];
        $existing = empty($this->errors[$line]) ? array() : $this->errors[$line];
        $this->errors[$line] = gplcart_array_merge($existing, $errors);
    }

    /**
     * Whether a error exists
     * @param string|null $line
     * @return boolean
     */
    protected function isError($line = null)
    {
        if (isset($line)) {
            return isset($this->errors[$line]);
        }
        return !empty($this->errors);
    }

    /**
     * Returns an array of values from a string using a delimiter character
     * @param string $string
     * @return array
     */
    protected function getMultiple($string)
    {
        $delimiter = $this->config->get('csv_delimiter_multiple', '|');
        return array_filter(array_map('trim', explode($delimiter, $string)));
    }

    /**
     * Returns an array of image data
     * @param string $string
     * @return array
     */
    protected function getImages($string)
    {
        $images = array();
        foreach ($this->getMultiple($string) as $image) {
            $path = $this->getImagePath($image);
            if (!empty($path)) {
                $images[] = array('path' => $path);
            }
        }

        return $images;
    }

    /**
     * Validates and returns a relative image path.
     * If given an absolute URL, the file will be downloaded
     * @param string $image
     * @return boolean|string
     */
    protected function getImagePath($image)
    {
        if (0 === strpos($image, 'http')) {
            return $this->downloadImage($image);
        }

        $path = trim($image, '/');
        $fullpath = GC_FILE_DIR . "/$path";

        if (!file_exists($fullpath)) {
            $vars = array('@path' => $path);
            $error = $this->language->text('File @path does not exist', $vars);
            $this->setError($error);
            return false;
        }

        $result = $this->file->validate($fullpath);

        if ($result === true) {
            return $path;
        }

        $this->setError((string) $result);
        return false;
    }

    /**
     * Downloads a remote image
     * @param string $url
     * @return boolean|string
     */
    protected function downloadImage($url)
    {
        $operation = $this->job['data']['operation'];
        $destination = 'image/upload/';
        $destination .= $this->config->get("{$operation['id']}_image_dirname", $operation['id']);

        $this->file->setUploadPath($destination)->setHandler('image');
        $result = $this->file->wget($url);

        if ($result === true) {
            return $this->file->getUploadedFile(true);
        }

        $this->setError((string) $result);
        return false;
    }

}
