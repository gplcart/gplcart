<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use core\Model;
use core\Logger;
use core\classes\Cache;

/**
 * Manages basic behaviors and data related to Google Analytics
 */
class Analytics extends Model
{

    /**
     * GA service class instance
     * @var \Google_Service_Analytics $service
     */
    protected $service;

    /**
     * GA profile id
     * @var type
     */
    protected $profile_id;

    /**
     * GA client class instance
     * @var \Google_Client $client
     */
    protected $client;

    /**
     * GA credentials class instance
     * @var \Google_Auth_AssertionCredentials $credentials
     */
    protected $credentials;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        parent::__construct();

        $this->logger = $logger;
        require GC_LIBRARY_DIR . '/gapi/src/Google/autoload.php';
    }

    /**
     * Sets credentials for GoogleAPI
     * @param string $email
     * @param string $certificate
     * @param string $app_name
     * @return object \core\models\Analytics
     */
    public function setCredentials($email, $certificate, $app_name)
    {
        $key_file = GC_FILE_DIR . '/' . $certificate;

        $this->client = new \Google_Client();
        $this->client->setApplicationName($app_name);
        $this->service = new \Google_Service_Analytics($this->client);

        $key = file_get_contents($key_file);

        $this->credentials = new \Google_Auth_AssertionCredentials(
                $email, array(\Google_Service_Analytics::ANALYTICS_READONLY), $key
        );

        $this->client->setAssertionCredentials($this->credentials);
        if ($this->client->getAuth()->isAccessTokenExpired()) {
            $this->client->getAuth()->refreshTokenWithAssertion($this->credentials);
        }

        return $this;
    }

    /**
     * Sets a Google Analytics view to work with
     * @param string $view
     * @return object \core\models\Analytics
     */
    public function setView($view)
    {
        $this->profile_id = $view;
        return $this;
    }

    /**
     * Returns an array of GA software statistic
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getSoftware($from = '30daysAgo', $to = 'today')
    {
        $dimensions = array(
            'ga:operatingSystem',
            'ga:operatingSystemVersion',
            'ga:browser',
            'ga:browserVersion'
        );

        $arguments = array($from, $to, 'ga:sessions', array(
                'dimensions' => implode(',', $dimensions),
                'sort' => '-ga:sessions',
        ));

        return $this->getResults($arguments);
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
        $cid = "ga.{$this->profile_id}." . md5(serialize($arguments));
        $cache = Cache::get($cid, null, $this->config->get('ga_cache_lifespan', 86400));

        if (isset($cache)) {
            $return = $cache;
        } else {

            try {
                $results = call_user_func_array(array($this->service->data_ga, 'get'), $arguments);
                $rows = $results->getRows();
            } catch (\Google_IO_Exception $e) {
                $this->logger->log('ga', $e->getMessage(), 'warning'); // Failed to connect, etc...
                return array();
            }

            if (!empty($rows)) {
                $return = $rows;
            }

            Cache::set($cid, $return);

            $log = array(
                'message' => 'Google Analytics for profile %s has updated',
                'variables' => array('%s' => $this->profile_id)
            );

            $this->logger->log('ga', $log);
        }

        $this->hook->fire('ga.results.after', $arguments, $return);
        return $return;
    }

    /**
     * Returns an array of GA popular pages statistic
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getTopPages($from = '30daysAgo', $to = 'today')
    {
        $fields = array(
            'ga:pageviews',
            'ga:uniquePageviews',
            'ga:timeOnPage',
            'ga:bounces',
            'ga:entrances',
            'ga:exits'
        );

        $arguments = array($from, $to, implode(',', $fields), array(
                'dimensions' => 'ga:hostname, ga:pagePath', 'sort' => '-ga:pageviews',
        ));

        return $this->getResults($arguments);
    }

    /**
     * Returns an array of GA source statistic
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getSources($from = '30daysAgo', $to = 'today')
    {
        $fields = array(
            'ga:sessions',
            'ga:pageviews',
            'ga:sessionDuration',
            'ga:exits'
        );

        $arguments = array($from, $to, implode(',', $fields), array(
                'dimensions' => 'ga:source,ga:medium', 'sort' => '-ga:sessions',
        ));

        return $this->getResults($arguments);
    }

    /**
     * Returns an array of GA keyword statistic
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getKeywords($from = '30daysAgo', $to = 'today')
    {
        $arguments = array($from, $to, 'ga:sessions', array(
                'dimensions' => 'ga:keyword', 'sort' => '-ga:sessions',
        ));

        return $this->getResults($arguments);
    }

    /**
     * Returns an array of GA traffic statistic
     * @param string $from
     * @param string $to
     * @return array
     */
    public function getTraffic($from = '30daysAgo', $to = 'today')
    {
        $fields = array('ga:sessions', 'ga:pageviews');
        $arguments = array($from, $to, implode(',', $fields), array('dimensions' => 'ga:date'));
        return $this->getResults($arguments);
    }

}
