<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Language as LanguageModel;

/**
 * Provides methods to validate languages
 */
class Language extends Component
{

    /**
     * Language module class instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * @param LanguageModel $language
     */
    public function __construct(LanguageModel $language)
    {
        parent::__construct();

        $this->language = $language;
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
        $this->validateBool('status');
        $this->validateBool('default');
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

        $data = $this->language->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Language'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a language code
     * @return boolean|null
     */
    protected function validateCodeLanguage()
    {
        $field = 'code';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Code');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (preg_match('/^[A-Za-z\\-]+$/', $value) !== 1) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['code']) && $updating['code'] === $value) {
            return true;
        }

        $language = $this->language->get($value);

        if (!empty($language)) {
            $this->setErrorExists($field, $label);
            return false;
        }

        return true;
    }

    /**
     * Validates a language name
     * @return boolean|null
     */
    protected function validateNameLanguage()
    {
        $field = 'name';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($value) > 50) {
            $this->setErrorLengthRange($field, $this->translation->text('Name'), 0, 50);
            return false;
        }

        return true;
    }

    /**
     * Validates an language native name
     * @return boolean|null
     */
    protected function validateNativeNameLanguage()
    {
        $field = 'native_name';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            return true; // If not set, code will be used instead
        }

        if (mb_strlen($value) > 50) {
            $this->setErrorLengthRange($field, $this->translation->text('Native name'), 0, 50);
            return false;
        }

        return true;
    }

}
