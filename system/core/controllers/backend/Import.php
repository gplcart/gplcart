<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Import as ModelsImport;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to import operations
 */
class Import extends BackendController
{

    /**
     * Import model instance
     * @var \core\models\Import $import
     */
    protected $import;

    /**
     * Constructor
     * @param ModelsImport $import
     */
    public function __construct(ModelsImport $import)
    {
        parent::__construct();
        $this->import = $import;
    }

    /**
     * Displays the import operations overview page
     */
    public function listImport()
    {
        $job = $this->getJob();
        $this->setData('job', $job);

        $this->controlAccess('file_upload');

        $this->downloadErrorsImport();
        $this->downloadTemplateImport();

        $operations = $this->getOperationsImport();
        $this->setData('operations', $operations);

        $this->submitImport();

        $this->setTitleListImport();
        $this->setBreadcrumbListImport();
        $this->outputListImport();
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
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the import operations overview page
     */
    protected function outputListImport()
    {
        $this->output('tool/import/list');
    }

    /**
     * Downloads a CSV template file
     */
    protected function downloadTemplateImport()
    {
        $operation_id = $this->request->get('download_template');
        $operation = $this->import->getOperation($operation_id);

        if (!empty($operation['csv']['template'])) {
            $this->response->download($operation['csv']['template']);
        }
    }

    /**
     * Downloads an error log file
     */
    protected function downloadErrorsImport()
    {
        $operation_id = $this->request->get('download_errors');
        $operation = $this->import->getOperation($operation_id);

        if (!empty($operation['log']['errors'])) {
            $this->response->download($operation['log']['errors']);
        }
    }

    /**
     * Starts import
     * @param array $operation
     * @return null
     */
    protected function submitImport()
    {
        $operation_id = $this->request->post('import');

        if (empty($operation_id)) {
            return null;
        }

        $operation = $this->import->getOperation($operation_id);

        if (empty($operation)) {
            return null;
        }

        $this->validateImport($operation);
        $errors = $this->error();

        if (empty($errors)) {
            return $this->setJobImport($operation);
        }

        $this->setError($operation_id, $errors);
        return null;
    }

    /**
     * Validates submitted import data
     * @param array $operation
     * @return boolean
     */
    protected function validateImport(array $operation)
    {
        $this->setSubmitted('limit', $this->import->getLimit());
        $this->setSubmitted('delimiter', $this->import->getCsvDelimiter());
        $this->setSubmitted('operation', $operation);
        $this->validate('import');
    }

    /**
     * Sets up the import job
     * @param array $operation
     */
    protected function setJobImport(array $operation)
    {
        $submitted = $this->getSubmitted();

        $job = array(
            'data' => $submitted,
            'id' => $operation['job_id'],
            'total' => $submitted['filesize'],
            'redirect_message' => array(
                'finish' => 'Success. Inserted: %inserted, updated: %updated'
            )
        );

        if (!empty($operation['log']['errors'])) {
            $options = array('!url' => $this->url(false, array('download_errors' => $operation['id'])));
            $error = $this->text('Inserted: %inserted, updated: %updated, errors: %errors. <a href="!url">See error log</a>', $options);

            $job['redirect_message']['errors'] = $error;
        }

        $this->setJob($job);
    }

}
