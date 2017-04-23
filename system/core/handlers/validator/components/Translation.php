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
 * Provides methods to validate translations
 */
class Translation extends ComponentValidator
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validates a uploaded translation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function upload(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateLanguageTranslation();
        $this->validateUploadTranslation();

        return $this->getResult();
    }

    /**
     * Validates translation language code
     */
    protected function validateLanguageTranslation()
    {
        $field = 'language';
        $label = $this->language->text('Language');
        $code = $this->getSubmitted($field);

        if (empty($code)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $language = $this->language->get($code);

        if (empty($language)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }
        return true;
    }

    /**
     * Uploads and validates a translation
     * @return boolean
     */
    protected function validateUploadTranslation()
    {
        if ($this->isError()) {
            return null;
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $this->setErrorRequired('file', $this->language->text('File'));
            return false;
        }

        $code = $this->getSubmitted('language');

        $result = $this->file->upload($file, 'csv', GC_LOCALE_DIR . "/$code");

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getTransferred();
        $this->setSubmitted('destination', $uploaded);
        return true;
    }

}
