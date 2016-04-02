<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers\admin;

use core\Controller;
use core\models\Job;
use core\models\Product;
use core\models\Export as E;
use core\classes\Tool;

class Export extends Controller
{

    /**
     * Job model instance
     * @var \core\models\Job $job
     */
    protected $job;

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
     * @param Job $job
     * @param E $export
     * @param Product $product
     */
    public function __construct(Job $job, E $export, Product $product)
    {
        parent::__construct();

        $this->job = $job;
        $this->export = $export;
        $this->product = $product;
    }

    /**
     * Displays the export operations overview page
     */
    public function operations()
    {
        $this->data['operations'] = $this->export->getOperations();

        $this->setTitleOperatios();
        $this->setBreadcrumbOperatios();
        $this->outputOperatios();
    }

    /**
     * Sets titles on the operations overview page
     */
    protected function setTitleOperatios()
    {
        $this->setTitle($this->text('Export'));
    }

    /**
     * Sets breadcrumbs on the operations overview page
     */
    protected function setBreadcrumbOperatios()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
    }

    /**
     * Renders the operations overview page
     */
    protected function outputOperatios()
    {
        $this->output('tool/export/list');
    }

    /**
     * Displays the csv export page
     */
    public function export($operation_id)
    {
        $operation = $this->get($operation_id);

        if ($this->request->get('download')) {
            $this->download($operation);
        }

        if ($this->request->post('export')) {
            $this->submit($operation);
        }

        $this->data['job'] = $this->getJob();
        $this->data['limit'] = $this->export->getLimit();
        $this->data['stores'] = $this->store->getNames();

        $this->setTitleExport($operation);
        $this->setBreadcrumbExport();
        $this->outputExport();
    }

    /**
     * Renders the export page
     */
    protected function outputExport()
    {
        $this->output('tool/export/edit');
    }

    /**
     * Sets titles on the export page
     * @param array $operation
     */
    protected function setTitleExport($operation)
    {
        $this->setTitle($this->text('Export %operation', array('%operation' => $operation['name'])));
    }

    /**
     * Sets breadcrumbs on the export page
     */
    protected function setBreadcrumbExport()
    {
        $this->setBreadcrumb(array('text' => $this->text('Dashboard'), 'url' => $this->url('admin')));
        $this->setBreadcrumb(array('text' => $this->text('Operations'), 'url' => $this->url('admin/tool/export')));
    }

    /**
     * Returns an array of operation data
     * @param string $operation_id
     * @return array
     */
    protected function get($operation_id)
    {
        $operation = $this->export->getOperation($operation_id);

        if ($operation) {
            return $operation;
        }

        $this->outputError(404);
    }

    /**
     * Outputs export file to download
     * @param array $operation
     */
    protected function download($operation)
    {
        if (file_exists($operation['file'])) {
            $this->response->download($operation['file']);
        }
    }

    /**
     * Starts export
     * @param array $operation
     * @return null
     */
    protected function submit($operation)
    {
        $this->submitted = $this->request->post();
        $this->validate($operation);
        $errors = $this->formErrors(false);

        if (!empty($errors)) {
            return;
        }

        $job_id = $operation['job_id'];
        $this->job->delete($job_id);

        $job = array(
            'id' => $job_id,
            'redirect_message' => array(
                'finish' => $this->text('Successfully exported %count items. <a href="!href">Download</a>', array(
                    '!href' => $this->url(false, array('download' => 1)),
                    '%count' => $this->submitted['total']))),
            'total' => $this->submitted['total'],
            'operations' => array($job_id => array('arguments' => array($this->submitted)))
        );

        if (!empty($operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $this->text('Errors: %errors. <a href="!url">See error log</a>', array(
                '!url' => $this->url(false, array('download_errors' => 1))));
        }

        $this->job->set($job);
        $this->url->redirect(false, array('job_id' => $job_id));
    }

    /**
     * Validates an array of csv export data
     */
    protected function validate($operation)
    {
        $this->submitted['total'] = $this->product->getList($this->submitted + array('count' => true));

        if (!$this->submitted['total']) {
            $this->setMessage($this->text('Nothing to export'), 'danger');
            $this->data['form_errors'] = true;
            return false;
        }

        if (file_put_contents($operation['file'], '') === false) {
            $this->setMessage($this->text('Failed to create file %path', array('%path' => $operation['file'])), 'danger');
            $this->data['form_errors'] = true;
            return false;
        }

        if (!empty($operation['log']['errors'])) {
            file_put_contents($operation['log']['errors'], '');
        }

        Tool::writeCsv($operation['file'], $operation['csv']['header'], $this->export->getCsvDelimiter());
        $this->submitted['operation'] = $operation;
        return true;
    }
}
