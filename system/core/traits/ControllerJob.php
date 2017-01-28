<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods to process various system jobs
 */
trait ControllerJob
{

    /**
     * Loads the current job from the current URL
     * @param \gplcart\core\models\Job $job
     * @param \gplcart\core\helpers\Request $request
     * @return array
     */
    protected function getCurrentJobTrait($job, $request)
    {
        $job_id = (string) $request->get('job_id');

        if (empty($job_id)) {
            return array();
        }

        return $job->get($job_id);
    }

    /**
     * Processes the current job
     * @param \gplcart\core\Controller $controller
     * @param \gplcart\core\models\Job $job
     * @param \gplcart\core\helpers\Request $request
     * @param \gplcart\core\helpers\Response $response
     * @return null
     */
    protected function processCurrentJobTrait($controller, $job, $request,
            $response)
    {
        $data = $this->getCurrentJobTrait($job, $request);

        if (empty($data['status'])) {
            return null;
        }

        $controller->setJsSettings('job', $data);
        $process_job_id = (string) $request->get('process_job');

        if ($request->isAjax() && $process_job_id == $data['id']) {
            $response->json($job->process($data));
        }
    }

}
