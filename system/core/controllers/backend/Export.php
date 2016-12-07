<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\helpers\File;
use core\models\Export as ExportModel;
use core\models\Product as ProductModel;
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
     * @param ExportModel $export
     * @param ProductModel $product
     */
    public function __construct(ExportModel $export, ProductModel $product)
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
        $download = $this->isQuery('download')//
                && !empty($operation['file'])//
                && file_exists($operation['file']);

        if ($download) {
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

        $this->setSubmitted('settings');
        $this->validateExport($operation);

        if ($this->hasErrors('settings')) {
            return null;
        }

        $this->setJobExport($operation);
        return null;
    }

    /**
     * Validates an array of csv export data
     * @param array $operation
     * @return null
     */
    protected function validateExport(array $operation)
    {
        $options = $this->getSubmitted('options');

        $limit = $this->export->getLimit();
        $delimiter = $this->export->getCsvDelimiter();
        $multiple_delimiter = $this->export->getCsvDelimiterMultiple();

        $total = $this->job->getTotal($operation['job_id'], $options);

        if (empty($total)) {
            $this->setError('error', $this->text('Nothing to export'));
            return null;
        }

        $this->setSubmitted('total', $total);
        $this->setSubmitted('limit', $limit);
        $this->setSubmitted('delimiter', $delimiter);
        $this->setSubmitted('multiple_delimiter', $multiple_delimiter);

        $this->validateFileExport($operation);
        return null;
    }

    /**
     * Creates an export file and writes there header
     * @param array $operation
     * @return boolean
     */
    protected function validateFileExport(array $operation)
    {
        if (file_put_contents($operation['file'], '') === false) {
            $vars = array('%path' => $operation['file']);
            $message = $this->text('Failed to create file %path', $vars);
            $this->setError('error', $message);
            return false;
        }

        $delimiter = $this->getSubmitted('delimiter');
        File::csv($operation['file'], $operation['csv']['header'], $delimiter);
        $this->setSubmitted('operation', $operation);
        return true;
    }

    /**
     * Sets and performs export job
     * @param array $operation
     */
    protected function setJobExport(array $operation)
    {
        $submitted = $this->getSubmitted();

        $vars = array('@href' => $this->url(false, array('download' => 1)), '%count' => $submitted['total']);
        $finish = $this->text('Exported %count items. <a href="@href">Download</a>', $vars);

        $vars = array('!url' => $this->url(false, array('download_errors' => 1)));
        $redirect = $this->text('Errors: %errors. <a href="!url">See error log</a>', $vars);

        $job = array(
            'data' => $submitted,
            'id' => $operation['job_id'],
            'total' => $submitted['total'],
            'redirect_message' => array('finish' => $finish)
        );

        if (!empty($operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $redirect;
        }

        $this->setJob($job);
    }

    /**
     * Sets titles on the export page
     * @param array $operation
     */
    protected function setTitleEditExport(array $operation)
    {
        $vars = array('%operation' => $operation['name']);
        $text = $this->text('Export %operation', $vars);
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
