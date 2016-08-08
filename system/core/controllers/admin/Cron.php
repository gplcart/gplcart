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
use core\models\Report as ModelsReport;

/**
 * Handles incoming requests and outputs data related to cron jobs
 */
class Cron extends Controller
{

    /**
     * Report model instance
     * @var \core\models\Report $report
     */
    protected $report;

    /**
     * Controller
     * @param ModelsReport $report
     */
    public function __construct(ModelsReport $report)
    {
        parent::__construct();
        $this->report = $report;
    }

    /**
     * Processes cron requests
     */
    public function cron()
    {
        $this->checkKey();
        $this->checkAccess();

        register_shutdown_function(array($this, 'shutdownHandler'));
        ini_set('max_execution_time', 0);

        $this->logger->log('cron', 'Cron has started');

        $this->processFiles();
        $this->processLogs();
        $this->processHistory();

        $this->hook->fire('cron');
        $this->config->set('cron_last_run', GC_TIME);

        exit($this->text('Cron has started'));
    }

    /**
     * Handles PHP shutdown
     */
    public function shutdownHandler()
    {
        $this->config->set('cron_last_run', GC_TIME);
    }

    /**
     * Checks the current cron key
     */
    protected function checkKey()
    {
        $key = $this->request->get('key', '');

        if (!is_string($key)) {
            exit;
        }

        if (empty($key) || (strcmp($key, $this->cron_key) !== 0)) {
            exit;
        }
    }

    /**
     * Checks whether to abort cron execution
     * @return boolean
     */
    protected function checkAccess()
    {
        if ($this->access('cron')) {
            return true;
        }

        if (!empty($this->cron_interval) && ((GC_TIME - $this->cron_last_run) > $this->cron_interval)) {
            return true;
        }

        exit;
    }

    /**
     * Deletes expired records from the history table
     */
    protected function processHistory()
    {
        $sth = $this->config->getDb()->prepare('DELETE FROM history WHERE time < :time');
        $sth->execute(array(':time' => (GC_TIME - (int) $this->config->get('history_lifespan', 2628000))));
    }

    /**
     * Processes files (delete expired etc)
     * @return integer
     */
    protected function processFiles()
    {
        $deleted = 0;
        $deleted += Tool::deleteFiles(GC_PRIVATE_DOWNLOAD_DIR, array('csv'), $this->config->get('export_lifespan', 86400));
        $deleted += Tool::deleteFiles(GC_PRIVATE_IMPORT_DIR, array('csv'), $this->config->get('import_lifespan', 86400));
        $deleted += Tool::deleteFiles(GC_PRIVATE_LOGS_DIR, array('csv'), $this->config->get('log_lifespan', 86400));

        if ($deleted > 0) {
            $this->logger->log('cron', array('message' => 'Deleted @num expired files', 'variables' => array('@num' => $deleted)));
        }

        return $deleted;
    }

    /**
     * Process saved logs
     * @return boolean
     */
    protected function processLogs()
    {
        $this->report->clearExpired($this->config->get('report_log_lifespan', 86400));

        $status = $this->report->checkFilesystem();

        if ($status !== true) {
            $this->logger->log('system_status', array(
                'message' => implode('<br>', (array) $status)), 'warning');
        }

        return true;
    }

}
