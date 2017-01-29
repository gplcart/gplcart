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
     * @param \gplcart\core\Controller $controller
     * @return array
     */
    protected function getCurrentJobTrait($controller)
    {
        /* @var $job \gplcart\core\models\Job */
        $job = $controller->getInstance('job');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        $job_id = (string) $request->get('job_id');

        if (empty($job_id)) {
            return array();
        }

        return $job->get($job_id);
    }

    /**
     * Processes the current job
     * @param \gplcart\core\Controller $controller
     * @return null
     */
    protected function processCurrentJobTrait($controller)
    {
        /* @var $job \gplcart\core\models\Job */
        $job = $controller->getInstance('job');

        /* @var $request \gplcart\core\helpers\Request */
        $request = $controller->getInstance('request');

        /* @var $response \gplcart\core\helpers\Response */
        $response = $controller->getInstance('response');

        $data = $this->getCurrentJobTrait($controller);

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
