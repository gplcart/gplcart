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
 * Provides methods to validate modules
 */
class Module extends ComponentValidator
{

    /**
     * Path for uploaded module files that is relative to main file directory
     */
    const UPLOAD_PATH = 'private/modules';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Performs module upload validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function upload(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateUploadModule();
        return $this->getResult();
    }

    /**
     * Uploads and validates a module
     * @return boolean
     */
    protected function validateUploadModule()
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            $this->setErrorRequired('file', $this->language->text('File'));
            return false;
        }

        $result = $this->file->upload($file, 'zip', self::UPLOAD_PATH);

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getUploadedFile();
        $this->setSubmitted('destination', $uploaded);
        return true;
    }

}
