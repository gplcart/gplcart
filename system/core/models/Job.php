<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Handler,
    gplcart\core\Container;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to batch jobs
 */
class Job extends Model
{

    /**
     * Max milliseconds for one iteration
     */
    const MAX_TIME = 1000;

    /**
     * A key for array of a job data in the session
     */
    const SESSION_KEY = 'jobs';

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

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
     * @param LanguageModel $language
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(LanguageModel $language, SessionHelper $session,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->session = $session;
        $this->language = $language;
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
                'start' => $this->language->text('Starting'),
                'finish' => $this->language->text('Finished'),
                'process' => $this->language->text('Processing')
            ),
            'redirect' => array(
                'finish' => $this->url->get(),
                'errors' => $this->url->get(),
            ),
            'redirect_message' => array(
                'finish' => '',
                'errors' => '',
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

        $result = $this->session->delete(self::SESSION_KEY . ".$job_id");

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
        ini_set('max_execution_time', 0);
        register_shutdown_function(array($this, 'shutdownHandler'), $job);

        $this->hook->attach('job.process.before', $job, $this);

        if (empty($job['status'])) {
            return $this->result($job, array('finish' => true));
        }

        $progress = 0;
        $start_time = microtime(true);

        while (round((microtime(true) - $start_time) * 1000, 2) < self::MAX_TIME) {

            $this->call($job);

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

        $response = $this->result($job, $result);

        $this->hook->attach('job.process.after', $job, $result, $response, $this);
        return $response;
    }

    /**
     * Calls a job processor
     * @param array $job
     * @return boolean
     */
    protected function call(array &$job)
    {
        $handlers = $this->getHandlers();

        if (empty($handlers[$job['id']]['handlers']['process'])) {
            return false;
        }

        $class = $handlers[$job['id']]['handlers']['process'];
        $instance = Container::get($class);
        call_user_func_array(array($instance, $class[1]), array(&$job));
        return true;
    }

    /**
     * Returns total number of items to be processed
     * @param string $handler_id
     * @param array $arguments
     * @return integer
     */
    public function getTotal($handler_id, array $arguments = array())
    {
        $handlers = $this->getHandlers();
        return (int) Handler::call($handlers, $handler_id, 'total', array($arguments));
    }

    /**
     * Shutdown handler
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if (isset($error['type']) && $error['type'] === E_ERROR) {
            $text = $this->language->text('The job has not been properly completed');
            $this->session->setMessage($text, 'danger');
        }
    }

    /**
     * Returns a job from the session
     * @param string $job_id
     * @return array
     */
    protected function getSession($job_id)
    {
        return $this->session->get(self::SESSION_KEY . ".$job_id", array());
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return boolean
     */
    protected function setSession(array $job)
    {
        return $this->session->set(self::SESSION_KEY . ".{$job['id']}", $job);
    }

    /**
     * Returns an array of data to be send to the user
     * @param array $job
     * @param array $result
     * @return array
     */
    protected function result(array $job, array $result = array())
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
     * @return null|bool
     */
    protected function setFinishData(array &$result, array &$job)
    {
        $result['message'] = $job['message']['finish'];

        if (empty($job['errors'])) {
            if (!empty($job['redirect']['finish'])) {
                $result['redirect'] = $job['redirect']['finish'];
            }
            if (empty($job['redirect_message']['finish'])) {
                $message = $this->language->text('Successfully processed %total items', array('%total' => $job['total']));
            } else {
                $message = $this->language->text($job['redirect_message']['finish'], array(
                    '%total' => $job['total'],
                    '%inserted' => $job['inserted'],
                    '%updated' => $job['updated']));
            }
            $this->session->setMessage($message, 'success');
            return null;
        }

        if (!empty($job['redirect']['errors'])) {
            $result['redirect'] = $job['redirect']['errors'];
        }

        if (empty($job['redirect_message']['errors'])) {
            $message = $this->language->text('Processed %total items, errors: %errors', array(
                '%total' => $job['total'],
                '%errors' => $job['errors']));
        } else {
            $message = $this->language->text($job['redirect_message']['errors'], array(
                '%total' => $job['total'],
                '%errors' => $job['errors'],
                '%inserted' => $job['inserted'],
                '%updated' => $job['updated']));
        }

        return $this->session->setMessage($message, 'danger');
    }

    /**
     * Returns an array of job handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &gplcart_static(__METHOD__);

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
