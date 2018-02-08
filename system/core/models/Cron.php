<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Config;
use gplcart\core\Hook;
use gplcart\core\Logger;
use gplcart\core\models\History as HistoryModel;
use gplcart\core\models\Report as ReportModel;

/**
 * Manages basic behaviors and data related to scheduled tasks
 */
class Cron
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * History model instance
     * @var \gplcart\core\models\History $history
     */
    protected $history;

    /**
     * @param Logger $logger
     * @param Hook $hook
     * @param Config $config
     * @param ReportModel $report
     * @param HistoryModel $history
     */
    public function __construct(Logger $logger, Hook $hook, Config $config, ReportModel $report,
                                HistoryModel $history)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->logger = $logger;

        $this->report = $report;
        $this->history = $history;
    }

    /**
     * Run cron tasks
     * @return bool
     */
    public function run()
    {
        $result = null;
        $this->hook->attach('cron.run.before', $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        ini_set('max_execution_time', 0);
        register_shutdown_function(array($this, 'shutdownHandler'));

        $this->logger->log('cron', 'Cron has started', 'info');

        try {
            $result = $this->process();
        } catch (Exception $ex) {
            $this->logger->log('cron', $ex->getMessage(), 'danger', false);
            $result = false;
        }

        $this->hook->attach('cron.run.after', $result, $this);
        $this->config->set('cron_last_run', GC_TIME);

        return (bool) $result;
    }

    /**
     * Processes all defined tasks
     * @return bool
     */
    public function process()
    {
        $this->report->deleteExpired();
        $this->history->deleteExpired();

        // Delete files in temporary directory
        foreach (gplcart_file_scan_recursive(GC_DIR_PRIVATE_TEMP) as $file) {
            if (strpos(basename($file), '.') !== 0) { // Ignore hidden files
                gplcart_file_delete_recursive($file);
            }
        }

        $result = $this->report->checkFilesystem();

        if ($result !== true) {
            foreach ((array) $result as $message) {
                $this->logger->log('system_status', array('message' => $message), 'warning');
            }
        }

        return true;
    }

    /**
     * Returns the cron key
     * @return string
     */
    public function getKey()
    {
        return $this->config->get('cron_key', '');
    }

    /**
     * Handles PHP shutdown
     */
    public function shutdownHandler()
    {
        $this->config->set('cron_last_run', GC_TIME);
    }

}
