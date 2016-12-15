<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\File as FileModel;
use core\helpers\Request as RequestHelper;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate settings
 */
class Settings extends BaseValidator
{

    /**
     * Request class instance
     * @var \core\helpers\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param FileModel $file
     * @param RequestHelper $request
     */
    public function __construct(FileModel $file, RequestHelper $request)
    {
        parent::__construct();

        $this->file = $file;
        $this->request = $request;
    }

    /**
     * Performs full settings data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function settings(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateSettings($options);
        $this->validateEmailSettings($options);
        $this->validateFileSettings($options);

        return $this->getResult();
    }

    /**
     * Validates and prepares settings
     * @param array $options
     * @return boolean
     */
    protected function validateSettings(array $options)
    {
        $cron_key = $this->getSubmitted('cron_key', $options);
        $smtp_host = $this->getSubmitted('smtp_host', $options);
        $smtp_auth = $this->getSubmitted('smtp_auth', $options);

        if (!empty($smtp_host)) {
            $smtp_host = gplcart_string_array($smtp_host);
            $this->setSubmitted('smtp_host', $smtp_host, $options);
        }

        if (empty($cron_key)) {
            $cron_key = gplcart_string_random();
            $this->setSubmitted('cron_key', $cron_key, $options);
        }

        if (isset($smtp_auth)) {
            $smtp_auth = gplcart_string_bool($smtp_auth);
            $this->setSubmitted('smtp_auth', $smtp_auth, $options);
        }

        return true;
    }

    /**
     * Validates E-mails
     * @param array $options
     * @return boolean|null
     */
    protected function validateEmailSettings(array $options)
    {
        $value = $this->getSubmitted('gapi_email', $options);

        if (empty($value)) {
            return null;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $error = $this->language->text('Invalid E-mail');
            $this->setError('gapi_email', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates uploaded files
     * @param array $options
     * @return boolean|null
     */
    protected function validateFileSettings(array $options)
    {
        if ($this->isError()) {
            return null;
        }

        $file = $this->request->file('gapi_certificate');

        if (empty($file)) {
            return true;
        }

        $result = $this->file->upload($file);

        if ($result !== true) {
            $this->setError('gapi_certificate', $result, $options);
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $this->setSubmitted('gapi_certificate', $uploaded, $options);
        return true;
    }

}
