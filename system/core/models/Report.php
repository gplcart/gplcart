<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use DateTime;
use gplcart\core\Model;
use gplcart\core\Cache;
use gplcart\core\models\Language as LanguageModel;

/**
 * Manages basic behaviors and data related to system reports
 */
class Report extends Model
{

    /**
     * Cache instance
     * @var \gplcart\core\Cache $cache
     */
    protected $cache;

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param LanguageModel $language
     * @param Cache $cache
     */
    public function __construct(LanguageModel $language, Cache $cache)
    {
        parent::__construct();

        $this->cache = $cache;
        $this->language = $language;
    }

    /**
     * Returns an array of log messages
     * @param array $data
     * @return array|integer
     */
    public function getList(array $data = array())
    {
        $sql = 'SELECT *';

        if (!empty($data['count'])) {
            $sql = 'SELECT COUNT(log_id)';
        }

        $sql .= ' FROM log WHERE log_id IS NOT NULL';

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

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'severity', 'time', 'text');

        if ((isset($data['sort']) && in_array($data['sort'], $allowed_sort))//
                && (isset($data['order']) && in_array($data['order'], $allowed_order))) {
            $sql .= " ORDER BY {$data['sort']} {$data['order']}";
        } else {
            $sql .= ' ORDER BY time DESC';
        }

        if (!empty($data['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $data['limit']));
        }

        if (!empty($data['count'])) {
            return (int) $this->db->fetchColumn($sql, $where);
        }

        $options = array('index' => 'log_id', 'unserialize' => 'data');

        $list = $this->db->fetchAll($sql, $where, $options);
        $this->hook->fire('report.list', $list);

        return $list;
    }

    /**
     * Returns an array of log types
     * @return array
     */
    public function getTypes()
    {
        $sql = 'SELECT DISTINCT type FROM log';
        return $this->db->fetchColumnAll($sql, array());
    }

    /**
     * Returns an array of severities
     * @return array
     */
    public function getSeverities()
    {
        return array(
            'info' => $this->language->text('Info'),
            'danger' => $this->language->text('Danger'),
            'warning' => $this->language->text('Warning')
        );
    }

    /**
     * Returns an array of totals per severity type
     * @return array
     */
    public function countSeverity()
    {
        $sql = "SELECT"
                . " SUM(severity = 'danger') AS danger,"
                . " SUM(severity = 'warning') AS warning,"
                . " SUM(severity = 'info') AS info"
                . " FROM log";

        $result = $this->db->fetchAll($sql, array());
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
        $sql = 'DELETE FROM log WHERE log_id IN(' . $placeholders . ')';

        $this->db->run($sql);
        return true;
    }

    /**
     * Deletes expired logs
     * @param integer $interval
     */
    public function deleteExpired($interval)
    {
        $time = (GC_TIME - (int) $interval);
        $this->db->run('DELETE FROM log WHERE time < ?', array($time));
    }

    /**
     * Clears Google Analytics cache
     * @param string $profile_id
     */
    public function clearGaCache($profile_id)
    {
        $this->cache->clear("ga.$profile_id.", array('pattern' => '*'));
    }

    /**
     * Returns an array of chart data
     * @param object $analytics
     * @return array
     */
    public function buildTrafficChart(\gplcart\core\models\Analytics $analytics)
    {
        $results = $analytics->get('traffic');

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
            'data' => $traffic_data[0],
        );

        $build['datasets'][1] = array(
            'label' => 'pageviews',
            'data' => $traffic_data[1],
        );

        $build['options'] = array(
            'responsive' => true,
            'maintainAspectRatio' => false);

        return $build;
    }

    /**
     * Returns an array of system statuses
     * @return array
     */
    public function getStatus()
    {
        $statuses = array();

        $statuses['core_version'] = array(
            'title' => $this->language->text('Core version'),
            'description' => '',
            'severity' => 'info',
            'status' => GC_VERSION,
            'weight' => 0,
        );

        $statuses['database_version'] = array(
            'title' => $this->language->text('Database version'),
            'description' => '',
            'severity' => 'info',
            'status' => $this->db->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'weight' => 1,
        );

        $statuses['php_version'] = array(
            'title' => $this->language->text('PHP version'),
            'description' => '',
            'severity' => 'info',
            'status' => PHP_VERSION,
            'weight' => 2,
        );

        $statuses['php_os'] = array(
            'title' => $this->language->text('PHP operating system'),
            'description' => '',
            'severity' => 'info',
            'status' => PHP_OS,
            'weight' => 3,
        );

        $statuses['php_memory_limit'] = array(
            'title' => $this->language->text('PHP Memory Limit'),
            'description' => '',
            'severity' => 'info',
            'status' => ini_get('memory_limit'),
            'weight' => 4,
        );

        $statuses['php_apc_enabled'] = array(
            'title' => $this->language->text('PHP APC cache enabled'),
            'description' => '',
            'severity' => 'info',
            'status' => ini_get('apc.enabled') ? $this->language->text('Yes') : $this->language->text('No'),
            'weight' => 5,
        );

        $statuses['server_software'] = array(
            'title' => $this->language->text('Server software'),
            'description' => '',
            'severity' => 'info',
            'status' => $_SERVER['SERVER_SOFTWARE'],
            'weight' => 6
        );

        $date_format = $this->config->get('date_prefix', 'd.m.Y');
        $date_format .= $this->config->get('date_suffix', ' H:i');

        $statuses['cron'] = array(
            'title' => $this->language->text('Cron last run'),
            'description' => '',
            'severity' => 'info',
            'status' => date($date_format, $this->config->get('cron_last_run')),
            'weight' => 7,
        );

        $filesystem = $this->checkFilesystem();

        $statuses['filesystem'] = array(
            'title' => $this->language->text('Filesystem is protected'),
            'description' => '',
            'severity' => 'danger',
            'status' => ($filesystem === true) ? $this->language->text('Yes') : $this->language->text('No'),
            'details' => ($filesystem === true) ? array() : $filesystem,
            'weight' => 8,
        );

        $this->hook->fire('report.statuses', $statuses);

        gplcart_array_sort($statuses);

        return $statuses;
    }

    /**
     * Checks filesystem. Returns true if no issues found or an array of errors
     * @return boolean|array
     */
    public function checkFilesystem()
    {
        $results = array(
            $this->checkPermissions(GC_CONFIG_COMMON),
            $this->checkPermissions(GC_CONFIG_DATABASE)
        );

        if (file_exists(GC_CONFIG_OVERRIDE)) {
            $results[] = $this->checkPermissions(GC_CONFIG_OVERRIDE);
        }

        $filtered = array_filter($results, 'is_string');

        if (empty($filtered)) {
            return true;
        }

        return $filtered;
    }

    /**
     * Checks file permissions
     * @param string $file
     * @param string $permissions
     * @return boolean|array
     */
    protected function checkPermissions($file, $permissions = '0444')
    {
        if (substr(sprintf('%o', fileperms($file)), -4) === (string) $permissions) {
            return true;
        }

        $vars = array('%s' => $file, '%perm' => $permissions);
        return $this->language->text('File %s is not secure. The file permissions must be %perm', $vars);
    }

}
