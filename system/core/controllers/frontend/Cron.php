<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\Logger;
use gplcart\core\models\Report as ReportModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to cron jobs
 */
class Cron extends FrontendController
{

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * @param ReportModel $report
     * @param Logger $logger
     */
    public function __construct(ReportModel $report, Logger $logger)
    {
        parent::__construct();

        $this->logger = $logger;
        $this->report = $report;
    }

    /**
     * Processes cron requests
     */
    public function executeCron()
    {
        $this->controlAccessExecuteCron();

        register_shutdown_function(array($this, 'shutdownHandlerCron'));
        ini_set('max_execution_time', 0);
        $this->logger->log('cron', 'Cron has started', 'info', true);

        $this->processTasksCron();

        $this->hook->fire('cron', $this);
        $this->config->set('cron_last_run', GC_TIME);
        $this->response->html($this->text('Cron has started'));
    }

    /**
     * Controls access to execute cron
     */
    protected function controlAccessExecuteCron()
    {
        $key = $this->getQuery('key', '', 'string');
        if (strcmp($key, $this->config('cron_key', '')) !== 0) {
            $this->response->error403(false);
        }
    }

    /**
     * Processes all defined tasks
     */
    protected function processTasksCron()
    {
        $this->deleteExpiredHistoryCron();
        $this->deleteExpiredFilesCron();
        $this->deleteExpiredLogsCron();
        $this->checkFilesystemCron();
    }

    /**
     * Deletes expired records from history table
     */
    protected function deleteExpiredHistoryCron()
    {
        $lifespan = (int) $this->config('history_lifespan', 2628000);
        $ago = (GC_TIME - $lifespan);

        /* @var $database \gplcart\core\Database */
        $database = $this->config->getDb();
        $database->run('DELETE FROM history WHERE time < ?', array($ago));
    }

    /**
     * Deletes old expired files
     */
    protected function deleteExpiredFilesCron()
    {
        foreach (gplcart_file_scan_recursive(GC_PRIVATE_TEMP_DIR) as $file) {
            if (is_dir($file)) {
                gplcart_file_delete_recursive($file);
            } else if (basename($file) !== '.gitignore') {
                unlink($file);
            }
        }
    }

    /**
     * Deletes expired log records
     */
    protected function deleteExpiredLogsCron()
    {
        $lifespan = $this->config('report_log_lifespan', 86400);
        $this->report->deleteExpired($lifespan);
    }

    /**
     * Checks filesystem and logs errors
     * @return boolean
     */
    protected function checkFilesystemCron()
    {
        $result = $this->report->checkFilesystem();

        if ($result === true) {
            return true;
        }

        foreach ((array) $result as $message) {
            $log = array('message' => $message);
            $this->logger->log('system_status', $log, 'warning');
        }

        return false;
    }

    /**
     * Handles PHP shutdown
     */
    public function shutdownHandlerCron()
    {
        $this->config->set('cron_last_run', GC_TIME);
    }

}
