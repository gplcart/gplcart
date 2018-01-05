<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate files to be stored in the database
 */
class File extends ComponentValidator
{

    /**
     * Default path for uploaded files
     */
    const PATH = 'image/upload/common';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
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
            $this->setErrorUnavailable('update', $this->translation->text('File'));
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
            $this->setErrorLengthRange('title', $this->translation->text('Title'), 0, 255);
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
        if ($this->isUpdating() || $this->isError()) {
            return null;
        }

        $field = 'file';

        if (isset($this->options['field']) && $this->options['field'] !== $field) {
            return null;
        }

        $path = $this->getSubmitted('path');
        $label = $this->translation->text('File');

        if (isset($path)) {
            if (is_readable(gplcart_file_absolute($path))) {
                return true;
            }
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $result = $this->file_transfer->upload($file, null, self::PATH);

        if ($result !== true) {
            $this->setError($field, (string) $result);
            return false;
        }

        $uploaded = $this->file_transfer->getTransferred(true);
        $this->setSubmitted('path', $uploaded);
        return true;
    }

}
