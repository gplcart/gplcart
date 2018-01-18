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

        $this->unsetSubmitted('update');

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

        $data = $this->file->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('File'));
            return false;
        }

        $this->setSubmitted('update', $data);
        return true;
    }

    /**
     * Validates a title field
     * @return boolean
     */
    protected function validateTitleFile()
    {
        $field = 'title';
        $value = $this->getSubmitted($field);

        if (isset($value) && mb_strlen($value) > 255) {
            $this->setErrorLengthRange($field, $this->translation->text('Title'), 0, 255);
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

        if ($this->isExcluded($field)) {
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

        $this->setSubmitted('path', $this->file_transfer->getTransferred(true));
        return true;
    }

}
