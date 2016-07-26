<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\models;

use PDO;
use DateTime;
use core\Model;
use core\classes\Curl;
use core\classes\Cache;
use core\models\Module as ModelsModule;

/**
 * Manages basic behaviors and data related to various reports
 */
class Report extends Model
{

    /**
     * Module class instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * CURL class instance
     * @var \core\classes\Curl $curl
     */
    protected $curl;

    /**
     * Constructor
     * @param ModelsModule $module
     * @param Curl $curl
     */
    public function __construct(ModelsModule $module, Curl $curl)
    {
        parent::__construct();

        $this->curl = $curl;
        $this->module = $module;
    }

    /**
     * Returns an array of log messages
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT * ';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(log_id) ';
        }

        $sql .= 'FROM log WHERE log_id IS NOT NULL';

        $where = array();

        if (isset($data['severity'])) {
            $sql .= " AND severity=?";
            $where[] = $data['severity'];
        }

        if (isset($data['type'])) {
            $types = (array) $data['type'];
            $placeholders = rtrim(str_repeat('?, ', count($types)), ', ');
            $sql .= ' AND type IN(' . $placeholders . ')';
            $where = array_merge($where, $types);
        }

        if (isset($data['text'])) {
            $sql .= " AND text LIKE ?";
            $where[] = "%{$data['text']}%";
        }

        if (isset($data['sort']) && (isset($data['order']) && in_array($data['order'], array('asc', 'desc'), true))) {
            switch ($data['sort']) {
                case 'type':
                    $sql .= " ORDER BY type {$data['order']}";
                    break;
                case 'severity':
                    $sql .= " ORDER BY severity {$data['order']}";
                    break;
                case 'time':
                    $sql .= " ORDER BY time {$data['order']}";
                    break;
                case 'text':
                    $sql .= " ORDER BY text {$data['order']}";
                    break;
            }
        } else {
            $sql .= ' ORDER BY time DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        $sth = $this->db->prepare($sql);
        $sth->execute($where);

        if (!empty($data['count'])) {
            return $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $record) {
            $record['data'] = unserialize($record['data']);
            $list[$record['log_id']] = $record;
        }

        return $list;
    }

    /**
     * Returns an array of log types
     * @return array
     */
    public function getTypes()
    {
        return $this->db->query('SELECT DISTINCT type FROM log')->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Returns an array of totals per severity type
     * @return array
     */
    public function countSeverity()
    {
        $sql = "
        SELECT
        SUM(severity = 'danger') AS danger,
        SUM(severity = 'warning') AS warning,
        SUM(severity = 'info') AS info
        FROM log";

        $result = $this->db->query($sql)->fetchAll();
        return reset($result);
    }

    /**
     * Clears log records
     * @param array $error_types
     * @return boolean
     */
    public function clear(array $error_types = array())
    {
        if (empty($error_types)) {
            return (bool) $this->db->query('DELETE FROM log');
        }

        $placeholders = rtrim(str_repeat('?, ', count($error_types)), ', ');
        $sth = $this->db->prepare('DELETE FROM log WHERE log_id IN(' . $placeholders . ')');
        $sth->execute($error_types);
        return true;
    }

    /**
     * Deletes expired logs
     * @param integer $interval
     */
    public function clearExpired($interval)
    {
        $sth = $this->db->prepare('DELETE FROM log WHERE time < :time');
        $sth->execute(array(':time' => (GC_TIME - (int) $interval)));
    }

    /**
     * Clears Google Analytics cache
     * @param string $profile_id
     */
    public function clearGaCache($profile_id)
    {
        Cache::clear("ga.$profile_id.", '*.cache');
    }

    /**
     * Returns an array of chart data
     * @param object $analytics
     * @return array
     */
    public function buildTrafficChart(\core\models\Analytics $analytics)
    {
        $results = $analytics->getTraffic();

        if (empty($results)) {
            return array();
        }

        $build = array();
        $traffic_data = array();

        foreach ($results as $row => $values) {
            $date = DateTime::createFromFormat('Ymd', $values[0]);
            $build['labels'][$row] = $date->format('M j');
            $traffic_data[0][$row] = $values[1];
            $traffic_data[1][$row] = $values[2];
        }

        $build['datasets'][0] = array(
            'label' => 'sessions',
            'fillColor' => 'rgba(220,220,220,0.2)',
            'strokeColor' => 'rgba(220,220,220,1)',
            'pointColor' => 'rgba(220,220,220,1)',
            'pointStrokeColor' => '#fff',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(220,220,220,1)',
            'data' => $traffic_data[0],
        );

        $build['datasets'][1] = array(
            'label' => 'pageviews',
            'fillColor' => 'rgba(151,187,205,0.2)',
            'strokeColor' => 'rgba(151,187,205,1)',
            'pointColor' => 'rgba(151,187,205,1)',
            'pointStrokeColor' => '#fff',
            'pointHighlightFill' => '#fff',
            'pointHighlightStroke' => 'rgba(151,187,205,1)',
            'data' => $traffic_data[1],
        );

        $build['options'] = array('bezierCurve' => false, 'responsive' => true);

        return $build;
    }

    /**
     * Returns the system environment info
     * @return array
     */
    public function getEnvironmentInfo()
    {
        return array(
            'php' => $this->phpinfo(),
            'system' => array(
                'version' => GC_VERSION,
                'modules' => $this->module->getEnabled(),
            ),
        );
    }

    /**
     * Returns an array of PHP errors
     * @param integer $limit
     * @return array
     */
    public function getPhpErrors($limit = 100)
    {
        $errors = $this->getList(array(
            'limit' => array(0, $limit),
            'sort' => 'time',
            'order' => 'desc',
            'type' => array('php_error', 'php_shutdown')));

        return $errors;
    }

    /**
     * Sends error reporting to remoted endpoint
     * @param array $errors
     * @param boolean $clear
     * @return boolean
     */
    public function reportErrors(array $errors, $clear = true)
    {
        if (empty($errors)) {
            return false;
        }

        $data = array(
            'environment' => $this->getEnvironmentInfo(),
            'errors' => $errors
        );

        $result = $this->curl->post(GC_REPORT_URL, array(CURLOPT_POSTFIELDS => http_build_query($data)));

        if ($clear && !empty($result)) {
            $this->clear(array_keys($errors));
        }

        return (boolean) $result;
    }

    /**
     * Returns PHP info as a string
     * @return string
     */
    public function phpinfo()
    {
        ob_start();
        phpinfo(INFO_MODULES);
        $result = ob_get_contents();
        ob_end_clean();

        // remove auth data
        if (isset($_SERVER['AUTH_USER'])) {
            $result = str_replace($_SERVER['AUTH_USER'], '***', $$result);
        }

        if (isset($_SERVER['AUTH_PASSWORD'])) {
            $result = str_replace($_SERVER['AUTH_PASSWORD'], '***', $result);
        }

        return $result;
    }

}
