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
 * Provides methods to validate modules
 */
class Module extends BaseValidator
{

    /**
     * File model instance
     * @var core\models\File $file
     */
    protected $file;

    /**
     * Request class instance
     * @var \core\helpers\Request $request
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
     */
    public function upload(array &$submitted, array $options = array())
    {
        $this->validateUploadModule($submitted);
        return $this->getResult();
    }

    /**
     * Uploads and validates a module
     * @param array $submitted
     * @return boolean
     */
    protected function validateUploadModule(array &$submitted)
    {
        $file = $this->request->file('file');

        if (empty($file)) {
            $this->errors['file'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('File')
            ));
            return false;
        }

        $result = $this->file->setUploadPath('private/modules')
                ->setHandler('zip')
                ->upload($file);

        if ($result !== true) {
            $this->errors['file'] = $result;
            return false;
        }

        $submitted['destination'] = $this->file->getUploadedFile();
        return true;
    }

}
