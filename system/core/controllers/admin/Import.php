<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\classes\Curl;
use core\controllers\admin\Controller as BackendController;
use core\models\File as ModelsFile;
use core\models\Import as ModelsImport;
use core\models\Job as ModelsJob;

/**
 * Handles incoming requests and outputs data related to import operations
 */
class Import extends BackendController
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
    public function __construct(
        ModelsJob $job,
        ModelsImport $import,
        ModelsFile $file,
        Curl $curl
    ) {
        parent::__construct();

        $this->job = $job;
        $this->curl = $curl;
        $this->file = $file;
        $this->import = $import;
    }

    /**
     * Displays the import operations overview page
     */
    public function listImport()
    {
        $this->setDemoImport();

        $job = $this->getJob();
        $operations = $this->getOperationsImport();

        $this->setData('job', $job);
        $this->setData('operations', $operations);

        $this->setTitleListImport();
        $this->setBreadcrumbListImport();
        $this->outputListImport();
    }

    /**
     * Sets a batch job to import demo content including categories and images
     * The process will start when "demo" parameter is set in the URL
     */
    protected function setDemoImport()
    {
        if ($this->isQuery('demo') && $this->isConnectedSiteImport()) {
            $this->controlAccess('category_add');
            $this->setJobDemoImport('category');
        }

        if ($this->request->get('demo-next') === 'product') {
            $this->controlAccess('product_add');
            $this->setJobDemoImport('product');
        }
    }

    /**
     * Checks if demo site is connected
     * The site contains images to be attached to the demo products
     * @return boolean
     */
    protected function isConnectedSiteImport()
    {
        $header = $this->curl->header(GC_DEMO_URL);

        if (!empty($header['header_size'])) {
            return true;
        }

        $message = $this->text('Unable to connect to external server that provides demo images. Check your internet connection');

        $this->setMessage($message, 'warning');
        return false;
    }

    /**
     * Imports a demo content from CSV files
     * @param string $operation_id
     * @return null
     */
    protected function setJobDemoImport($operation_id)
    {
        $operation = $this->getOperationImport($operation_id);

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

            // Next step - create products
            $job['redirect']['finish'] = $this->url('', array('demo-next' => 'product'));
        }

        if ($operation_id == 'product') {

            $job['message'] = array(
                'start' => $this->text('Starting to create demo products...'),
                'process' => $this->text('Creating demo products.'
                    . ' It may take some time to download images from an external site.'),
            );

            $job['redirect_message'] = array(
                'finish' => $this->text('Finished. <a href="!href">See demo products</a>', array(
                    '!href' => $this->url('admin/content/product')
                )),
                'errors' => $this->text('An error occurred while creating demo products.'
                    . ' A possible reason might be you have duplicated category names.'),
            );
        }

        $this->job->submit($job);
    }

    /**
     * Loads an operation
     * @param string $operation_id
     * @return array
     */
    protected function getOperationImport($operation_id)
    {
        $operation = $this->import->getOperation($operation_id);

        if (empty($operation)) {
            $this->outputError(404);
        }

        return $operation;
    }

    /**
     * Returns an array of import operations
     * @return array
     */
    protected function getOperationsImport()
    {
        return $this->import->getOperations();
    }

    /**
     * Sets titles on the import operations overview page
     */
    protected function setTitleListImport()
    {
        $this->setTitle($this->text('Import'));
    }

    /**
     * Sets breadcrumbs on the import operations overview page
     */
    protected function setBreadcrumbListImport()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );


        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the import operations overview page
     */
    protected function outputListImport()
    {
        $this->output('tool/import/list');
    }

    /**
     * Displays the import form page
     * @param string $operation_id
     */
    public function editImport($operation_id)
    {
        $this->controlAccess('file_upload');

        $operation = $this->getOperationImport($operation_id);

        $this->downloadImport($operation);
        $this->submitImport($operation);

        $job = $this->getJob();

        $this->setData('job', $job);
        $this->setData('operation', $operation);

        $this->setTitleEditImport($operation);
        $this->setBreadcrumbEditImport();
        $this->outputEditImport();
    }

    /**
     * Listening to the current URL and outputs files to download if needed
     * @param array $operation
     */
    protected function downloadImport(array $operation)
    {
        if ($this->isQuery('download_template') && isset($operation['csv']['template'])) {
            $this->response->download($operation['csv']['template']);
        }

        if ($this->isQuery('download_errors') && isset($operation['log']['errors'])) {
            $this->response->download($operation['log']['errors']);
        }
    }

    /**
     * Starts import
     * @param array $operation
     */
    protected function submitImport(array $operation)
    {
        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('import');
        $this->validateImport($operation);

        if (!$this->hasErrors('import')) {
            $this->setJobImport($operation);
        }
    }

    /**
     * Validates submitted import data
     * @param array $operation
     * @return null
     */
    protected function validateImport(array $operation)
    {
        $limit = $this->import->getLimit();

        $this->setSubmitted('limit', $limit);
        $this->setSubmitted('operation', $operation);

        $this->addValidator('file', array(
            'upload' => array(
                'handler' => 'csv',
                'path' => 'private/import',
                'file' => $this->request->file('file')
            )
        ));

        $errors = $this->setValidators($operation);

        if (empty($errors)) {

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

    protected function setJobImport(array $operation)
    {
        $submitted = $this->getSubmitted();

        $job = array(
            'data' => $submitted,
            'id' => $operation['job_id'],
            'total' => $submitted['filesize'],
            'redirect_message' => array(
                'finish' => 'Data has been successfully imported. Inserted: %inserted, updated: %updated'
            )
        );

        if (!empty($operation['log']['errors'])) {

            $error_message = $this->text('Inserted: %inserted, updated: %updated,'
                . ' errors: %errors. <a href="!url">See error log</a>', array(
                '!url' => $this->url(false, array('download_errors' => 1))
            ));

            $job['redirect_message']['errors'] = $error_message;
        }

        $this->job->submit($job);
    }

    /**
     * Sets titles on the import form page
     * @param array $operation
     */
    protected function setTitleEditImport(array $operation)
    {
        $text = $this->text('Import %operation', array(
            '%operation' => $operation['name']
        ));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the import form page
     */
    protected function setBreadcrumbEditImport()
    {
        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Import'),
            'url' => $this->url('admin/tool/import')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the import page templates
     */
    protected function outputEditImport()
    {
        $this->output('tool/import/edit');
    }

}
