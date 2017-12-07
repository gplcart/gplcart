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

    abstract public function isAjax();

    abstract public function setJsSettings($key, $data, $weight = null);

    abstract public function outputJson($data, array $options = array());

    abstract public function getQuery($key = null, $default = null, $type = 'string');

    abstract public function render($file, $data = array(), $merge = true, $default = '');

    /**
     * Processes the current job
     * @param \gplcart\core\models\Job $job_model
     */
    protected function setJob($job_model)
    {
        $cancel_job_id = $this->getQuery('cancel_job');

        if (!empty($cancel_job_id)) {
            $job_model->delete($cancel_job_id);
            return null;
        }

        $job_id = $this->getQuery('job_id');

        if (empty($job_id)) {
            return null;
        }

        $job = $job_model->get($job_id);

        if (empty($job['status'])) {
            return null;
        }

        $this->setJsSettings('job', $job);
        if ($this->getQuery('process_job') === $job['id'] && $this->isAjax()) {
            $this->outputJson($job_model->process($job));
        }
    }

    /**
     * Returns the rendered job widget
     * @param \gplcart\core\models\Job $job_model
     * @param null|array $job
     * @return string
     */
    public function getWidgetJob($job_model, $job = null)
    {
        if (!isset($job)) {
            $job = $job_model->get($this->getQuery('job_id', ''));
        }

        $rendered = '';
        if (!empty($job['status'])) {
            $job += array('widget' => 'common/job');
            $rendered = $this->render($job['widget'], array('job' => $job));
        }

        return $rendered;
    }

}
