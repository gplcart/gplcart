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
use core\classes\Tool;
use core\classes\Cache;
use core\models\Module as ModelsModule;
use core\models\Language as ModelsLanguage;

/**
 * Manages basic behaviors and data related to various reports
 */
class Report extends Model
{

    /**
     * Module model instance
     * @var \core\models\Module $module
     */
    protected $module;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Constructor
     * @param ModelsModule $module
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsModule $module, ModelsLanguage $language)
    {
        parent::__construct();

        $this->module = $module;
        $this->language = $language;
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
            return (int) $sth->fetchColumn();
        }

        $list = array();
        foreach ($sth->fetchAll(PDO::FETCH_ASSOC) as $record) {
            $record['data'] = unserialize($record['data']);
            $list[$record['log_id']] = $record;
        }

        $this->hook->fire('report.list', $list);

        return $list;
    }

    /**
     * Returns an array of log types
     * @return array
     */
    public function getTypes()
    {
        return $this->db->query('SELECT DISTINCT type FROM log')
                        ->fetchAll(PDO::FETCH_COLUMN);
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
     * Returns PHP info as a string
     * @return string
     */
    public function phpinfo()
    {
        ob_start();
        phpinfo(INFO_MODULES);
        $result = ob_get_clean();

        // remove auth data
        if (isset($_SERVER['AUTH_USER'])) {
            $result = str_replace($_SERVER['AUTH_USER'], '***', $$result);
        }

        if (isset($_SERVER['AUTH_PASSWORD'])) {
            $result = str_replace($_SERVER['AUTH_PASSWORD'], '***', $result);
        }

        $result = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $result);
        $result = str_replace('<table', '<table class="table"', $result);

        return $result;
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
            'status' => $this->db->getAttribute(PDO::ATTR_SERVER_VERSION),
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
            'status' => ini_get('apc.enabled'),
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

        $statuses['filesystem'] = array(
            'title' => $this->language->text('Filesystem is protected'),
            'description' => '',
            'severity' => 'danger',
            'status' => $this->checkFilesystem(),
            'weight' => 8,
        );

        $this->hook->fire('report.statuses', $statuses);

        Tool::sortWeight($statuses);

        return $statuses;
    }

    /**
     * Checks filesystem. Returns true if no issues found or an array of errors
     * @return boolean|array
     */
    public function checkFilesystem()
    {
        $results[] = $this->checkPermissions(GC_CONFIG_COMMON);

        if (file_exists(GC_CONFIG_OVERRIDE)) {
            $results[] = $this->checkPermissions(GC_CONFIG_OVERRIDE);
        }

        $directories = array(GC_ROOT_DIR, GC_CACHE_DIR, GC_PRIVATE_DIR, GC_FILE_DIR);

        foreach ($directories as $directory) {
            $private = ($directory !== GC_FILE_DIR);
            $results[] = $this->checkHtaccess($directory, $private);
        }

        $results = array_filter($results, 'is_string');

        if (empty($results)) {
            return true;
        }

        return $results;
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

        return $this->language->text('File %s is not secure. The file permissions must be %perm', array(
                    '%s' => $file,
                    '%perm' => $permissions));
    }

    /**
     * Checks permissions and existance of .htaccess file
     * @param string $directory
     * @param boolean $private
     * @return boolean|array
     */
    protected function checkHtaccess($directory, $private = true)
    {
        $htaccess = $directory . '/.htaccess';

        if (file_exists($htaccess)) {
            return $this->checkPermissions($htaccess);
        }

        // Try to create the missing file
        if (Tool::htaccess($directory, $private)) {
            return true;
        }

        return $this->language->text('Missing .htaccess file %s', array(
                    '%s' => $htaccess));
    }

}
