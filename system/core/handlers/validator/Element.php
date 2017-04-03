<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Methods to validate single elements
 */
class Element extends BaseValidator
{
    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }
    
    /**
     * 
     * @param type $field
     * @param type $label
     * @return boolean
     */
    public function validateNotEmptyElement($field, $label)
    {
        if ($this->getSubmitted($field)) {
            return true;
        }
        
        $error = $this->language->text('@field is required', array('@field' => $label));
        $this->setError($field, $error);
        return false;
    }

    /**
     * 
     * @param type $field
     * @param type $label
     * @return boolean
     */
    public function validateNumericElement($field, $label, $value = null)
    {
        if(!isset($value)){
            $value = $this->getSubmitted($field);
        }
        
        if (is_numeric($value)) {
            return true;
        }

        $error = $this->language->text('@field must be numeric', array('@field' => $label));
        $this->setError($field, $error);
        return false;
    }
    
    /**
     * 
     * @param type $field
     * @param type $label
     * @param type $max
     * @return boolean
     */
    public function validateMaxLengthElement($field, $label, $max)
    {
        if (mb_strlen($this->getSubmitted($field)) > $max) {
            $vars = array('@max' => $max, '@field' => $label);
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError($field, $error);
            return false;
        }
        return true;
    }

}
