<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Field as FieldModel;
use gplcart\core\models\ProductClass as ProductClassModel;
use gplcart\core\models\ProductClassField as ProductClassFieldModel;


/**
 * Provides methods to validate a product class fields
 */
class ProductClassField extends ComponentValidator
{
    /**
     * @var \gplcart\core\models\Field $field
     */
    protected $field;

    /**
     * Product class model instance
     * @var \gplcart\core\models\ProductClass $product_class
     */
    protected $product_class;

    /**
     * Product class field model instance
     * @var \gplcart\core\models\ProductClassField $product_class_field
     */
    protected $product_class_field;

    /**
     * @param ProductClassModel $product_class
     * @param ProductClassFieldModel $product_class_field
     * @param FieldModel $field
     */
    public function __construct(ProductClassModel $product_class,
                                ProductClassFieldModel $product_class_field, FieldModel $field)
    {
        parent::__construct();

        $this->field = $field;
        $this->product_class = $product_class;
        $this->product_class_field = $product_class_field;
    }

    /**
     * Performs full product class field validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function productClassField(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateProductClassField();
        $this->validateProductClassProductClassField();
        $this->validateWeight();
        $this->validateBool('required');
        $this->validateBool('multiple');
        $this->validateFieldIdProductClassField();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a product class field to be updated
     * @return boolean
     */
    protected function validateProductClassField()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->product_class_field->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Address'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a product class ID
     * @return boolean|null
     */
    protected function validateProductClassProductClassField()
    {
        $field = 'product_class_id';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Product class');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorInteger($field, $label);
            return false;
        }

        $product_class = $this->product_class->get($value);

        if (empty($product_class)) {
            $this->setErrorUnavailable('update', $label);
            return false;
        }

        return true;
    }

    /**
     * Validates one or several field IDs
     * @return bool|null
     */
    protected function validateFieldIdProductClassField()
    {
        $field = 'field_id';

        if ($this->isExcluded($field) || $this->isError('product_class_id')) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Fields');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $updating = $this->getUpdating();
        $product_class_id = $this->getSubmitted('product_class_id');

        if (!isset($product_class_id) && isset($updating['product_class_id'])) {
            $product_class_id = $updating['product_class_id'];
        }

        if (empty($product_class_id)) {
            $this->setErrorUnavailable($field, $this->translation->text('Unknown product class ID'));
        }

        $existing = $this->product_class_field->getList(array('index' => 'field_id',
            'product_class_id' => $product_class_id));

        $processed = array();
        foreach ((array) $value as $field_id) {

            if (empty($field_id) || !is_numeric($field_id)) {
                $this->setErrorInvalid($field, $label);
                return false;
            }

            if (in_array($field_id, $processed)) {
                $this->setErrorExists($field, $label);
                return false;
            }

            if (isset($existing[$field_id])) {
                $this->setErrorExists($field, $label);
                return false;
            }

            $data = $this->field->get($field_id);

            if (empty($data)) {
                $this->setErrorUnavailable($field, $label);
                return false;
            }

            $processed[] = $field_id;
        }

        return true;
    }

}
