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
        $this->validateUploadFile();
        $this->validatePathFile();
        $this->validateEntityFile();
        $this->validateEntityIdFile();
        $this->validateFileTypeFile();
        $this->validateMimeTypeFile();

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
     * Validates a file path
     * @return boolean|null
     */
    protected function validatePathFile()
    {
        $field = 'path';
        $value = $this->getSubmitted($field);

        if ($this->isExcluded($field) || $this->isError()) {
            return null;
        }

        if (!isset($value) && $this->isUpdating()) {
            return null;
        }

        $label = $this->translation->text('File');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_readable(gplcart_file_absolute($value))) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates file upload
     * @return bool|null
     */
    protected function validateUploadFile()
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            return null;
        }

        $result = $this->file_transfer->upload($file, null, 'image/upload/common');

        if ($result !== true) {
            $this->setError('path', (string) $result);
            return false;
        }

        $this->setSubmitted('path', $this->file_transfer->getTransferred(true));
        return true;
    }

    /**
     * Validates entity name
     * @return bool|null
     */
    protected function validateEntityFile()
    {
        $field = 'entity';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Entity');

        if (empty($value) || strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 1, 255);
            return false;
        }

        if (preg_match('/[^a-z_]+/', $value) === 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

    /** Validates entity ID
     * @return bool|null
     */
    protected function validateEntityIdFile()
    {
        $field = 'entity_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Entity ID');

        if (!is_numeric($value)) {
            $this->setErrorInteger($field, $label);
            return false;
        }

        if (strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 0, 10);
            return false;
        }

        return true;
    }

    /** Validates file type
     * @return bool|null
     */
    protected function validateFileTypeFile()
    {
        $field = 'file_type';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Type');

        if (empty($value) || strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 1, 255);
            return false;
        }

        if (preg_match('/[^a-z_]+/', $value) === 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        return true;
    }

    /** Validates file MIME type
     * @return bool|null
     */
    protected function validateMimeTypeFile()
    {
        $field = 'mime_type';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('MIME type');

        if (empty($value) || strlen($value) > 255) {
            $this->setErrorLengthRange($field, $label, 1, 255);
            return false;
        }

        return true;
    }

}
