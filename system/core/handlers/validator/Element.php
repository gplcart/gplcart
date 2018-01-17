<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Container;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Parent class handlers containing methods to validate single elements
 */
class Element extends BaseValidator
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->translation = Container::get('gplcart\\core\\models\\Translation');
    }

    /**
     * Set "Field required" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorRequired($field, $label)
    {
        $error = $this->translation->text('@field is required', array('@field' => $label));
        return $this->setError($field, $error);
    }

    /**
     * Set "Field not numeric" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorNumeric($field, $label)
    {
        $error = $this->translation->text('@field must be numeric', array('@field' => $label));
        return $this->setError($field, $error);
    }

    /**
     * Set "Field not integer" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorInteger($field, $label)
    {
        $error = $this->translation->text('@field must be integer', array('@field' => $label));
        return $this->setError($field, $error);
    }

    /**
     * Set "Object unavailable" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorUnavailable($field, $label)
    {
        $error = $this->translation->text('@name is unavailable', array('@name' => $label));
        return $this->setError($field, $error);
    }

    /**
     * Set "Length must be between min and max" error
     * @param string $field
     * @param string $label
     * @param int $min
     * @param int $max
     * @return array
     */
    protected function setErrorLengthRange($field, $label, $min = 1, $max = 255)
    {
        $vars = array('@min' => $min, '@max' => $max, '@field' => $label);
        $error = $this->translation->text('@field must be @min - @max characters long', $vars);
        return $this->setError($field, $error);
    }

    /**
     * Set "Invalid value" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorInvalid($field, $label)
    {
        $error = $this->translation->text('@field has invalid value', array('@field' => $label));
        return $this->setError($field, $error);
    }

    /**
     * Set "Object already exists" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorExists($field, $label)
    {
        $error = $this->translation->text('@name already exists', array('@name' => $label));
        return $this->setError($field, $error);
    }

}
