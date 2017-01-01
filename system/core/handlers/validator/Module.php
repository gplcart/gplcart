<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\File as FileModel;
use gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate modules
 */
class Module extends BaseValidator
{

    /**
     * Path for uploaded module files that is relative to main file directory
     */
    const UPLOAD_PATH = 'private/modules';

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

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
     * Performs module upload validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function upload(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateUploadModule($options);
        return $this->getResult();
    }

    /**
     * Uploads and validates a module
     * @param array $options
     * @return boolean
     */
    protected function validateUploadModule(array $options)
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            $vars = array('@field' => $this->language->text('File'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('file', $error, $options);
            return false;
        }

        $result = $this->file->setUploadPath(self::UPLOAD_PATH)
                ->setHandler('zip')
                ->upload($file);

        if ($result !== true) {
            $this->setError('file', $result, $options);
            return false;
        }

        $uploaded = $this->file->getUploadedFile();
        $this->setSubmitted('destination', $uploaded, $options);
        return true;
    }

}
