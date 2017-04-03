<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Job as JobModel,
    gplcart\core\models\Export as ExportModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate export operations
 */
class Export extends ComponentValidator
{

    /**
     * Export model instance
     * @var \gplcart\core\models\Export $export
     */
    protected $export;

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Constructor
     * @param JobModel $job
     * @param ExportModel $export
     */
    public function __construct(JobModel $job, ExportModel $export)
    {
        parent::__construct();

        $this->job = $job;
        $this->export = $export;
    }

    /**
     * Performs validation of submitted export data
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function export(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateOperationExport();
        $this->validateTotalExport();
        $this->validateFileExport();
        $this->validateHeaderExport();

        return $this->getResult();
    }

    /**
     * Validates operation data
     * @return boolean
     */
    protected function validateOperationExport()
    {
        $operation = $this->getSubmitted('operation');

        if (!is_array($operation)) {
            $operation = $this->export->getOperation($operation);
        }

        if (empty($operation)) {
            $this->setErrorUnavailable('operation', $this->language->text('Operation'));
            return false;
        }

        $this->setSubmitted('operation', $operation);
        return true;
    }

    /**
     * Validates operation file
     * @return boolean
     */
    protected function validateFileExport()
    {
        if ($this->isError()) {
            return null;
        }

        $operation = $this->getSubmitted('operation');

        if (file_put_contents($operation['file'], '') === false) {
            $vars = array('%path' => $operation['file']);
            $message = $this->language->text('Failed to create file %path', $vars);
            $this->setError('operation', $message);
            return false;
        }

        return true;
    }

    /**
     * Validates CSV header
     * @return boolean
     */
    protected function validateHeaderExport()
    {
        if ($this->isError()) {
            return null;
        }

        $delimiter = $this->getSubmitted('delimiter');
        $operation = $this->getSubmitted('operation');

        if (empty($delimiter)) {
            $this->setErrorRequired('operation', $this->language->text('Delimiter'));
            return false;
        }

        if (empty($operation['csv']['header'])) {
            $this->setErrorRequired('operation', $this->language->text('Header'));
            return false;
        }

        gplcart_file_csv($operation['file'], $operation['csv']['header'], $delimiter);
        return true;
    }

    /**
     * Validates total of items to be processed
     * @return bool
     */
    protected function validateTotalExport()
    {
        $options = $this->getSubmitted('options');
        $operation = $this->getSubmitted('operation');

        $total = $this->job->getTotal($operation['job_id'], $options);

        if (empty($total)) {
            $this->setError('operation', $this->language->text('Nothing to export'));
            return false;
        }

        $this->setSubmitted('total', $total);
        return true;
    }

}
