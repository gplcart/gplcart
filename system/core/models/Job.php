<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache,
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
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Constructor
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
        $this->hook->fire('job.get.before', $job_id);

        if (empty($job_id)) {
            return array();
        }

        $job = $this->getSession($job_id);

        $this->hook->fire('job.get.after', $job_id, $job);
        return $job;
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return array
     */
    public function set(array $job)
    {
        $this->hook->fire('job.set.before', $job);

        if (empty($job)) {
            return array();
        }

        $default = $this->getDefault();
        $job = gplcart_array_merge($default, $job);

        $existing = $this->getSession($job['id']);

        if (!empty($existing)) {
            return $existing;
        }

        $this->setSession($job);
        $this->hook->fire('job.set.after', $job, $job['id']);
        return $job;
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
        );
    }

    /**
     * Deletes a job from the session
     * @param mixed $job_id
     * @return boolean
     */
    public function delete($job_id = null)
    {
        $this->hook->fire('job.delete.before', $job_id);

        if ($job_id === false) {
            return false;
        }

        $this->session->delete(self::SESSION_KEY . ".$job_id");
        $this->hook->fire('job.delete.after', $job_id);
        return true;
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

        $this->hook->fire('job.process.before', $job);

        if (empty($job['status'])) {
            return $this->result($job, array('finish' => true));
        }

        $progress = 0;
        $start_time = microtime(true);

        // Loop until the max time limit reached
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

        $this->hook->fire('job.process.after', $job, $result, $response);
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
     * @return null
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

        $this->session->setMessage($message, 'danger');
    }

    /**
     * Returns an array of job handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &Cache::memory('job.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['export_product'] = array(
            'handlers' => array(
                'total' => array('gplcart\\core\\handlers\\job\\export\\Product', 'total'),
                'process' => array('gplcart\\core\\handlers\\job\\export\\Product', 'process')
            ),
        );

        $handlers['export_category'] = array(
            'handlers' => array(
                'total' => array('gplcart\\core\\handlers\\job\\export\\Category', 'total'),
                'process' => array('gplcart\\core\\handlers\\job\\export\\Category', 'process')
            ),
        );

        $handlers['import_category'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\job\\import\\Category', 'process')
            ),
        );

        $handlers['import_product'] = array(
            'handlers' => array(
                'process' => array('gplcart\\core\\handlers\\job\\import\\Product', 'process')
            ),
        );

        $this->hook->fire('job.handlers', $handlers);
        return $handlers;
    }

    /**
     * Submits a new job
     * @param array $job
     */
    public function submit($job)
    {
        $this->delete($job['id']);

        if (!empty($job['data']['operation']['log']['errors'])) {
            file_put_contents($job['data']['operation']['log']['errors'], '');
        }

        $this->set($job);
        $this->url->redirect('', array('job_id' => $job['id']));
    }

}
