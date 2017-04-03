<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\File as FileModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate settings
 */
class Settings extends ComponentValidator
{

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateSettings();
        return $this->getResult();
    }

    /**
     * Validates and prepares settings
     * @return boolean
     */
    protected function validateSettings()
    {
        $cron_key = $this->getSubmitted('cron_key');
        $smtp_host = $this->getSubmitted('smtp_host');
        $smtp_auth = $this->getSubmitted('smtp_auth');

        if (!empty($smtp_host)) {
            $smtp_host = gplcart_string_array($smtp_host);
            $this->setSubmitted('smtp_host', $smtp_host);
        }

        if (empty($cron_key)) {
            $cron_key = gplcart_string_random();
            $this->setSubmitted('cron_key', $cron_key);
        }

        if (isset($smtp_auth)) {
            $smtp_auth = gplcart_string_bool($smtp_auth);
            $this->setSubmitted('smtp_auth', $smtp_auth);
        }

        return true;
    }

}
