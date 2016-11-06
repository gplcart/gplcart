<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\classes\Request;
use core\models\File as ModelsFile;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate files to be stored in the database
 */
class File extends BaseValidator
{

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsFile $file
     * @param Request $request
     */
    public function __construct(ModelsFile $file, Request $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Performs full file data validation
     * @param array $submitted
     */
    public function file(array &$submitted, array $options = array())
    {
        $this->validateFile($submitted);
        $this->validateTitleFile($submitted);
        $this->validateDescription($submitted);
        $this->validateWeight($submitted);
        $this->validateTranslation($submitted);
        $this->validatePathFile($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a file to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateFile(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $file = $this->file->get($submitted['update']);

            if (empty($file)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('File')));
                return false;
            }

            $submitted['update'] = $file;
        }

        return true;
    }

    /**
     * Validates a title field
     * @param array $submitted
     * @return boolean
     */
    protected function validateTitleFile(array &$submitted)
    {
        if (isset($submitted['title']) && mb_strlen($submitted['title']) > 255) {
            $options = array('@max' => 255, '@field' => $this->language->text('Title'));
            $this->errors['title'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a path of existing file or uploads a file
     * @param array $submitted
     * @return boolean
     */
    protected function validatePathFile(array &$submitted)
    {
        if (!empty($submitted['update'])) {
            return null; // Existing files cannot be changed
        }

        // Prevent uploading if errors have occurred before
        if (!empty($this->errors)) {
            return null;
        }

        //Validate an existing file if the path is provided
        if (isset($submitted['path'])) {

            if (is_readable(GC_FILE_DIR . "/{$submitted['path']}")) {
                return true;
            }

            $this->errors['file'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('File')));
            return false;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $this->errors['file'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('File')
            ));
            return false;
        }

        $result = $this->file->setUploadPath('image/upload/common')->upload($file);

        if ($result !== true) {
            $this->errors['file'] = $result;
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $submitted['path'] = $uploaded;
        return true;
    }

}
