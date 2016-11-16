<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Field as ModelsField;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate field data
 */
class Field extends BaseValidator
{

    /**
     * Field model instance
     * @var \core\models\Field $field
     */
    protected $field;

    /**
     * Constructor
     * @param ModelsField $field
     */
    public function __construct(ModelsField $field)
    {
        parent::__construct();

        $this->field = $field;
    }

    /**
     * Performs full field data validation
     * @param array $submitted
     * @param array $options
     */
    public function field(array &$submitted, array $options = array())
    {
        $this->validateField($submitted);
        $this->validateTitle($submitted);
        $this->validateWeight($submitted);
        $this->validateTranslation($submitted);
        $this->validateTypeField($submitted);
        $this->validateWidgetTypeField($submitted);

        return $this->getResult();
    }

    /**
     * Validates a field to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateField(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->field->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Field')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates field type
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTypeField(array $submitted)
    {
        if (!empty($submitted['update'])) {
            return null; // Cannot change type of existing field
        }

        if (empty($submitted['type'])) {
            $this->errors['type'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Type')
            ));
            return false;
        }

        $types = $this->field->getTypes();

        if (empty($types[$submitted['type']])) {
            $this->errors['type'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Type')));
            return false;
        }

        return true;
    }

    /**
     * Validates field widget type
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateWidgetTypeField(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['widget'])) {
            return null;
        }

        if (empty($submitted['widget'])) {
            $this->errors['widget'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Widget')
            ));
            return false;
        }

        $types = $this->field->getWidgetTypes();

        if (empty($types[$submitted['widget']])) {
            $this->errors['widget'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Widget')));
            return false;
        }

        return true;
    }

}
