<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\Controller;
use core\classes\Tool;
use core\models\Job as ModelsJob;
use core\models\Export as ModelsExport;
use core\models\Product as ModelsProduct;

/**
 * Handles incoming requests and outputs data related to export operations
 */
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
     * @param ModelsJob $job
     * @param ModelsExport $export
     * @param ModelsProduct $product
     */
    public function __construct(ModelsJob $job, ModelsExport $export,
            ModelsProduct $product)
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
        $this->setData('operations', $this->export->getOperations());

        $this->setTitleOperatios();
        $this->setBreadcrumbOperatios();
        $this->outputOperatios();
    }

    /**
     * Displays the csv export page
     * @param string $operation_id
     */
    public function export($operation_id)
    {
        $operation = $this->get($operation_id);

        if ($this->isSubmitted('download')) {
            $this->download($operation);
        }

        if ($this->isSubmitted('export')) {
            $this->submit($operation);
        }
        
        $this->setData('job', $this->getJob());
        $this->setData('limit', $this->export->getLimit());
        $this->setData('stores', $this->store->getNames());

        $this->setTitleExport($operation);
        $this->setBreadcrumbExport();
        $this->outputExport();
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
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
    }

    /**
     * Renders the operations overview page
     */
    protected function outputOperatios()
    {
        $this->output('tool/export/list');
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
    protected function setTitleExport(array $operation)
    {
        $this->setTitle($this->text('Export %operation', array(
            '%operation' => $operation['name'])));
    }

    /**
     * Sets breadcrumbs on the export page
     */
    protected function setBreadcrumbExport()
    {
        $this->setBreadcrumb(array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')));
        
        $this->setBreadcrumb(array(
            'text' => $this->text('Operations'),
            'url' => $this->url('admin/tool/export')));
    }

    /**
     * Returns an array of operation data
     * @param string $operation_id
     * @return array
     */
    protected function get($operation_id)
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
    protected function download(array $operation)
    {
        if (!empty($operation['file']) && file_exists($operation['file'])) {
            $this->response->download($operation['file']);
        }
    }

    /**
     * Starts export
     * @param array $operation
     * @return null
     */
    protected function submit(array $operation)
    {
        $this->setSubmitted();
        $this->validate($operation);
        
        if($this->hasErrors()){
            return;
        }

        $job = array(
            'data' => $this->getSubmitted(),
            'id' => $operation['job_id'],
            'total' => $this->getSubmitted('total'),
            'redirect_message' => array(
                'finish' => $this->text('Successfully exported %count items. <a href="!href">Download</a>', array(
                    '!href' => $this->url(false, array('download' => 1)),
                    '%count' => $this->getSubmitted('total')))),
        );

        if (!empty($operation['log']['errors'])) {
            $job['redirect_message']['errors'] = $this->text('Errors: %errors. <a href="!url">See error log</a>', array(
                '!url' => $this->url(false, array('download_errors' => 1))));
        }

        $this->job->submit($job);
    }

    /**
     * Validates an array of csv export data
     * @param array $operation
     * @return null
     */
    protected function validate(array $operation)
    {
        $options = $this->getSubmitted();
        $options['count'] = true;
        $total = $this->product->getList($options); // TODO: fix

        $this->setSubmitted('total', $total);

        if (empty($total)) {
            $this->setError('error', $this->text('Nothing to export'));
            return;
        }

        if (file_put_contents($operation['file'], '') === false) {
            
            $message = $this->text('Failed to create file %path', array(
                '%path' => $operation['file']));
            
            $this->setError('error', $message);
            return;
        }

        Tool::writeCsv($operation['file'], $operation['csv']['header'], $this->export->getCsvDelimiter());
        $this->setSubmitted('operation', $operation);
    }

}
