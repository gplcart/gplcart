<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Field as FieldModel;
use gplcart\core\handlers\validator\BaseComponent as BaseComponentValidator;

/**
 * Provides methods to validate field data
 */
class Field extends BaseComponentValidator
{

    /**
     * Field model instance
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateField();
        $this->validateTitle();
        $this->validateWeight();
        $this->validateTranslation();
        $this->validateTypeField();
        $this->validateWidgetTypeField();

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
            $this->setErrorUnavailable('update', $this->translation->text('Field'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a field type
     * @return boolean|null
     */
    protected function validateTypeField()
    {
        if ($this->isUpdating()) {
            return null; // Cannot change type of existing field
        }

        $field = 'type';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $type = $this->getSubmitted($field);
        $label = $this->translation->text('Type');

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->field->getTypes();

        if (empty($types[$type])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a field widget type
     * @return boolean|null
     */
    protected function validateWidgetTypeField()
    {
        $field = 'widget';

        if ($this->isExcludedField($field)) {
            return null;
        }

        $type = $this->getSubmitted($field);
        $label = $this->translation->text('Widget');

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->field->getWidgetTypes();

        if (empty($types[$type])) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
