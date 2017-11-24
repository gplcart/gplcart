<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Controller methods to process batch jobs
 */
trait Job
{

    /**
     * Processes the current job
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Job $job_model
     */
    protected function setJob($controller, $job_model)
    {
        $cancel_job_id = $controller->getQuery('cancel_job');

        if (!empty($cancel_job_id)) {
            $job_model->delete($cancel_job_id);
            return null;
        }

        $job_id = $controller->getQuery('job_id');

        if (empty($job_id)) {
            return null;
        }

        $job = $job_model->get($job_id);

        if (empty($job['status'])) {
            return null;
        }

        $controller->setJsSettings('job', $job);
        if ($controller->getQuery('process_job') === $job['id'] && $controller->isAjax()) {
            $controller->outputJson($job_model->process($job));
        }
    }

    /**
     * Returns the rendered job widget
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Job $job_model
     * @param null|array $job
     * @return string
     */
    public function getWidgetJob($controller, $job_model, $job = null)
    {
        if (!isset($job)) {
            $job = $job_model->get($controller->getQuery('job_id', ''));
        }

        $rendered = '';
        if (!empty($job['status'])) {
            $job += array('widget' => 'common/job');
            $rendered = $controller->render($job['widget'], array('job' => $job));
        }

        return $rendered;
    }

}
