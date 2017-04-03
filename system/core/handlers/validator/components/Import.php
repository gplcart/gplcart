<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\helpers\Csv as CsvHelper,
    gplcart\core\helpers\Request as RequestHelper;
use gplcart\core\models\File as FileModel,
    gplcart\core\models\Import as ImportModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate import data
 */
class Import extends ComponentValidator
{

    /**
     * Path for uploaded field value files that is relative to main file directory
     */
    const UPLOAD_PATH = 'private/import';

    /**
     * CSV class instance
     * @var \gplcart\core\helpers\Csv $csv
     */
    protected $csv;

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request
     */
    protected $request;

    /**
     * Import model instance
     * @var \gplcart\core\models\Import $import
     */
    protected $import;

    /**
     * File model instance
     * @var \gplcart\core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param CsvHelper $csv
     * @param RequestHelper $request
     * @param ImportModel $import
     * @param FileModel $file
     */
    public function __construct(CsvHelper $csv, RequestHelper $request,
            ImportModel $import, FileModel $file)
    {
        parent::__construct();

        $this->csv = $csv;
        $this->file = $file;
        $this->import = $import;
        $this->request = $request;
    }

    /**
     * Performs full import data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function import(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateOperationImport();
        $this->validateFilePathImport();
        $this->validateFileUploadImport();
        $this->validateCsvHeaderImport();

        return $this->getResult();
    }

    /**
     * Validates an operation
     * @return boolean
     */
    protected function validateOperationImport()
    {
        $operation_id = $this->getSubmitted('operation_id');

        if (isset($operation_id)) {
            $operation = $this->import->getOperation($operation_id);
            $this->setSubmitted('operation', $operation);
        }

        $operation = $this->getSubmitted('operation');

        if (empty($operation)) {
            $vars = array('@name' => $this->language->text('Operation'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('operation', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a relative file path
     * @return boolean|null
     */
    protected function validateFilePathImport()
    {
        $path = $this->getSubmitted('path');

        if (!isset($path)) {
            return null; // The file probably will be uploaded via UI, stop here
        }

        $filepath = GC_FILE_DIR . "/$path";

        if (is_readable($filepath)) {
            $this->setSubmitted('filepath', $filepath);
            $this->setSubmitted('filesize', filesize($filepath));
            return true;
        }

        $vars = array('@name' => $this->language->text('File'));
        $error = $this->language->text('@name is unavailable', $vars);
        $this->setError('file', $error);
        return false;
    }

    /**
     * Validates a uploaded file
     * @return boolean|null
     */
    protected function validateFileUploadImport()
    {
        $filepath = $this->getSubmitted('filepath');

        if (isset($filepath)) {
            return null; // Filepath already defined by a relative path, exit
        }

        $file = $this->request->file('file');

        if (empty($file)) {
            $vars = array('@field' => $this->language->text('File'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('file', $error);
            return false;
        }

        $result = $this->file->upload($file, 'csv', self::UPLOAD_PATH);

        if ($result !== true) {
            $this->setError('file', (string) $result);
            return false;
        }

        $uploaded = $this->file->getUploadedFile();
        $this->setSubmitted('filepath', $uploaded);
        $this->setSubmitted('filesize', filesize($uploaded));
        return true;
    }

    /**
     * Validates CSV header
     * @return boolean|null
     */
    public function validateCsvHeaderImport()
    {
        if ($this->isError()) {
            return null; // Abort on existing errors
        }

        $operation = $this->getSubmitted('operation');
        $header = $operation['csv']['header'];
        $delimiter = $this->import->getCsvDelimiter();
        $filepath = $this->getSubmitted('filepath');

        $real_header = $this->csv->setFile($filepath)
                ->setHeader($header)
                ->setDelimiter($delimiter)
                ->getHeader();

        $header_id = reset($header);
        $real_header_id = reset($real_header);

        if ($header_id !== $real_header_id || array_diff($header, $real_header)) {
            $vars = array('@format' => implode(' | ', $header));
            $error = $this->language->text('Wrong header. Required columns: @format', $vars);
            $this->setError('file', $error);
            return false;
        }

        return true;
    }

}
