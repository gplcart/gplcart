<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate languages
 */
class Language extends ComponentValidator
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Performs full language data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function language(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateLanguage();
        $this->validateWeight();
        $this->validateStatus();
        $this->validateDefault();
        $this->validateNameLanguage();
        $this->validateNativeNameLanguage();
        $this->validateCodeLanguage();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a language to be updated
     * @return boolean
     */
    protected function validateLanguage()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $language = $this->language->get($id);

        if (empty($language)) {
            $this->setErrorUnavailable('update', $this->language->text('Language'));
            return false;
        }

        $this->setUpdating($language);
        return true;
    }

    /**
     * Validates a language code
     * @return boolean|null
     */
    protected function validateCodeLanguage()
    {
        $field = 'code';
        $label = $this->language->text('Code');
        $code = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[A-Za-z\\-]+$/', $code) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true; // Updating, dont check own code uniqueness
        }

        $language = $this->language->get($code);

        if (!empty($language)) {
            $this->setErrorExists($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Validates a language name
     * @return boolean
     */
    protected function validateNameLanguage()
    {
        $field = 'name';
        $label = $this->language->text('Name');
        $name = $this->getSubmitted($field);

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($name) > 50) {
            $this->setErrorLengthRange($field, $label, 0, 50);
            return false;
        }

        return true;
    }

    /**
     * Validates an language native name
     * @return boolean
     */
    protected function validateNativeNameLanguage()
    {
        $field = 'native_name';
        $label = $this->language->text('Native name');
        $name = $this->getSubmitted($field);

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($name) > 50) {
            $this->setErrorLengthRange($field, $label, 0, 50);
            return false;
        }
        return true;
    }

}
