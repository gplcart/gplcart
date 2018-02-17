<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\FieldValue as FieldValueModel;

/**
 * Provides methods to validate field data
 */
class FieldValue extends Component
{

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
     * @param FieldModel $field
     * @param FieldValueModel $field_value
     */
    public function __construct(FieldModel $field, FieldValueModel $field_value)
    {
        parent::__construct();

        $this->field = $field;
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
        $this->validateUploadImages('field_value');

        $this->unsetSubmitted('update');
        $this->unsetSubmitted('field');

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
            $this->setErrorUnavailable('update', $this->translation->text('Field value'));
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
        $field = 'field_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Field');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $field_data = $this->field->get($value);

        if (empty($field_data)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted('field', $field_data);
        return true;
    }

    /**
     * Validates a color code
     * @return boolean|null
     */
    protected function validateColorFieldValue()
    {
        $field = 'color';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value) || $value === '') {
            return null;
        }

        if (preg_match('/#([a-fA-F0-9]{3}){1,2}\b/', $value) !== 1) {
            $this->setErrorInvalid($field, $this->translation->text('Color'));
            return false;
        }

        // HTML5 color field cannot have empty value
        // Default value is #000000
        // Assuming black is empty
        if ($value === '#000000') {
            $this->setSubmitted($field, '');
        }

        return true;
    }

}
