<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\File as FileModel;
use core\helpers\Request as RequestHelper;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate files to be stored in the database
 */
class File extends BaseValidator
{

    /**
     * Default path for uploaded field that is relative to main file directory
     */
    const UPLOAD_PATH = 'image/upload/common';

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param FileModel $file
     * @param RequestHelper $request
     */
    public function __construct(FileModel $file, RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Performs full file data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function file(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateFile($options);
        $this->validateTitleFile($options);
        $this->validateDescription($options);
        $this->validateWeight($options);
        $this->validateTranslation($options);
        $this->validatePathFile($options);

        return $this->getResult();
    }

    /**
     * Validates a file to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateFile(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $file = $this->file->get($id);

        if (empty($file)) {
            $vars = array('@name' => $this->language->text('File'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setSubmitted('update', $file);
        return true;
    }

    /**
     * Validates a title field
     * @param array $options
     * @return boolean
     */
    protected function validateTitleFile(array $options)
    {
        $title = $this->getSubmitted('title', $options);

        if (isset($title) && mb_strlen($title) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('title', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a path of existing file or uploads a file
     * @param array $options
     * @return boolean|null
     */
    protected function validatePathFile(array $options)
    {
        if ($this->isUpdating()) {
            return null; // Existing files cannot be changed
        }

        if ($this->isError()) {
            return null; // Do not if an error has occured before
        }

        $path = $this->getSubmitted('path', $options);

        //Validate an existing file if the path is provided
        if (isset($path)) {
            if (is_readable(GC_FILE_DIR . "/$path")) {
                return true;
            }
            $vars = array('@name' => $this->language->text('File'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('file', $error, $options);
            return false;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $vars = array('@field' => $this->language->text('File'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('file', $error, $options);
            return false;
        }

        $result = $this->file->setUploadPath(self::UPLOAD_PATH)->upload($file);

        if ($result !== true) {
            $this->setError('file', $result, $options);
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $this->setSubmitted('path', $uploaded);
        return true;
    }

}
