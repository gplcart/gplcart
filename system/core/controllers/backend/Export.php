<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Export as ExportModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to export operations
 */
class Export extends BackendController
{

    /**
     * Export model instance
     * @var \gplcart\core\models\Export $export
     */
    protected $export;

    /**
     * The current export operation
     * @var array
     */
    protected $data_operation = array();

    /**
     * Constructor
     * @param ExportModel $export
     */
    public function __construct(ExportModel $export)
    {
        parent::__construct();

        $this->export = $export;
    }

    /**
     * Displays the export operations overview page
     */
    public function listExport()
    {
        $this->setTitleListExport();
        $this->setBreadcrumbListExport();

        $this->setData('operations', $this->getOperationsExport());

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
        $this->setExport($operation_id);
        $this->downloadExport();

        $this->setTitleEditExport();
        $this->setBreadcrumbEditExport();

        $this->submitExport();
        
        $job = $this->getCurrentJob();

        $this->setData('job', $this->renderJob($job));
        $this->setData('stores', $this->store->getNames());
        $this->outputEditExport();
    }

    /**
     * Starts export
     */
    protected function submitExport()
    {
        if ($this->isPosted('export') && $this->validateExport()) {
            $this->setJobExport();
        }
    }

    /**
     * Validates an array of csv export data
     * @return bool
     */
    protected function validateExport()
    {
        $this->setSubmitted('settings');

        $this->setSubmitted('limit', $this->export->getLimit());
        $this->setSubmitted('operation', $this->data_operation['id']);
        $this->setSubmitted('delimiter', $this->export->getCsvDelimiter());
        $this->setSubmitted('multiple_delimiter', $this->export->getCsvDelimiterMultiple());

        $this->validate('export');

        return !$this->hasErrors('settings');
    }

    /**
     * Returns an array of operation data
     * @param string $operation_id
     * @return array
     */
    protected function setExport($operation_id)
    {
        $operation = $this->export->getOperation($operation_id);

        if (empty($operation)) {
            $this->outputHttpStatus(404);
        }

        $this->data_operation = $operation;
        return $operation;
    }

    /**
     * Outputs export file to download
     */
    protected function downloadExport()
    {
        $download = $this->isQuery('download')//
                && !empty($this->data_operation['file'])//
                && file_exists($this->data_operation['file']);

        if ($download) {
            $this->response->download($this->data_operation['file']);
        }
    }

    /**
     * Sets and performs export job
     */
    protected function setJobExport()
    {
        $submitted = $this->getSubmitted();

        $vars1 = array('@href' => $this->url('', array('download' => 1)), '%count' => $submitted['total']);
        $finish = $this->text('Exported %count items. <a href="@href">Download</a>', $vars1);

        $vars2 = array('!url' => $this->url('', array('download_errors' => 1)));
        $redirect = $this->text('Errors: %errors. <a href="!url">See error log</a>', $vars2);

        $job = array(
            'data' => $submitted,
            'id' => $this->data_operation['job_id'],
            'total' => $submitted['total'],
            'redirect_message' => array('finish' => $finish)
        );

        if (!empty($this->data_operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $redirect;
        }

        $this->job->submit($job);
    }

    /**
     * Sets titles on the export page
     */
    protected function setTitleEditExport()
    {
        $vars = array('%operation' => $this->data_operation['name']);
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
