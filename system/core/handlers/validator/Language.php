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
 * Provides methods to validate languages
 */
class Language extends BaseValidator
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
        $this->submitted = &$submitted;

        $this->validateLanguage($options);
        $this->validateWeight($options);
        $this->validateStatus($options);
        $this->validateDefault($options);
        $this->validateNameLanguage($options);
        $this->validateNativeNameLanguage($options);
        $this->validateCodeLanguage($options);

        return $this->getResult();
    }

    /**
     * Validates a language to be updated
     * @param array $options
     * @return boolean
     */
    protected function validateLanguage(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $language = $this->language->get($id);

        if (empty($language)) {
            $vars = array('@name' => $this->language->text('Language'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($language);
        return true;
    }

    /**
     * Validates a language code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCodeLanguage(array $options)
    {
        $code = $this->getSubmitted('code', $options);

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        if (preg_match('/^[A-Za-z-_]{1,10}$/', $code) !== 1) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true; // Updating, dont check own code uniqueness
        }

        $language = $this->language->get($code);

        if (!empty($language)) {
            $vars = array('@object' => $this->language->text('Code'));
            $error = $this->language->text('@object already exists', $vars);
            $this->setError('code', $error, $options);
            return false;
        }

        // Remove data of updating language
        // to prevent from saving in the serialized string
        $this->unsetSubmitted('update');
        return true;
    }

    /**
     * Validates a language name
     * @param array $options
     * @return boolean
     */
    protected function validateNameLanguage(array $options)
    {
        $name = $this->getSubmitted('name', $options);

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (preg_match('/^[A-Za-z]{1,50}$/', $name) !== 1) {
            $vars = array('@field' => $this->language->text('Name'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('name', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an language native name
     * @param array $options
     * @return boolean
     */
    protected function validateNativeNameLanguage(array $options)
    {
        $name = $this->getSubmitted('native_name', $options);

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($name) > 50) {
            $vars = array('@max' => 50, '@field' => $this->language->text('Native name'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('native_name', $error, $options);
            return false;
        }

        return true;
    }

}
