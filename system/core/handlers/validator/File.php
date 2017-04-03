<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\File as FileModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate files to be stored in the database
 */
class File extends ComponentValidator
{

    /**
     * Default path for uploaded field that is relative to main file directory
     */
    const UPLOAD_PATH = 'image/upload/common';

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateFile();
        $this->validateTitleFile();
        $this->validateDescription();
        $this->validateWeight();
        $this->validateTranslation();
        $this->validatePathFile();

        return $this->getResult();
    }

    /**
     * Validates a file to be updated
     * @return boolean|null
     */
    protected function validateFile()
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
     * @return boolean
     */
    protected function validateTitleFile()
    {
        $title = $this->getSubmitted('title');

        if (isset($title) && mb_strlen($title) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Title'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('title', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a path of existing file or uploads a file
     * @return boolean|null
     */
    protected function validatePathFile()
    {
        if ($this->isUpdating()) {
            return null; // Existing files cannot be changed
        }

        if ($this->isError()) {
            return null; // Do not if an error has occured before
        }

        $path = $this->getSubmitted('path');

        //Validate an existing file if the path is provided
        if (isset($path)) {
            if (is_readable(GC_FILE_DIR . "/$path")) {
                return true;
            }
            $vars = array('@name' => $this->language->text('File'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('file', $error);
            return false;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $vars = array('@field' => $this->language->text('File'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('file', $error);
            return false;
        }

        $result = $this->file->upload($file, null, self::UPLOAD_PATH);

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $this->setSubmitted('path', $uploaded);
        return true;
    }

}
