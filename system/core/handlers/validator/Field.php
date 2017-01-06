<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate field data
 */
class Field extends BaseValidator
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param FieldModel $field
     */
    public function __construct(FieldModel $field)
    {
        parent::__construct();

        $this->field = $field;
    }

    /**
     * Performs full field data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function field(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateField($options);
        $this->validateTitle($options);
        $this->validateWeight($options);
        $this->validateTranslation($options);
        $this->validateTypeField($options);
        $this->validateWidgetTypeField($options);

        return $this->getResult();
    }

    /**
     * Validates a field to be updated
     * @return boolean|null
     */
    protected function validateField()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->field->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Field'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a field type
     * @param array $options
     * @return boolean|null
     */
    protected function validateTypeField(array $options)
    {
        if ($this->isUpdating()) {
            return null; // Cannot change type of existing field
        }

        $type = $this->getSubmitted('type', $options);

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $types = $this->field->getTypes();

        if (empty($types[$type])) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a field widget type
     * @param array $options
     * @return boolean|null
     */
    protected function validateWidgetTypeField(array $options)
    {
        $type = $this->getSubmitted('widget', $options);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Widget'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('widget', $error, $options);
            return false;
        }

        $types = $this->field->getWidgetTypes();

        if (empty($types[$type])) {
            $vars = array('@name' => $this->language->text('Widget'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('widget', $error, $options);
            return false;
        }

        return true;
    }

}
