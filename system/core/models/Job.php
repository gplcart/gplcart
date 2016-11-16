<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Container;
use core\classes\Url;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Session;
use core\models\Language as ModelsLanguage;

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
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Constructor
     * @param ModelsLanguage $language
     * @param Session $session
     * @param Url $url
     */
    public function __construct(ModelsLanguage $language, Session $session,
            Url $url)
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
        $this->hook->fire('get.job.before', $job_id);

        if (empty($job_id)) {
            return array();
        }

        $job = $this->getSession($job_id);

        $this->hook->fire('get.job.after', $job_id, $job);
        return $job;
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return array
     */
    public function set(array $job)
    {
        $this->hook->fire('set.job.before', $job);

        if (empty($job)) {
            return array();
        }

        $default = $this->getDefault();
        $job = Tool::merge($default, $job);

        $existing = $this->getSession($job['id']);

        if (!empty($existing)) {
            return $existing;
        }

        $this->setSession($job);
        $this->hook->fire('set.job.after', $job, $job['id']);
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
            'widget' => '',
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
            'data' => array(
                'limit' => $this->config->get('import_limit', 10),
                'delimiter' => $this->config->get('csv_delimiter', ",")
            ),
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
        $this->hook->fire('delete.job.before', $job_id);

        if ($job_id === false) {
            return false;
        }

        $this->session->delete(self::SESSION_KEY, $job_id);
        $this->hook->fire('delete.job.after', $job_id);
        return true;
    }

    /**
     * Processes one job iteration
     * @param array $job
     * @return array
     */
    public function process(array $job)
    {
        // Try to set endless process
        ini_set('max_execution_time', 0);

        //Register shutdown function to handle fatal errors in processor
        register_shutdown_function(array($this, 'shutdownHandler'), $job);

        // Chance to change the job from a module
        $this->hook->fire('process.job.before', $job);

        if (empty($job['status'])) {
            // Probably has been disabled on the previous iteration
            return $this->result($job, array('finish' => true));
        }

        $progress = 0;
        $start_time = microtime(true);

        // Loop until the max time limit reached
        while (round((microtime(true) - $start_time) * 1000, 2) < self::MAX_TIME) {

            // Call a processor
            $this->call($job);

            // Check if the job has been disabled by processor
            if (empty($job['status'])) {
                break;
            }

            // Calculate percent progress
            $progress = round($job['done'] * 100 / $job['total']);

            if ($job['done'] < $job['total']) {
                continue;
            }

            // All done
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

        return $this->result($job, $result);
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
        $instance = Container::instance($class);

        if (!is_object($instance)) {
            return false;
        }

        call_user_func_array(array($instance, $class[1]), array(&$job));
        return true;
    }

    /**
     * Shutdown handler
     * @param array $job
     */
    public function shutdownHandler(array $job)
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
        return $this->session->get(self::SESSION_KEY, $job_id, array());
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return boolean
     */
    protected function setSession(array $job)
    {
        return $this->session->set(self::SESSION_KEY, $job['id'], $job);
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
        $this->hook->fire('result.job', $job, $result);
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
            return;
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

        $handlers['index_product_id'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\search\\Product', 'process')
            ),
        );

        $handlers['index_order_id'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\search\\Order', 'process')
            ),
        );

        $handlers['export_product'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\export\\Product', 'process')
            ),
        );

        $handlers['import_state'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\State', 'process')
            ),
        );

        $handlers['import_city'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\City', 'process')
            ),
        );

        $handlers['import_category'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\Category', 'process')
            ),
        );

        $handlers['import_field'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\Field', 'process')
            ),
        );

        $handlers['import_field_value'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\FieldValue', 'process')
            ),
        );

        $handlers['import_user'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\User', 'process')
            ),
        );

        $handlers['import_product'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\Product', 'process')
            ),
        );

        $this->hook->fire('job.handlers', $handlers);
        return $handlers;
    }

}
