<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\File as FileModel;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate field data
 */
class FieldValue extends BaseValidator
{

    /**
     * Path for uploaded field value files that is relative to main file directory
     */
    const UPLOAD_PATH = 'image/upload/field_value';

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
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Field value model instance
     * @var \gplcart\core\models\FieldValue $field_value
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
     * @return boolean|array
     */
    public function fieldValue(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateFieldValue();
        $this->validateTitle();
        $this->validateWeight();
        $this->validateTranslation();
        $this->validateFieldFieldValue();
        $this->validateColorFieldValue();
        $this->validateFileFieldValue();

        return $this->getResult();
    }

    /**
     * Validates a field value to be updated
     * @return boolean|null
     */
    protected function validateFieldValue()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->field_value->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Field value'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a field id
     * @return boolean|null
     */
    protected function validateFieldFieldValue()
    {
        $field_id = $this->getSubmitted('field_id');

        if ($this->isUpdating() && !isset($field_id)) {
            return null;
        }

        if (empty($field_id)) {
            $vars = array('@field' => $this->language->text('Field'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('field_id', $error);
            return false;
        }

        if (!is_numeric($field_id)) {
            $vars = array('@field' => $this->language->text('Field'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('field_id', $error);
            return false;
        }

        $field = $this->field->get($field_id);

        if (empty($field['field_id'])) {
            $vars = array('@name' => $this->language->text('Field'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('field_id', $error);
            return false;
        }

        $this->setSubmitted('field', $field);
        return true;
    }

    /**
     * Validates a color code
     * @return boolean|null
     */
    protected function validateColorFieldValue()
    {
        $color = $this->getSubmitted('color');

        if (!isset($color) || $color === '') {
            return null;
        }

        if (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $color) !== 1) {
            $vars = array('@field' => $this->language->text('Color'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('color', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates uploaded image
     * @return boolean|null
     */
    protected function validateFileFieldValue()
    {
        // Do not upload if an error has occurred before
        if ($this->isError()) {
            return null;
        }

        $file = $this->request->file('file');
        $path = $this->getSubmitted('path');

        if ($this->isUpdating() && (!isset($path) && empty($file))) {
            return null;
        }

        if (isset($path)) {
            
            if (is_readable(GC_FILE_DIR . "/$path")) {
                return true;
            }
            
            $vars = array('@name' => $this->language->text('File'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('file', $error);
            
            return false;
        }

        if (empty($file)) {
            return true;
        }

        $result = $this->file->setUploadPath(self::UPLOAD_PATH)
                ->setHandler('image')
                ->upload($file);

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $this->setSubmitted('path', $uploaded);
        return true;
    }

}
