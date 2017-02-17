<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Import as ImportModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to import operations
 */
class Import extends BackendController
{

    /**
     * Import model instance
     * @var \gplcart\core\models\Import $import
     */
    protected $import;

    /**
     * Constructor
     * @param ImportModel $import
     */
    public function __construct(ImportModel $import)
    {
        parent::__construct();
        $this->import = $import;
    }

    /**
     * Displays the import operations overview page
     */
    public function listImport()
    {
        $this->setJob();

        $this->downloadErrorsImport();
        $this->downloadTemplateImport();

        $this->setData('operations', $this->getOperationsImport());

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

        if ($this->validateImport($operation)) {
            $this->setJobImport($operation);
        } else {
            $this->setError($operation_id, $this->error());
        }
    }

    /**
     * Validates submitted import data
     * @param array $operation
     * @return boolean
     */
    protected function validateImport(array $operation)
    {
        $this->setSubmitted('operation', $operation);
        $this->setSubmitted('limit', $this->import->getLimit());
        $this->setSubmitted('delimiter', $this->import->getCsvDelimiter());

        $errors = $this->validate('import');
        return empty($errors);
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
            $options = array('!url' => $this->url('', array('download_errors' => $operation['id'])));
            $error = $this->text('Inserted: %inserted, updated: %updated, errors: %errors. <a href="!url">See error log</a>', $options);

            $job['redirect_message']['errors'] = $error;
        }

        $this->job->submit($job);
    }

}
