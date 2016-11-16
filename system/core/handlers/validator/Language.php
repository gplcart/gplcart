<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\handlers\validator\Base as BaseValidator;

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
     */
    public function language(array &$submitted, array $options = array())
    {
        $this->validateLanguage($submitted);
        $this->validateWeight($submitted);
        $this->validateStatus($submitted);
        $this->validateDefault($submitted);
        $this->validateNameLanguage($submitted);
        $this->validateNativeNameLanguage($submitted);
        $this->validateCodeLanguage($submitted);

        return $this->getResult();
    }

    /**
     * Validates a language to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateLanguage(array &$submitted)
    {
        if (!empty($submitted['update']) && is_string($submitted['update'])) {
            $language = $this->language->get($submitted['update']);
            if (empty($language)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Language')));
                return false;
            }

            $submitted['update'] = $language;
        }

        return true;
    }

    /**
     * Validates a language code
     * @param array $submitted
     * @return boolean
     */
    protected function validateCodeLanguage(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['code'])) {
            return null;
        }

        if (empty($submitted['code'])) {
            $this->errors['code'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Code')
            ));
            return false;
        }

        if (!preg_match('/^[A-Za-z-_]{1,10}$/', $submitted['code'])) {
            $this->errors['code'] = $this->language->text('Invalid language code. It must conform to ISO 639-1 standard');
            return false;
        }

        if (isset($submitted['update']['code'])//
                && $submitted['update']['code'] === $submitted['code']) {
            return true; // Updating, dont check own code uniqueness
        }

        $language = $this->language->get($submitted['code']);

        if (!empty($language)) {
            $this->errors['code'] = $this->language->text('@object already exists', array(
                '@object' => $this->language->text('Code')));
            return false;
        }

        // Remove data of updating language
        // to prevent from saving in the serialized string
        unset($submitted['update']);
        return true;
    }

    /**
     * Validates an language name
     * @param array $submitted
     * @return boolean
     */
    protected function validateNameLanguage(array &$submitted)
    {
        if (!isset($submitted['name'])) {
            return true; // If not set, code will be used instead
        }

        if (!preg_match('/^[A-Za-z]{1,50}$/', $submitted['name'])) {
            $this->errors['name'] = $this->language->text('Invalid language name. It must be in English and 1 - 50 characters long');
            return false;
        }

        return true;
    }

    /**
     * Validates an language native name
     * @param array $submitted
     * @return boolean
     */
    protected function validateNativeNameLanguage(array &$submitted)
    {
        if (!isset($submitted['native_name'])) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($submitted['native_name']) > 50) {
            $this->errors['native_name'] = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 50,
                '@field' => $this->language->text('Native name')
            ));

            return false;
        }

        return true;
    }

}
