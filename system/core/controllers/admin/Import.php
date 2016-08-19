<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Curl;
use core\models\Job as ModelsJob;
use core\models\File as ModelsFile;
use core\models\Import as ModelsImport;

/**
 * Handles incoming requests and outputs data related to import operations
 */
class Import extends Controller
{

    /**
     * Curl class instance
     * @var \core\classes\Curl $curl
     */
    protected $curl;

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Job model instance
     * @var \core\models\Job $job
     */
    protected $job;

    /**
     * File model instance
     * @var \core\models\File $file
     */
    protected $file;

    /**
     * Constructor
     * @param ModelsJob $job
     * @param ModelsImport $import
     * @param ModelsFile $file
     * @param Curl $curl
     */
    public function __construct(ModelsJob $job, ModelsImport $import,
            ModelsFile $file, Curl $curl)
    {
        parent::__construct();

        $this->job = $job;
        $this->curl = $curl;
        $this->file = $file;
        $this->import = $import;
    }

    /**
     * Displays the import operations overview page
     */
    public function operations()
    {
        $this->setImportDemo();

        $job = $this->getJob();
        $operations = $this->import->getOperations();

        $this->setData('job', $job);
        $this->setData('operations', $operations);

        $this->setTitleOperations();
        $this->setBreadcrumbOperations();
        $this->outputOperations();
    }

    /**
     * Checks the current URL and access and imports a demo content if needed
     */
    protected function setImportDemo()
    {
        if ($this->request->get('demo') && $this->isConnected()) {
            $this->controlAccess('category_add');
            $this->demo('category');
        }

        if ($this->request->get('demo-next') === 'product') {
            $this->controlAccess('product_add');
            $this->demo('product');
        }
    }

    /**
     * Checks if demo site is connected and sets a warning message if not
     * @return boolean
     */
    protected function isConnected()
    {
        $header = $this->curl->header(GC_DEMO_URL);

        if (empty($header['header_size'])) {
            $this->setMessage('Unable to connect to external server that provides demo images. Check your internet connection', 'warning');
            return false;
        }

        return true;
    }

    /**
     * Imports a demo content from CSV files
     * @param string $operation_id
     * @return null
     */
    protected function demo($operation_id)
    {
        $operation = $this->import->getOperation($operation_id);

        $data = array(
            'limit' => 1,
            'operation' => $operation,
            'filepath' => $operation['csv']['template'],
            'filesize' => filesize($operation['csv']['template'])
        );

        $job = array(
            'data' => $data,
            'id' => $operation['job_id'],
            'total' => $data['filesize'],
        );

        if ($operation_id == 'category') {

            $job['message'] = array(
                'start' => $this->text('Starting to create product categories'),
                'process' => $this->text('Creating categories...')
            );

            // Go to create products
            $job['redirect']['finish'] = $this->url('', array('demo-next' => 'product'));
        }

        if ($operation_id == 'product') {

            $job['message'] = array(
                'start' => $this->text('Starting to create demo products...'),
                'process' => $this->text('Creating demo products. It may take some time to download images from an external site.'),
            );

            $job['redirect_message'] = array(
                'finish' => $this->text('Finished. <a href="!href">See demo products</a>', array(
                    '!href' => $this->url('admin/content/product'))),
                'errors' => $this->text('An error occurred while creating demo products. A possible reason might be you have duplicated category names.'),
            );
        }

        $this->job->submit($job);
    }

    /**
     * Displays the import form page
     * @param string $operation_id
     */
    public function import($operation_id)
    {
        $this->controlAccess('file_upload');

        $operation = $this->getOperation($operation_id);

        if ($this->request->get('download_template') && isset($operation['csv']['template'])) {
            $this->response->download($operation['csv']['template']);
        }

        if ($this->request->get('download_errors') && isset($operation['log']['errors'])) {
            $this->response->download($operation['log']['errors']);
        }

        if ($this->isPosted('import')) {
            $this->submit($operation);
        }

        $job = $this->getJob();

        $this->setData('job', $job);
        $this->setData('operation', $operation);

        $this->setTitleImport($operation);
        $this->setBreadcrumbImport();
        $this->outputImport();
    }

    /**
     * Sets titles on the import operations overview page
     */
    protected function setTitleOperations()
    {
        $this->setTitle($this->text('Import'));
    }

    /**
     * Sets breadcrumbs on the import operations overview page
     */
    protected function setBreadcrumbOperations()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the import operations overview page
     */
    protected function outputOperations()
    {
        $this->output('tool/import/list');
    }

    /**
     * Sets titles on the import form page
     * @param array $operation
     */
    protected function setTitleImport(array $operation)
    {
        $this->setTitle($this->text('Import %operation', array(
                    '%operation' => $operation['name'])));
    }

    /**
     * Sets breadcrumbs on the import form page
     */
    protected function setBreadcrumbImport()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));

        $this->setBreadcrumb(array(
            'text' => $this->text('Import'),
            'url' => $this->url('admin/tool/import')));
    }

    /**
     * Renders the import page templates
     */
    protected function outputImport()
    {
        $this->output('tool/import/edit');
    }

    /**
     * Loads an operation
     * @param string $operation_id
     * @return array
     */
    protected function getOperation($operation_id)
    {
        $operation = $this->import->getOperation($operation_id);

        if (empty($operation)) {
            $this->outputError(404);
        }

        return $operation;
    }

    /**
     * Starts import
     * @param array $operation
     * @return null
     */
    protected function submit(array $operation)
    {
        $this->setSubmitted();
        $this->validate($operation);

        if ($this->hasErrors()) {
            return;
        }

        $submitted = $this->getSubmitted();

        $job = array(
            'data' => $submitted,
            'id' => $operation['job_id'],
            'total' => $submitted['filesize'],
            'redirect_message' => array(
                'finish' => 'Data has been successfully imported. Inserted: %inserted, updated: %updated'
            ),
        );

        if (!empty($operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $this->text('Inserted: %inserted, updated: %updated, errors: %errors. <a href="!url">See error log</a>', array(
                '!url' => $this->url(false, array('download_errors' => 1))));
        }

        $this->job->submit($job);
    }

    /**
     * Validates submitted import data
     * @param array $operation
     * @return null
     */
    protected function validate(array $operation)
    {
        $this->setSubmitted('operation', $operation);
        $this->setSubmitted('limit', $this->import->getLimit());

        $this->addValidator('file', array(
            'upload' => array(
                'path' => 'private/import',
                'handler' => 'csv',
                'file' => $this->request->file('file')
        )));

        $errors = $this->setValidators($operation);

        if (!empty($errors)) {
            return;
        }

        $uploaded = $this->getValidatorResult('file');
        $filepath = GC_FILE_DIR . "/$uploaded";
        $filesize = filesize($filepath);

        $this->setSubmitted('filepath', $filepath);
        $this->setSubmitted('filesize', $filesize);

        $result = $this->import->validateCsvHeader($filepath, $operation);

        if ($result !== true) {
            $this->setError('file', $result);
        }
    }

}
