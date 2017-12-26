<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook,
    gplcart\core\Config;
use gplcart\core\models\Translation as TranslationModel;

/**
 * Manages basic behaviors and data related to system reports
 */
class Report
{

    /**
     * Database class instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

    /**
     * @param Hook $hook
     * @param Config $config
     * @param Translation $translation
     */
    public function __construct(Hook $hook, Config $config, TranslationModel $translation)
    {
        $this->hook = $hook;
        $this->config = $config;
        $this->db = $this->config->getDb();
        $this->translation = $translation;
    }

    /**
     * Returns an array of log messages
     * @param array $options
     * @return array|integer
     */
    public function getList(array $options = array())
    {
        $result = null;
        $this->hook->attach('report.event.list.before', $options, $result, $this);

        if (isset($result)) {
            return $result;
        }

        $sql = 'SELECT *';

        if (!empty($options['count'])) {
            $sql = 'SELECT COUNT(log_id)';
        }

        $sql .= ' FROM log WHERE log_id IS NOT NULL';

        $conditions = array();

        if (isset($options['severity'])) {
            $sql .= " AND severity=?";
            $conditions[] = $options['severity'];
        }

        if (isset($options['type'])) {
            settype($options['type'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($options['type'])), ',');
            $sql .= " AND type IN($placeholders)";
            $conditions = array_merge($conditions, $options['type']);
        }

        if (isset($options['text'])) {
            $sql .= " AND text LIKE ?";
            $conditions[] = "%{$options['text']}%";
        }

        $allowed_order = array('asc', 'desc');
        $allowed_sort = array('type', 'severity', 'time', 'text');

        if ((isset($options['sort']) && in_array($options['sort'], $allowed_sort))//
                && (isset($options['order']) && in_array($options['order'], $allowed_order))) {
            $sql .= " ORDER BY {$options['sort']} {$options['order']}";
        } else {
            $sql .= ' ORDER BY time DESC';
        }

        if (!empty($options['limit'])) {
            $sql .= ' LIMIT ' . implode(',', array_map('intval', $options['limit']));
        }

        if (empty($options['count'])) {
            $result = $this->db->fetchAll($sql, $conditions, array('index' => 'log_id', 'unserialize' => 'data'));
        } else {
            $result = (int) $this->db->fetchColumn($sql, $conditions);
        }

        $this->hook->attach('report.event.list.after', $options, $result, $this);
        return $result;
    }

    /**
     * Delete log records
     * @param array $data
     * @return boolean
     */
    public function delete(array $data = array())
    {
        $sql = 'DELETE FROM log';

        $conditions = array();

        if (empty($data['log_id'])) {
            $sql .= " WHERE log_id IS NOT NULL";
        } else {
            settype($data['log_id'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['log_id'])), ',');
            $sql .= " WHERE log_id IN($placeholders)";
            $conditions = array_merge($conditions, $data['log_id']);
        }

        if (!empty($data['type'])) {
            settype($data['type'], 'array');
            $placeholders = rtrim(str_repeat('?,', count($data['type'])), ',');
            $sql .= " AND type IN($placeholders)";
            $conditions = array_merge($conditions, $data['type']);
        }

