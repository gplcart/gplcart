<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\Handler;

/**
 * Base validator class
 */
class Base extends Handler
{

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

        $path = $this->getKeyPath($key);

        if (isset($path)) {
            return gplcart_array_get($this->submitted, $path);
        }

        return $this->submitted;
    }

    /**
     * Sets a value to an array of submitted values
     * @param string $key
     * @param mixed $value
     */
    public function setSubmitted($key, $value)
    {
        gplcart_array_set($this->submitted, $this->getKeyPath($key), $value);
    }

    /**
     * Removes a value from an array of submitted values
     * @param string $key
     */
    public function unsetSubmitted($key)
    {
        gplcart_array_unset($this->submitted, $this->getKeyPath($key));
    }

    /**
     * Whether the currently submitted entity is updating
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
     * Returns an array that represents a path to a nested array value
     * @param string|array $key
     * @return array
     */
    protected function getKeyPath($key)
    {
        if (empty($this->options['parents'])) {
            return $key;
        }

        if (is_string($this->options['parents'])) {
            $this->options['parents'] = explode('.', $this->options['parents']);
        }

        if (is_string($key)) {
            $key = explode('.', $key);
        }

        return array_merge((array)$this->options['parents'], (array)$key);
    }

    /**
     * Returns a normalized array key with the current parents prepended
     * @param string $key
     * @return string
     */
    protected function getKey($key)
    {
        return trim(implode('.', (array)$this->getKeyPath($key)), '.');
    }

    /**
     * Whether the field should be excluded from validation
     * @param string $field
     * @return bool
     */
    protected function isExcluded($field)
    {
        return isset($this->options['field']) && $this->options['field'] !== $this->getKey($field);
    }

    /**
     * Sets a validation error
     * @param string|array $key
     * @param string $error
     * @return array
     */
    protected function setError($key, $error)
    {
        gplcart_array_set($this->errors, $this->getKeyPath($key), $error);
        return $this->errors;
    }

    /**
     * Whether an error exists
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
     * Returns one or all errors
     * @param string|null $key
     * @return mixed
     */
    protected function getError($key = null)
    {
        if (!isset($key)) {
            return $this->errors;
        }

        return gplcart_array_get($this->errors, $this->getKeyPath($key));
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

}
