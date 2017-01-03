<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\Logger;
use gplcart\core\Handler;
use gplcart\core\Library;

/**
 * Manages basic behaviors and data related to Google Analytics
 */
class Analytics extends Model
{

    /**
     * Cache instance
     * @var \gplcart\core\Cache $cache
     */
    protected $cache;

    /**
     * Library instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * GA service class instance
     * @var object
     */
    protected $service;

    /**
     * GA profile id
     * @var integer
     */
    protected $profile_id;

    /**
     * GA client class instance
     * @var object
     */
    protected $client;

    /**
     * GA credentials class instance
     * @var object
     */
    protected $credentials;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Cache $cache
     * @param Logger $logger
     * @param Library $library
     */
    public function __construct(Cache $cache, Logger $logger, Library $library)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->logger = $logger;
        $this->library = $library;
    }

    /**
     * Sets credentials for GoogleAPI
     * @param string $email
     * @param string $certificate
     * @param string $app_name
     * @return \gplcart\core\models\Analytics
     */
    public function setCredentials($email, $certificate, $app_name)
    {
        $this->library->load('google_api');
        
        $this->client = new \Google_Client;
        $this->client->setApplicationName($app_name);
        $this->service = new Google_Service_Analytics($this->client);

        $key = file_get_contents(GC_FILE_DIR . "/$certificate");

        try {
            
            //@todo Fix removed lib methods

            $this->credentials = new \Google_Auth_AssertionCredentials($email, array(\Google_Service_Analytics::ANALYTICS_READONLY), $key);
            $this->client->setAssertionCredentials($this->credentials);

            if ($this->client->getAuth()->isAccessTokenExpired()) {
                $this->client->getAuth()->refreshTokenWithAssertion($this->credentials);
            }

            return $this;
        } catch (\Google_Auth_Exception $e) {
            $this->logger->log('ga', $e->getMessage(), 'danger', false);
        }

        return $this;
    }

    /**
     * Sets a Google Analytics view to work with
     * @param integer $view
     * @return \gplcart\core\models\Analytics
     */
    public function setView($view)
    {
        $this->profile_id = $view;
        return $this;
    }

    /**
     * Returns a statistic for a given handler ID
     * @param string $handler_id
     * @param array $arguments
     * @return array
     */
    public function get($handler_id, array $arguments = array())
    {
        $this->hook->fire('ga.get.before', $handler_id, $arguments);

        $handlers = $this->getHandlers();

        if (empty($handlers[$handler_id])) {
            return array();
        }

        $arguments += array(
            $this->config->get('ga_from', '30daysAgo'),
            $this->config->get('ga_until', 'today'),
            $this->config->get('ga_limit', 20)
        );

        $query = Handler::call($handlers, $handler_id, 'query', $arguments);

        if (empty($query)) {
            return array();
        }

        $results = $this->getResults($query);
        $this->hook->fire('ga.get.after', $handler_id, $arguments, $results);

        return $results;
    }

    /**
     * Returns an array of GA handlers
     * @return array
     */
    protected function getHandlers()
    {
        $handlers = &Cache::memory('ga.handlers');

        if (isset($handlers)) {
            return $handlers;
        }

        $handlers = array();

        $handlers['traffic'] = array(
            'handlers' => array(
                'query' => array('gplcart\\core\\handlers\\ga\\Query', 'traffic')
            ),
        );

        $handlers['keywords'] = array(
            'handlers' => array(
                'query' => array('gplcart\\core\\handlers\\ga\\Query', 'keywords')
            ),
        );

        $handlers['sources'] = array(
            'handlers' => array(
                'query' => array('gplcart\\core\\handlers\\ga\\Query', 'sources')
            ),
        );

        $handlers['top_pages'] = array(
            'handlers' => array(
                'query' => array('gplcart\\core\\handlers\\ga\\Query', 'topPages')
            ),
        );

        $handlers['software'] = array(
            'handlers' => array(
                'query' => array('gplcart\\core\\handlers\\ga\\Query', 'software')
            ),
        );

        $this->hook->fire('ga.handlers', $handlers);

        return $handlers;
    }

    /**
     * Caches and returns the formatted data array
     * @param array $arguments
     * @return mixed
     */
    public function getResults(array $arguments)
    {
        if (empty($this->profile_id)) {
            return false;
        }

        array_unshift($arguments, 'ga:' . $this->profile_id);

        $this->hook->fire('ga.results.before', $arguments);

        $return = array();
        $lifespan = $this->config->get('ga_cache_lifespan', 86400);
        $cid = array("ga.{$this->profile_id}" => $arguments);

        $cache = $this->cache->get($cid, array('lifespan' => $lifespan));

        if (isset($cache)) {
            $return = $cache;
        } else {

            try {
                $results = call_user_func_array(array($this->service->data_ga, 'get'), $arguments);
                $rows = $results->getList();
            } catch (\Google_IO_Exception $e) {
                $this->logger->log('ga', $e->getMessage(), 'danger', false); // Failed to connect, etc...
                return array();
            }

            if (!empty($rows)) {
                $return = $rows;
            }

            $this->cache->set($cid, $return);

            $log = array(
                'message' => 'Google Analytics for profile %s has updated',
                'variables' => array('%s' => $this->profile_id)
            );

            $this->logger->log('ga', $log);
        }

        $this->hook->fire('ga.results.after', $arguments, $return);
        return $return;
    }

}
