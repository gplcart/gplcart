<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\models;

use core\Hook;
use core\Handler;
use core\classes\Session;
use core\classes\Tool;
use core\classes\Cache;
use core\classes\Url as U;
use core\models\Language;
use core\models\Queue;

class Job
{

    const JOB_MAX_TIME = 1000;
    const JOB_SESSION_KEY = 'jobs';

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Queue model instance
     * @var \core\models\Queue $queue
     */
    protected $queue;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Constructor
     * @param Language $language
     * @param Queue $queue
     * @param Session $session
     * @param Hook $hook
     * @param U $url
     */
    public function __construct(Language $language, Queue $queue, Session $session, Hook $hook, U $url)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->queue = $queue;
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
     * Returns a job from the session
     * @param string $job_id
     * @return array
     */
    protected function getSession($job_id)
    {
        return $this->session->get(self::JOB_SESSION_KEY, $job_id, array());
    }

    /**
     * Sets a job to the session
     * @param array $job
     * @return boolean
     */
    protected function setSession(array $job)
    {
        return $this->session->set(self::JOB_SESSION_KEY, $job['id'], $job);
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

        $default = array(
            'id' => uniqid(),
            'status' => true,
            'title' => '',
            'widget' => '',
            'url' => '',
            'total' => 0,
            'errors' => 0,
            'inserted' => 0,
            'updated' => 0,
            'context' => array(),
            'operations' => array(),
            'message' => array(
                'start' => $this->language->text('Starting'),
                'process' => $this->language->text('Processing'),
                'finish' => $this->language->text('Finished')
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

        $job = Tool::merge($default, $job);

        $existing = $this->getSession($job['id']);

        if ($existing && !empty($existing['operations'])) {
            return $existing;
        }

        foreach ($job['operations'] as $operation_id => &$operation) {
            if (empty($operation['queue'])) {
                continue;
            }

            if (empty($job['total'])) {
                $job['total'] = count($operation['queue']);
            }

            $queue = array('queue_id' => $operation_id, 'total' => $job['total'], 'status' => 1);
            $this->queue->set($queue, $operation['queue'], true);

            // Don't keep large arrays in the session
            unset($job['operations'][$operation_id]['queue']);
        }

        $this->setSession($job);
        $this->hook->fire('set.job.after', $job, $job['id']);

        return $job;
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

        $this->session->delete(self::JOB_SESSION_KEY, $job_id);
        $this->hook->fire('delete.job.after', $job_id);

        return true;
    }

    /**
     * Processes one job iteration (AJAX request)
     * @staticvar int $done
     * @staticvar int $errors
     * @staticvar array $context
     * @param array $job
     * @return array
     */
    public function process(array $job)
    {
        register_shutdown_function(array($this, 'shutdownHandler'), $job);
        ini_set('max_execution_time', 0);

        $this->hook->fire('process.job.before', $job);

        if (empty($job['status'])) {
            return $this->result($job, array('finish' => true));
        }

        if (empty($job['operations'])) {
            $job['status'] = false;

            return $this->result($job, array('finish' => true, 'progress' => 100));
        }

        $operation = reset($job['operations']);
        $operation_id = key($job['operations']);

        $arguments = !empty($operation['arguments']) ? (array) $operation['arguments'] : array();

        static $done = 0;
        static $errors = 0;
        static $inserted = 0;
        static $updated = 0;
        static $context = array();

        $progress = 0;
        $total = (int) $job['total'];
        $message = $job['message']['process'];

        if (isset($job['done'][$operation_id])) {
            $done = (int) $job['done'][$operation_id];
        }

        if (isset($job['context'])) {
            $context = $job['context'];
        }

        $handlers = $this->getHandlers();

        $start_time = microtime(true);

        while (round((microtime(true) - $start_time) * 1000, 2) < self::JOB_MAX_TIME) {
            $args = array_merge(array($job, $operation_id, $done, $context), $arguments);
            $result = Handler::call($handlers, $operation_id, 'process', $args);

            if (isset($result['done'])) {
                if (isset($result['increment']) && !$result['increment']) {
                    $done = (int) $result['done'];
                } else {
                    $done += (int) $result['done'];
                }
            }

            if (isset($result['errors'])) {
                $errors += (int) $result['errors'];
            }

            if (isset($result['context']) && is_array($result['context'])) {
                $context = Tool::merge($context, $result['context']);
            }

            if (isset($result['message'])) {
                $message = $result['message'];
            }

            if (isset($result['inserted'])) {
                $inserted += (int) $result['inserted'];
            }

            if (isset($result['updated'])) {
                $updated += (int) $result['updated'];
            }

            $progress = round($done * 100 / $total);

            if ($done < $total) {
                continue;
            }

            if (isset($job['operations'][$operation_id]['queue'])) {
                $this->queue->delete($operation_id);
            }

            unset($job['operations'][$operation_id]);
            break;
        }

        $job['done'][$operation_id] = $done;
        $job['errors'] += $errors;
        $job['inserted'] += $inserted;
        $job['updated'] += $updated;
        $job['context'] = $context;

        $return = $this->result($job, array(
            'done' => $done,
            'errors' => $errors,
            'progress' => $progress,
            'message' => $message
        ));

        return $return;
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
            'finish' => false,
            'errors' => 0,
            'progress' => 0,
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

        return;
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

        $handlers['import_option_combination'] = array(
            'handlers' => array(
                'process' => array('core\\handlers\\job\\import\\Combination', 'process')
            ),
        );

        $this->hook->fire('job.handlers', $handlers);

        return $handlers;
    }

    /**
     * Shutdown handler
     * @param array $job
     */
    public function shutdownHandler(array $job)
    {
        $error = error_get_last();
        if (isset($error['type']) && $error['type'] === E_ERROR) {
            $this->session->setMessage($this->language->text('An unexpected error has occurred. The job has not been properly completed'), 'danger');
        }
    }
}