        $this->db->run($sql, $conditions);
        return true;
    }

    /**
     * Deletes expired logs
     */
    public function deleteExpired()
    {
        $time = GC_TIME - $this->getExpiredLogLifespan();
        $this->db->run('DELETE FROM log WHERE time < ?', array($time));
    }

    /**
     * Max number of seconds to keep logs in the database
     * @return integer
     */
    public function getExpiredLogLifespan()
    {
        return (int) $this->config->get('report_log_lifespan', 24 * 60 * 60);
    }

    /**
     * Returns an array of system statuses
     * @return array
     */
    public function getStatus()
    {
        $statuses = array();

        $statuses['core_version'] = array(
            'title' => $this->translation->text('Core version'),
            'description' => '',
            'severity' => 'info',
            'status' => gplcart_version(),
            'weight' => 0,
        );

        $statuses['database_version'] = array(
            'title' => $this->translation->text('Database version'),
            'description' => '',
            'severity' => 'info',
            'status' => $this->db->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION),
            'weight' => 1,
        );

        $statuses['php_version'] = array(
            'title' => $this->translation->text('PHP version'),
            'description' => '',
            'severity' => 'info',
            'status' => PHP_VERSION,
            'weight' => 2,
        );

        $statuses['php_os'] = array(
            'title' => $this->translation->text('PHP operating system'),
            'description' => '',
            'severity' => 'info',
            'status' => PHP_OS,
            'weight' => 3,
        );

        $statuses['php_memory_limit'] = array(
            'title' => $this->translation->text('PHP Memory Limit'),
            'description' => '',
            'severity' => 'info',
            'status' => ini_get('memory_limit'),
            'weight' => 4,
        );

        $statuses['php_apc_enabled'] = array(
            'title' => $this->translation->text('PHP APC cache enabled'),
            'description' => '',
            'severity' => 'info',
            'status' => ini_get('apc.enabled') ? $this->translation->text('Yes') : $this->translation->text('No'),
            'weight' => 5,
        );

        $statuses['server_software'] = array(
            'title' => $this->translation->text('Server software'),
            'description' => '',
            'severity' => 'info',
            'status' => filter_input(INPUT_SERVER, 'SERVER_SOFTWARE', FILTER_SANITIZE_STRING),
            'weight' => 6
        );

        $date_format = $this->config->get('date_prefix', 'd.m.Y');
        $date_format .= $this->config->get('date_suffix', ' H:i');

        $statuses['cron'] = array(
            'title' => $this->translation->text('Cron last run'),
            'description' => '',
            'severity' => 'info',
            'status' => date($date_format, $this->config->get('cron_last_run')),
            'weight' => 7,
        );

        $filesystem = $this->checkFilesystem();

        $statuses['filesystem'] = array(
            'title' => $this->translation->text('Filesystem is protected'),
            'description' => '',
            'severity' => 'danger',
            'status' => $filesystem === true ? $this->translation->text('Yes') : $this->translation->text('No'),
            'details' => $filesystem === true ? array() : $filesystem,
            'weight' => 8,
        );

        $statuses['search_index'] = array(
            'title' => $this->translation->text('Search index'),
            'description' => '',
            'severity' => 'info',
            'status' => $this->translation->text('@num rows', array('@num' => $this->countSearchIndex())),
            'weight' => 9,
        );

        $this->hook->attach('report.statuses', $statuses, $this);

        gplcart_array_sort($statuses);
        return $statuses;
    }

    /**
     * Returns an array of log types
     * @return array
     */
    public function getTypes()
    {
        return $this->db->fetchColumnAll('SELECT DISTINCT type FROM log', array());
    }

    /**
     * Returns an array of log severity types
     * @return array
     */
    public function getSeverities()
    {
        return array(
            'info' => $this->translation->text('Info'),
            'danger' => $this->translation->text('Danger'),
            'warning' => $this->translation->text('Warning')
        );
    }

    /**
     * Checks file system
     * @return boolean|array
     */
    public function checkFilesystem()
    {
        $results = array(
            $this->checkPermissions(GC_FILE_CONFIG_COMPILED)
        );

        if (file_exists(GC_FILE_CONFIG_COMPILED_OVERRIDE)) {
            $results[] = $this->checkPermissions(GC_FILE_CONFIG_COMPILED_OVERRIDE);
        }

        $filtered = array_filter($results, 'is_string');
        return empty($filtered) ? true : $filtered;
    }

    /**
     * Checks file permissions
     * @param string $file
     * @param string $permissions
     * @return boolean|array
     */
    protected function checkPermissions($file, $permissions = '0444')
    {
        if (substr(sprintf('%o', fileperms($file)), -4) === $permissions) {
            return true;
        }

        $vars = array('%name' => $file, '%perm' => $permissions);
        return $this->translation->text('File %name is not secure. The file permissions must be %perm', $vars);
    }

    /**
     * Returns the total number of rows in the search index table
     * @return integer
     */
    protected function countSearchIndex()
    {
        return $this->db->fetchColumn('SELECT COUNT(*) FROM search_index');
    }

}
