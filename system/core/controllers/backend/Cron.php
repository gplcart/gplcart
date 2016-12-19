<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\backend;

use core\models\Report as ReportModel;
use core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to cron jobs
 */
class Cron extends BackendController
{

    /**
     * Report model instance
     * @var \core\models\Report $report
     */
    protected $report;

    /**
     * Controller
     * @param ReportModel $report
     */
    public function __construct(ReportModel $report)
    {
        parent::__construct();
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

        $this->hook->fire('cron');
        $this->config->set('cron_last_run', GC_TIME);
        $this->response->html($this->text('Cron has started'));
    }

    /**
     * Controls access to execute cron
     */
    protected function controlAccessExecuteCron()
    {
        $key = (string) $this->request->get('key', '');

        if (strcmp($key, $this->cron_key) !== 0) {
            $this->response->error403();
        }

        $has_access = $this->access('cron') //
                || (!empty($this->cron_interval) //
                && ((GC_TIME - $this->cron_last_run) > $this->cron_interval));

        if (!$has_access) {
            $this->response->error403();
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

        /* @var $database \core\Database */
        $database = $this->config->getDb();
        $database->run('DELETE FROM history WHERE time < ?', array($ago));
    }

    /**
     * Deletes old expired files
     */
    protected function deleteExpiredFilesCron()
    {
        $directories = array(
            'log' => GC_PRIVATE_LOGS_DIR,
            'import' => GC_PRIVATE_IMPORT_DIR,
            'export' => GC_PRIVATE_DOWNLOAD_DIR
        );

        $deleted = 0;
        foreach ($directories as $key => $path) {
            $extensions = array('csv');
            $lifespan = $this->config("{$key}_lifespan", 86400);
            $deleted += gplcart_file_delete($path, $extensions, $lifespan);
        }

        if (empty($deleted)) {
            return false;
        }

        $log = array(
            'message' => 'Deleted @num expired files',
            'variables' => array('@num' => $deleted)
        );

        $this->logger->log('cron', $log);
        return true;
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
