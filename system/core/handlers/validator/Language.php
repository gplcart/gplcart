<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

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

        // Remove data of updating language
        // to prevent from saving in serialized string
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
     * @return boolean|null
     */
    protected function validateCodeLanguage()
    {
        $code = $this->getSubmitted('code');

        if ($this->isUpdating() && !isset($code)) {
            return null;
        }

        if (empty($code)) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('code', $error);
            return false;
        }

        if (preg_match('/^[A-Za-z-_]{1,10}$/', $code) !== 1) {
            $vars = array('@field' => $this->language->text('Code'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('code', $error);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $code) {
            return true; // Updating, dont check own code uniqueness
        }

        $language = $this->language->get($code);

        if (!empty($language)) {
            $vars = array('@name' => $this->language->text('Code'));
            $error = $this->language->text('@name already exists', $vars);
            $this->setError('code', $error);
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
        $name = $this->getSubmitted('name');

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (preg_match('/^[A-Za-z]{1,50}$/', $name) !== 1) {
            $vars = array('@field' => $this->language->text('Name'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('name', $error);
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
        $name = $this->getSubmitted('native_name');

        if (!isset($name)) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($name) > 50) {
            $vars = array('@max' => 50, '@field' => $this->language->text('Native name'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('native_name', $error);
            return false;
        }

        return true;
    }

}
