<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Cron as CronModel;

/**
 * Handles incoming requests and outputs data related to CRON jobs
 */
class Cron extends Controller
{

    /**
     * Cron model instance
     * @var \gplcart\core\models\Cron $cron
     */
    protected $cron;

    /**
     * @param CronModel $cron
     */
    public function __construct(CronModel $cron)
    {
        parent::__construct();

        $this->cron = $cron;
    }

    /**
     * Processes CRON requests
     */
    public function executeCron()
    {
        $this->controlAccessExecuteCron();
        $this->cron->run();
        $this->response->outputHtml($this->text('Cron has started'));
    }

    /**
     * Controls access to execute CRON
     */
    protected function controlAccessExecuteCron()
    {
        if (strcmp($this->getQuery('key', ''), $this->cron->getKey()) !== 0) {
            $this->response->outputError403(false);
        }
    }

}
