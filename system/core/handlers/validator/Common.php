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
     * @param string $string
     * @param array $options
     * @return boolean
     */
    public function length($string, array $options = array())
    {

        $length = mb_strlen($string);
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
                            '%min' => 1, '%max' => 255));
            }

            return true;
        }

        return false;
    }

    /**
     * Validates an E-mail
     * @param string $string
     * @param array $options
     * @return boolean
     */
    public function email($string, array $options = array())
    {
        if (filter_var($string, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        
        return $this->language->text('Invalid E-mail');
    }
    
    

}
