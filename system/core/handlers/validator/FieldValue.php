<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\helpers\Request as RequestHelper;
use core\models\File as FileModel;
use core\models\Field as FieldModel;
use core\models\FieldValue as FieldValueModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate field data
 */
class FieldValue extends BaseValidator
{

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
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Field value model instance
     * @var \core\models\FieldValue $field_value
     */
    protected $field_value;

    /**
     * Constructor
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     * @param FileModel $file
     * @param RequestHelper $request
     */
    public function __construct(FieldModel $field, FieldValueModel $field_value,
            FileModel $file, RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->field = $field;
        $this->request = $request;
        $this->field_value = $field_value;
    }

    /**
     * Performs full field value data validation
     * @param array $submitted
     * @param array $options
     */
    public function fieldValue(array &$submitted, array $options = array())
    {
        $this->validateFieldValue($submitted);
        $this->validateTitle($submitted);
        $this->validateWeight($submitted);
        $this->validateTranslation($submitted);
        $this->validateFieldFieldValue($submitted);
        $this->validateColorFieldValue($submitted);
        $this->validateFileFieldValue($submitted);

        return $this->getResult();
    }

    /**
     * Validates a field value to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateFieldValue(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->field_value->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('@name is unavailable', array(
                    '@name' => $this->language->text('Field value')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a field id
     * @param array $submitted
     * @return boolean
     */
    protected function validateFieldFieldValue(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['field_id'])) {
            return null;
        }

        if (empty($submitted['field_id'])) {
            $this->errors['field_id'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Field')
            ));
            return false;
        }

        if (!is_numeric($submitted['field_id'])) {
            $options = array('@field' => $this->language->text('Field'));
            $this->errors['field_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $field = $this->field->get($submitted['field_id']);

        if (empty($field)) {
            $this->errors['field_id'] = $this->language->text('@name is unavailable', array(
                '@name' => $this->language->text('Field')));
            return false;
        }

        $submitted['field'] = $field;
        return true;
    }

    /**
     * Validates a color code
     * @param array $submitted
     * @return boolean
     */
    protected function validateColorFieldValue(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['color'])) {
            return null;
        }

        if (isset($submitted['field']['widget'])//
                && $submitted['field']['widget'] != 'color') {
            $submitted['color'] = '';
            return true;
        }

        if (empty($submitted['color'])) {
            $this->errors['color'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Color')
            ));
            return false;
        }

        if (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $submitted['color']) !== 1) {
            $this->errors['color'] = $this->language->text('Invalid color code');
            return false;
        }

        return true;
    }

    /**
     * Validates uploaded image
     * @param array $submitted
     * @return boolean
     */
    protected function validateFileFieldValue(array &$submitted)
    {
        // Prevent uploading if errors have occurred before
        if (!empty($this->errors)) {
            return null;
        }

        $file = $this->request->file('file');

        if (!empty($submitted['update']) && (!isset($submitted['path']) && empty($file))) {
            return null;
        }

        //Validate an existing file if the path is provided
        if (isset($submitted['path'])) {

            if (is_readable(GC_FILE_DIR . "/{$submitted['path']}")) {
                return true;
            }

            $this->errors['file'] = $this->language->text('@name is unavailable', array(
                '@name' => $this->language->text('Image')));
            return false;
        }

        if (empty($file)) {
            return true;
        }

        $result = $this->file->setUploadPath('image/upload/field_value')
                ->setHandler('image')
                ->upload($file);

        if ($result !== true) {
            $this->errors['file'] = $result;
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $submitted['path'] = $uploaded;
        return true;
    }

}
