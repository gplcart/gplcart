<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\File as FileModel;
use core\helpers\String as StringHelper;
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
     */
    public function settings(array &$submitted, array $options = array())
    {
        $this->validateSettings($submitted);
        $this->validateEmailSettings($submitted);
        $this->validateFileSettings($submitted);

        return $this->getResult();
    }

    /**
     * Validates and prepares settings
     * @param array $submitted
     * @return boolean
     */
    protected function validateSettings(array &$submitted)
    {
        if (!empty($submitted['smtp_host'])) {
            $submitted['smtp_host'] = StringHelper::toArray($submitted['smtp_host']);
        }

        if (empty($submitted['cron_key'])) {
            $submitted['cron_key'] = StringHelper::random();
        }

        if (isset($submitted['smtp_auth'])) {
            $submitted['smtp_auth'] = StringHelper::toBool($submitted['smtp_auth']);
        }

        return true;
    }

    /**
     * Validates E-mails
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailSettings(array &$submitted)
    {
        if (empty($submitted['gapi_email'])) {
            return null;
        }

        if (!filter_var($submitted['gapi_email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['gapi_email'] = $this->language->text('Invalid E-mail');
            return false;
        }

        return true;
    }

    /**
     * Validates uploaded files
     * @param array $submitted
     * @return boolean
     */
    protected function validateFileSettings(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        $file = $this->request->file('gapi_certificate');

        if (empty($file)) {
            return true;
        }

        $result = $this->file->upload($file);

        if ($result !== true) {
            $this->errors['gapi_certificate'] = $result;
            return false;
        }

        $uploaded = $this->file->getUploadedFile(true);
        $submitted['gapi_certificate'] = $uploaded;
        return true;
    }

}
