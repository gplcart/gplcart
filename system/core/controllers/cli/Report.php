<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\cli;

use core\CliController;
use core\models\Report as ModelsReport;

/**
 * Handles CLI commands related to reporting system events, statistic etc
 */
class Report extends CliController
{

    /**
     * Report model instance
     * @var \core\models\Report $report
     */
    protected $report;

    /**
     * Constructor
     */
    public function __construct(ModelsReport $report)
    {
        parent::__construct();

        $this->report = $report;
    }

    /**
     * Outputs results for the "report-status" command
     */
    public function statusReport()
    {
        $message = $this->getMessageStatusReport();
        $this->setMessage($message)->output();
    }

    /**
     * Returns a system status result message
     * @return string
     */
    protected function getMessageStatusReport()
    {
        $data = $this->report->getStatus();

        $message = '';
        foreach ($data as $item) {
            $message .= "{$item['title']} - {$item['status']}" . PHP_EOL;
        }

        return $message;
    }

    /**
     * Outputs results for "report-event" command
     */
    public function eventReport()
    {
        $mapping = $this->getMappingEventReport();
        $default = $this->getDefaultEventReport();
        $this->setSubmittedMapped($mapping, $default);

        $this->validateEventReport();
        $this->outputEventReport();
    }

    /**
     * Validates the "report-event" command
     */
    protected function validateEventReport()
    {
        $submitted = $this->getSubmitted();

        $submitted['limit'] = array(0, (int) $submitted['limit']);
        $this->setSubmitted($submitted);
    }

    /**
     * Returns an array of default arguments for the "report-event" command
     */
    protected function getDefaultEventReport()
    {
        return array('limit' => 20);
    }

    /**
     * Returns an array of data used to associate
     * the current CLI options with the data passed to model methods
     */
    protected function getMappingEventReport()
    {
        return array(
            'type' => 'type',
            'clear' => 'clear',
            'severity' => 'severity',
            'limit' => 'limit'
        );
    }

    /**
     * Outputs result message for the "report-event" command
     */
    protected function outputEventReport()
    {
        if ($this->isSubmitted('clear')) {
            $this->report->clear();
            $this->output();
        }

        $message = $this->getMessageEventReport();
        $this->setMessage($message)->output();
    }

    /**
     * Returns a string containing result message for the "report-event" command
     */
    protected function getMessageEventReport()
    {
        $submitted = $this->getSubmitted();
        $records = $this->report->getList($submitted);

        $message = '';
        foreach ($records as $record) {

            $variables = array();
            if (!empty($record['data']['variables'])) {
                $variables = $record['data']['variables'];
            }

            if (!empty($record['translatable'])) {
                $record['text'] = $this->text($record['text'], $variables);
            }

            $message .= date('d.m.y  H:i', $record['time']) . ' - ' . $record['text'] . PHP_EOL;
        }

        return $message;
    }

}
