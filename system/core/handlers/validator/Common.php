<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Language as ModelsLanguage;

/**
 * Provides methods to validate various data
 */
class Common
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     */
    public function __construct(ModelsLanguage $language)
    {
        $this->language = $language;
    }

    /**
     * Validates length of a string
     * @param string $subject
     * @param array $options
     * @return boolean
     */
    public function length($subject, array $options = array())
    {
        if (!isset($subject) && empty($options['required'])) {
            return true;
        }

        $length = mb_strlen($subject);
        $min = isset($options['min']) ? (int) $options['min'] : null;
        $max = isset($options['max']) ? (int) $options['max'] : null;

        if (isset($min) && !isset($max)) {
            if ($length < $min) {
                return $this->language->text('Content must not be less than %s characters', array('%s' => $min));
            }

            return true;
        }

        if (isset($max) && !isset($min)) {
            if ($length > $max) {
                return $this->language->text('Content must not exceed %s characters', array('%s' => $max));
            }

            return true;
        }

        if (isset($max) && isset($min)) {
            if ($length > $max || $length < $min) {
                return $this->language->text('Content must be %min - %max characters long', array(
                            '%min' => $min, '%max' => $max));
            }

            return true;
        }

        return false;
    }

    /**
     * Validates a numeric value
     * @param string|integer|float $subject
     * @param array $options
     * @return boolean|string
     */
    public function numeric($subject, array $options = array())
    {
        if (!isset($subject) && empty($options['required'])) {
            return true;
        }

        if (is_numeric($subject)) {
            return true;
        }

        return $this->language->text('Only numeric values allowed');
    }

    /**
     * Validates an E-mail
     * @param string $subject
     * @param array $options
     * @return boolean
     */
    public function email($subject, array $options = array())
    {
        if ((!isset($subject) || $subject === '') && empty($options['required'])) {
            return true;
        }

        if (filter_var($subject, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return $this->language->text('Invalid E-mail');
    }

    /**
     * 
     * @param array|null $subject
     * @param array $options
     * @return type
     */
    public function translation($subject, array $options = array())
    {
        if (empty($subject) && empty($options['required'])) {
            return true;
        }

        $fields = array('title', 'meta_title', 'meta_description');

        $errors = array();
        foreach ($subject as $lang => $fields) {
            foreach ($fields as $name => $value) {

                if (!in_array($name, $fields)) {
                    continue;
                }

                $result = $this->length($value, array('max' => 255));

                if ($result !== true) {
                    $errors[$lang][$name] = $result;
                }
            }
        }

        return empty($errors) ? true : $errors;
    }

    /**
     * Validates a value using a regexp
     * @param type $subject
     * @param array $options
     * @return boolean
     */
    public function regexp($subject, array $options = array())
    {

        if ((!isset($subject) || $subject === '') && empty($options['required'])) {
            return true;
        }

        if (empty($options['pattern'])) {
            return false;
        }

        if (preg_match($options['pattern'], $subject)) {
            return true;
        }

        return $this->language->text('Invalid format');
    }

    /**
     * Validates a date
     * @param type $subject
     * @param array $options
     * @return boolean
     */
    public function date($subject, array $options = array())
    {
        if ((!isset($subject) || $subject === '') && empty($options['required'])) {
            return true;
        }

        $timestamp = strtotime($subject);

        if (empty($timestamp)) {
            return $this->text('Date is not valid English textual datetime');
        }

        return array('result' => $timestamp);
    }

    /**
     * Validates images
     * @param array|null $subject
     * @param array $options
     */
    public function images($subject, array $options = array())
    {

        if (empty($subject) && empty($options['required'])) {
            return true;
        }

        $title = $options['submitted']['title'];

        foreach ($subject as &$image) {

            if (empty($image['title'])) {
                $image['title'] = $title;
            }

            if (empty($image['description'])) {
                $image['description'] = $title;
            }

            $image['title'] = mb_strimwidth($image['title'], 0, 255, '');

            if (empty($image['translation'])) {
                continue;
            }

            foreach ($image['translation'] as &$translation) {
                $translation['title'] = mb_strimwidth($translation['title'], 0, 255, '');
            }
        }

        return array('result' => $subject);
    }

}
