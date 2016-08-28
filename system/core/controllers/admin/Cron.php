<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\admin;

use core\classes\Tool;
use core\models\Report as ModelsReport;
use core\controllers\admin\Controller as BackendController;

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
    public function executeCron()
    {
        $this->controlAccessExecuteCron();

        register_shutdown_function(array($this, 'shutdownHandlerCron'));
        ini_set('max_execution_time', 0);

        $this->logger->log('cron', 'Cron has started', 'info', true);

        $this->processTasksCron();

        $this->hook->fire('cron');
        $this->config->set('cron_last_run', GC_TIME);

        exit($this->text('Cron has started'));
    }

    /**
     * Handles PHP shutdown
     */
    public function shutdownHandlerCron()
    {
        $this->config->set('cron_last_run', GC_TIME);
    }

    /**
     * Controls access to execute cron
     * @return null
     */
    protected function controlAccessExecuteCron()
    {
        $key = (string) $this->request->get('key', '');

        if (strcmp($key, $this->cron_key) !== 0) {
            exit;
        }

        if ($this->access('cron')) {
            return;
        }

        if (!empty($this->cron_interval) && ((GC_TIME - $this->cron_last_run) > $this->cron_interval)) {
            return;
        }

        exit;
    }

    /**
     * Deletes expired records from the history table
     */
    protected function processTasksCron()
    {
        // Delete expired records from history table
        $sth = $this->config->getDb()->prepare('DELETE FROM history WHERE time < :time');
        $sth->execute(array(':time' => (GC_TIME - (int) $this->config('history_lifespan', 2628000))));

        // Delete old files
        $dirs = array(
            'log' => GC_PRIVATE_LOGS_DIR,
            'import' => GC_PRIVATE_IMPORT_DIR,
            'export' => GC_PRIVATE_DOWNLOAD_DIR
        );

        $deleted = 0;
        foreach ($dirs as $key => $path) {
            $deleted += Tool::deleteFiles($path, array('csv'), $this->config("{$key}_lifespan", 86400));
        }

        if ($deleted > 0) {

            $log = array(
                'message' => 'Deleted @num expired files',
                'variables' => array('@num' => $deleted));

            $this->logger->log('cron', $log);
        }

        // Delete expired log records
        $this->report->clearExpired($this->config('report_log_lifespan', 86400));
        $result = $this->report->checkFilesystem();

        if ($result !== true) {
            foreach ((array) $result as $message) {
                $log = array('message' => $message);
                $this->logger->log('system_status', $log, 'warning');
            }
        }
    }

}
