<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\helpers\Tool;
use core\models\Export as ModelsExport;
use core\models\Product as ModelsProduct;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to export operations
 */
class Export extends BackendController
{

    /**
     * Export model instance
     * @var \core\models\Export $export
     */
    protected $export;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ModelsExport $export
     * @param ModelsProduct $product
     */
    public function __construct(ModelsExport $export, ModelsProduct $product)
    {
        parent::__construct();

        $this->export = $export;
        $this->product = $product;
    }

    /**
     * Displays the export operations overview page
     */
    public function listExport()
    {
        $operations = $this->getOperationsExport();
        $this->setData('operations', $operations);

        $this->setTitleListExport();
        $this->setBreadcrumbListExport();
        $this->outputListExport();
    }

    /**
     * Returns an array of export operations
     * @return array
     */
    protected function getOperationsExport()
    {
        return $this->export->getOperations();
    }

    /**
     * Sets titles on the operations overview page
     */
    protected function setTitleListExport()
    {
        $this->setTitle($this->text('Export'));
    }

    /**
     * Sets breadcrumbs on the operations overview page
     */
    protected function setBreadcrumbListExport()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the operations overview page
     */
    protected function outputListExport()
    {
        $this->output('tool/export/list');
    }

    /**
     * Displays the csv export page
     * @param string $operation_id
     */
    public function editExport($operation_id)
    {
        $operation = $this->getExport($operation_id);

        $this->downloadExport($operation);
        $this->submitExport($operation);

        $job = $this->getJob();
        $stores = $this->store->getNames();

        $this->setData('job', $job);
        $this->setData('stores', $stores);

        $this->setTitleEditExport($operation);
        $this->setBreadcrumbEditExport();
        $this->outputEditExport();
    }

    /**
     * Returns an array of operation data
     * @param string $operation_id
     * @return array
     */
    protected function getExport($operation_id)
    {
        $operation = $this->export->getOperation($operation_id);

        if (empty($operation)) {
            $this->outputError(404);
        }

        return $operation;
    }

    /**
     * Outputs export file to download
     * @param array $operation
     */
    protected function downloadExport(array $operation)
    {
        $start = $this->isQuery('download')//
                && !empty($operation['file'])//
                && file_exists($operation['file']);

        if ($start) {
            $this->response->download($operation['file']);
        }
    }

    /**
     * Starts export
     * @param array $operation
     * @return null
     */
    protected function submitExport(array $operation)
    {
        if (!$this->isPosted('export')) {
            return null;
        }

        $this->setSubmitted('export');
        $this->validateExport($operation);

        if (!$this->hasErrors('export')) {
            $this->setJobExport($operation);
        }

        return null;
    }

    /**
     * Validates an array of csv export data
     * @param array $operation
     * @return null
     */
    protected function validateExport(array $operation)
    {
        $options = $this->getSubmitted();
        $options['count'] = true;

        $limit = $this->export->getLimit();
        $total = $this->product->getList($options); // TODO: fix

        $this->setSubmitted('total', $total);
        $this->setSubmitted('export_limit', $limit);

        if (empty($total)) {
            $this->setError('error', $this->text('Nothing to export'));
            return null;
        }

        if (file_put_contents($operation['file'], '') === false) {

            $message = $this->text('Failed to create file %path', array(
                '%path' => $operation['file']
            ));

            $this->setError('error', $message);
            return null;
        }

        $delimiter = $this->export->getCsvDelimiter();
        Tool::writeCsv($operation['file'], $operation['csv']['header'], $delimiter);
        $this->setSubmitted('operation', $operation);

        return null;
    }

    /**
     * Sets and performs export job
     * @param array $operation
     */
    protected function setJobExport(array $operation)
    {
        $submitted = $this->getSubmitted();

        $finish_message = $this->text('Successfully exported %count items. <a href="@href">Download</a>', array(
            '@href' => $this->url(false, array('download' => 1)),
            '%count' => $submitted['total']
        ));

        $redirect_error_message = $this->text('Errors: %errors. <a href="!url">See error log</a>', array(
            '!url' => $this->url(false, array('download_errors' => 1))
        ));

        $job = array(
            'id' => $operation['job_id'],
            'data' => $submitted,
            'total' => $submitted['total'],
            'redirect_message' => array('finish' => $finish_message)
        );

        if (!empty($operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $redirect_error_message;
        }

        $this->setJob($job);
    }

    /**
     * Sets titles on the export page
     * @param array $operation
     */
    protected function setTitleEditExport(array $operation)
    {
        $text = $this->text('Export %operation', array(
            '%operation' => $operation['name']
        ));

        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the export page
     */
    protected function setBreadcrumbEditExport()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Operations'),
            'url' => $this->url('admin/tool/export')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Renders the export page
     */
    protected function outputEditExport()
    {
        $this->output('tool/export/edit');
    }

}
