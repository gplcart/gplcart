<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Handler,
    gplcart\core\Container;

/**
 * Base validator class
 */
class Base extends Handler
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * An array of validation errors
     * @var array
     */
    protected $errors = array();

    /**
     * An array of submitted values
     * @var array
     */
    protected $submitted = array();

    /**
     * An array of options
     * @var array
     */
    protected $options = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->language = Container::get('gplcart\\core\\models\\Language');
    }

    /**
     * Sets a property
     * @param string $property
     * @param mixed $value
     */
    public function setProperty($property, $value)
    {
        $this->{$property} = $value;
    }

    /**
     * Returns a submitted value
     * @param null|string $key
     * @return mixed
     */
    protected function getSubmitted($key = null)
    {
        if (!isset($key)) {
            return $this->submitted;
        }

        $parents = $this->getParents($key);

        if (!isset($parents)) {
            return $this->submitted;
        }

        return gplcart_array_get($this->submitted, $parents);
    }

    /**
     * Sets a value to an array of submitted values
     * @param string $key
     * @param mixed $value
     */
    public function setSubmitted($key, $value)
    {
        $parents = $this->getParents($key);
        gplcart_array_set($this->submitted, $parents, $value);
    }

    /**
     * Removes a value from an array of submitted values
     * @param string $key
     */
    public function unsetSubmitted($key)
    {
        $parents = $this->getParents($key);
        gplcart_array_unset($this->submitted, $parents);
    }

    /**
     * Whether we update the submitted object
     * @param string $key
     * @return boolean
     */
    protected function isUpdating($key = 'update')
    {
        return !empty($this->submitted[$key]);
    }

    /**
     * Sets a data of updating object to the submitted values
     * @param mixed $data
     * @param string $key
     */
    protected function setUpdating($data, $key = 'update')
    {
        $this->submitted[$key] = $data;
    }

    /**
     * Returns an array of entity to be updated
     * @param string $key
     * @return array
     */
    protected function getUpdating($key = 'update')
    {
        return empty($this->submitted[$key]) ? array() : $this->submitted[$key];
    }

    /**
     * Returns either an ID of entity to be updated or false if no ID found (adding).
     * It also returns false if an array of updating entity has been loaded and set
     * @param string $key
     * @return boolean|string|integer
     */
    protected function getUpdatingId($key = 'update')
    {
        if (empty($this->submitted[$key]) || is_array($this->submitted[$key])) {
            return false;
        }

        return $this->submitted[$key];
    }

    /**
     * Returns an array that represents a path to the nested array value
     * @param string|array $key A base key
     * @return array
     */
    protected function getParents($key)
    {
        if (empty($this->options['parents'])) {
            return $key;
        }

        if (is_string($this->options['parents'])) {
            $this->options['parents'] = explode('.', $this->options['parents']);
        }

        return array_merge((array) $this->options['parents'], (array) $key);
    }

    /**
     * Sets a validation error
     * @param string|array $key
     * @param string $error
     * @return array
     */
    protected function setError($key, $error)
    {
        gplcart_array_set($this->errors, $this->getParents($key), $error);
        return $this->errors;
    }

    /**
     * Whether an error(s) exist
     * @param string|null $key
     * @return boolean
     */
    protected function isError($key = null)
    {
        if (!isset($key)) {
            return !empty($this->errors);
        }

        $result = $this->getError($key);
        return !empty($result);
    }

    /**
     * Returns an error
     * @param string|null $key
     * @return mixed
     */
    protected function getError($key = null)
    {
        if (!isset($key)) {
            return $this->errors;
        }

        $parents = $this->getParents($key);
        return gplcart_array_get($this->errors, $parents);
    }

    /**
     * Returns validation results
     * @return array|boolean
     */
    protected function getResult()
    {
        $result = empty($this->errors) ? true : $this->errors;
        $this->errors = array();
        return $result;
    }

    /**
     * Set "Field required" error
     * @param string $field
     * @param string $label
     * @return array
     */
    protected function setErrorRequired($field, $label)
    {
        $error = $this->language->text('@field is required', array('@field' => $label));
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
        $error = $this->language->text('@field must be numeric', array('@field' => $label));
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
        $error = $this->language->text('@field must be integer', array('@field' => $label));
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
        $error = $this->language->text('@name is unavailable', array('@name' => $label));
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
        $error = $this->language->text('@field must be @min - @max characters long', $vars);
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
        $error = $this->language->text('@field has invalid value', array('@field' => $label));
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
        $error = $this->language->text('@name already exists', array('@name' => $label));
        return $this->setError($field, $error);
    }

}
