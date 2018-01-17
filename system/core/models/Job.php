<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use Exception;
use gplcart\core\Hook,
    gplcart\core\Handler;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to batch jobs
 */
class Job
{

    /**
     * Max milliseconds for one iteration
     */
    const LIMIT = 1000;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * @param Hook $hook
     * @param Translation $translation
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(Hook $hook, TranslationModel $translation, SessionHelper $session,
            UrlHelper $url)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->session = $session;
        $this->translation = $translation;
    }

    /**
     * Returns a job array from the session
     * @param string $job_id
     * @return array
     */
    public function get($job_id)
    {
        $result = null;
        $this->hook->attach('job.get.before', $job_id, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $result = $this->getSession($job_id);
        $this->hook->attach('job.get.after', $job_id, $result, $this);
        return $result;
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return array
     */
    public function set(array $job)
    {
        $result = null;
        $this->hook->attach('job.set.before', $job, $result, $this);

        if (isset($result)) {
            return (array) $result;
        }

        $default = $this->getDefault();
        $result = gplcart_array_merge($default, $job);
        $existing = $this->getSession($result['id']);

        if (!empty($existing)) {
            return (array) $existing;
        }

        $this->setSession($result);
        $this->hook->attach('job.set.after', $job, $result, $this);
        return (array) $result;
    }

    /**
     * Returns an array of default job values
     * @return array
     */
    protected function getDefault()
    {
        $current_url = $this->url->get();

        return array(
            'id' => uniqid(),
            'status' => true,
            'title' => '',
            'url' => '',
            'total' => 0,
            'errors' => 0,
            'done' => 0,
            'inserted' => 0,
            'updated' => 0,
            'context' => array(
                'offset' => 0,
                'line' => 1,
            ),
            'data' => array(),
            'message' => array(
                'start' => $this->translation->text('Starting...'),
                'finish' => $this->translation->text('Finished'),
                'process' => $this->translation->text('Processing...')
            ),
            'redirect' => array(
                'finish' => $current_url,
                'errors' => $current_url,
                'no_results' => $current_url
            ),
            'redirect_message' => array(
                'finish' => '',
                'errors' => '',
                'no_results' => ''
            ),
            'log' => array(
                'errors' => ''
            )
        );
    }

    /**
     * Deletes a job from the session
     * @param mixed $job_id
     * @return boolean
     */
    public function delete($job_id = null)
    {
        $result = null;
        $this->hook->attach('job.delete.before', $job_id, $result, $this);

        if (isset($result)) {
            return (bool) $result;
        }

        $result = $this->session->delete("jobs.$job_id");
        $this->hook->attach('job.delete.after', $job_id, $result, $this);
        return (bool) $result;
    }

    /**
     * Processes one job iteration
     * @param array $job
     * @return array
     */
    public function process(array $job)
    {
        $this->hook->attach('job.process.before', $job, $this);

        if (empty($job['status'])) {
            return $this->getResult($job, array('finish' => true));
        }

        $progress = 0;
        $start_time = microtime(true);

        while (round((microtime(true) - $start_time) * 1000, 2) < self::LIMIT) {

            $this->processIteration($job);

            if (empty($job['status'])) {
                break;
            }

            $progress = round($job['done'] * 100 / $job['total']);

            if ($job['done'] < $job['total']) {
                continue;
            }

            $job['status'] = false;
            break;
        }

        $result = array(
            'progress' => $progress,
            'done' => $job['done'],
            'errors' => $job['errors'],
            'finish' => empty($job['status']),
            'message' => $job['message']['process']
        );

        $response = $this->getResult($job, $result);

        $this->hook->attach('job.process.after', $job, $result, $response, $this);
        return $response;
    }

    /**
     * Call a handler processor
     * @param array $job
     */
    protected function processIteration(array &$job)
    {
        try {
            $handlers = $this->getHandlers();
            $callback = Handler::get($handlers, $job['id'], 'process');
            call_user_func_array($callback, array(&$job));
        } catch (Exception $ex) {
            $job['status'] = false;
            $job['errors'] ++;
        }
    }

    /**
     * Returns total number of items to be processed
     * @param string $handler_id
     * @param array $arguments
     * @return integer
     */
    public function getTotal($handler_id, array $arguments = array())
    {
        try {
            $handlers = $this->getHandlers();
            return (int) Handler::call($handlers, $handler_id, 'total', array($arguments));
        } catch (Exception $ex) {
            return 0;
        }
    }

    /**
     * Returns a job from the session
     * @param string $job_id
     * @return array
     */
    protected function getSession($job_id)
    {
        return $this->session->get("jobs.$job_id", array());
    }

    /**
     * Sets a job to the session
     * @param array $job
     */
    protected function setSession(array $job)
    {
        $this->session->set("jobs.{$job['id']}", $job);
    }

    /**
     * Returns an array of data to be send to the user
     * @param array $job
     * @param array $result
     * @return array
     */
    protected function getResult(array $job, array $result = array())
    {
        $result += array(
            'done' => 0,
            'errors' => 0,
            'progress' => 0,
            'finish' => false,
            'message' => $job['message']['process']
        );

        if (!empty($result['finish'])) {
            $this->setFinishData($result, $job);
        }

        $this->setSession($job);
        return $result;
    }

    /**
     * Sets finish redirect and message
     * @param array $result
     * @param array $job
     */
    protected function setFinishData(array &$result, array &$job)
    {
        $result['message'] = $job['message']['finish'];

        $this->setFinishDataErrors($result, $job);
        $this->setFinishDataNoErrors($result, $job);
    }

    /**
     * Sets finish redirect and message when an error occurred
     * @param array $result
     * @param array $job
     */
    protected function setFinishDataErrors(array &$result, array &$job)
    {
        if (!empty($job['errors'])) {

            if (!empty($job['redirect']['errors'])) {
                $result['redirect'] = $job['redirect']['errors'];
            }

            if (empty($job['redirect_message']['errors'])) {
                $vars = array('%total' => $job['total'], '%errors' => $job['errors']);
                $message = $this->translation->text('Processed %total items, errors: %errors', $vars);
            } else {
                $vars = array('%total' => $job['total'], '%errors' => $job['errors'],
                    '%inserted' => $job['inserted'], '%updated' => $job['updated']);
                $message = $this->translation->text($job['redirect_message']['errors'], $vars);
            }

            $this->session->setMessage($message, 'danger');
        }
    }

    /**
     * Sets finish redirect and message when no errors occurred
     * @param array $result
     * @param array $job
     */
    protected function setFinishDataNoErrors(array &$result, array &$job)
    {
        if (empty($job['errors'])) {

            if (!empty($job['redirect']['finish'])) {
                $result['redirect'] = $job['redirect']['finish'];
            } else if (!empty($job['redirect']['no_results']) && empty($job['inserted']) && empty($job['updated'])) {
                $result['redirect'] = $job['redirect']['no_results'];
            }

            if (empty($job['redirect_message']['finish'])) {
                $vars = array('%total' => $job['total']);
                $message = $this->translation->text('Successfully processed %total items', $vars);
            } else {
                $vars = array('%total' => $job['total'], '%inserted' => $job['inserted'], '%updated' => $job['updated']);
                $message = $this->translation->text($job['redirect_message']['finish'], $vars);
            }

            if (!empty($job['redirect_message']['no_results']) && empty($job['inserted']) && empty($job['updated'])) {
                $vars = array('%total' => $job['total']);
                $message = $this->translation->text($job['redirect_message']['no_results'], $vars);
            }

            $this->session->setMessage($message, 'success');
        }
    }

    /**
     * Returns an array of job handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &gplcart_static('job.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();
        $this->hook->attach('job.handlers', $handlers, $this);
        return $handlers;
    }

    /**
     * Submits a new job
     * @param array $job
     */
    public function submit($job)
    {
        $this->delete($job['id']);

        if (!empty($job['log']['errors'])) {
            file_put_contents($job['log']['errors'], '');
        }

        $this->set($job);
        $this->url->redirect('', array('job_id' => $job['id']));
    }

}
