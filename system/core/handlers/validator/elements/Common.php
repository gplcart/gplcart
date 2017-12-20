<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\elements;

use gplcart\core\handlers\validator\Element as ElementValidator;

/**
 * Methods to validate single elements
 */
class Common extends ElementValidator
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validates the field/value is not empty
     * @param array $submitted
     * @param array $options
     * @return bool|array
     */
    public function required(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (empty($value)) {
            return $this->setErrorRequired($options['field'], $options['label']);
        }

        return true;
    }

    /**
     * Validates the field/value is numeric
     * @param array $submitted
     * @param array $options
     * @return bool|array
     */
    public function numeric(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (is_numeric($value)) {
            return true;
        }

        return $this->setErrorNumeric($options['field'], $options['label']);
    }

    /**
     * Validates the field/value contains only digits
     * @param array $submitted
     * @param array $options
     * @return bool|array
     */
    public function integer(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (filter_var($value, FILTER_VALIDATE_INT) === false) {
            return $this->setErrorInteger($options['field'], $options['label']);
        }

        return true;
    }

    /**
     * Validates the field/value length
     * @param array $submitted
     * @param array $options
     * @return bool|array
     */
    public function length(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null,
            'arguments' => array()
        );

        $value = gplcart_array_get($submitted, $options['field']);

        list($min, $max) = $options['arguments'] + array(1, 255);

        $length = mb_strlen($value);
        if ($min <= $length && $length <= $max) {
            return true;
        }

        return $this->setErrorLengthRange($options['field'], $options['label'], $min, $max);
    }

    /**
     * Validates the field/value matches a regexp pattern
     * @param array $submitted
     * @param array $options
     * @return bool|array
     */
    public function regexp(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null,
            'arguments' => array()
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (empty($options['arguments']) || preg_match(reset($options['arguments']), $value) !== 1) {
            return $this->setErrorInvalid($options['field'], $options['label']);
        }

        return true;
    }

    /**
     * Validates the date format
     * @param array $submitted
     * @param array $options
     * @link http://php.net/manual/en/function.strtotime.php
     * @return bool|array
     */
    public function dateformat(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (strtotime($value) === false) {
            return $this->setErrorInvalid($options['field'], $options['label']);
        }

        return true;
    }

    /**
     * Validates the field/value is a JSON string
     * @param array $submitted
     * @param array $options
     * @return array|bool
     */
    public function json(array $submitted, array $options)
    {
        $options += array(
            'field' => null,
            'label' => null
        );

        $value = gplcart_array_get($submitted, $options['field']);

        if (json_decode($value) === null) {
            return $this->setErrorInvalid($options['field'], $options['label']);
        }

        return true;
    }

}
