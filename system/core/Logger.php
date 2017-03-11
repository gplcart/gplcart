<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Config;

/**
 * Provides methods to log various errors and events
 */
class Logger
{

    /**
     * Collected PHP errors
     * @var array
     */
    protected $php_errors = array();

    /**
     * Database instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Config instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->db = $this->config->getDb();
    }

    /**
     * Writes a log message to the CSV file
     * @param string $file
     * @param string $type
     * @param string $message
     * @param string $severity
     * @param integer $limit
     * @return bool
     */
    public function csv($file, $type, $message, $severity = 'info', $limit = 0)
    {
        $fields = array(date('M d, Y G:iA'), $severity, $type, strip_tags($message));
        return gplcart_file_csv($file, $fields, ',', '"', $limit);
    }

    /**
     * Writes a log message to the database
     * @param string $type
     * @param array|string $data
     * @param string $severity
     * @param boolean $translatable
     * @return boolean
     */
    public function log($type, $data, $severity = 'info', $translatable = true)
    {
        if (empty($this->db)) {
            return false;
        }

        $message = '';

        if (is_string($data)) {
            $message = $data;
        } elseif (isset($data['message'])) {
            $message = $data['message'];
            unset($data['message']);
        }

        $values = array(
            'text' => $message,
            'data' => (array) $data,
            'translatable' => $translatable,
            'type' => mb_substr($type, 0, 255, 'UTF-8'),
            'severity' => mb_substr($severity, 0, 255, 'UTF-8')
        );

        return $this->add($values);
    }

    /**
     * Adds a log record
     * @param array $data
     * @return bool
     */
    public function add(array $data)
    {
        $data += array(
            'time' => GC_TIME,
            'log_id' => gplcart_string_random(6)
        );

        return (bool) $this->db->insert('log', $data);
    }

    /**
     * Counts all PHP errors
     * @return integer
     */
    public function countPhpErrors()
    {
        if (empty($this->db)) {
            return 0;
        }

        $sql = "SELECT COUNT(*) FROM log WHERE type LIKE ?";
        return (int) $this->db->fetchColumn($sql, array('php_%'));
    }

    /**
     * Error handler
     * @param integer $code
     * @param string $message
     * @param string $file
     * @param string $line
     */
    public function errorHandler($code, $message, $file = '', $line = '')
    {
        $error = array(
            'code' => $code,
            'file' => $file,
            'line' => $line,
            'message' => $message
        );

        // Get a unique key for every error to check if it has already caught
        $key = md5(json_encode($error));

        if (isset($this->php_errors[$key])) {
            return null; // Don't log the same error again
        }

        $this->log('php_error', $error, 'warning', false);
        $this->php_errors[$key] = $this->getFormattedError($error);
    }

    /**
     * Shutdown handler
     */
    public function shutdownHandler()
    {
        $error = error_get_last();
        $types = $this->getFatalErrorTypes();

        if (in_array($error['type'], $types)) {
            $error['code'] = $error['type'];
            $this->log('php_shutdown', $error, 'danger', false);
        }
    }

    /**
     * Returns an array of PHP error types which to be considered fatal
     * @return array
     */
    protected function getFatalErrorTypes()
    {
        return array(
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_USER_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_RECOVERABLE_ERROR,
        );
    }

    /**
     * Common exception handler
     */
    public function exceptionHandler($exception)
    {
        $error = $this->getExceptionMessageArray($exception);
        $this->log('php_exception', $error, 'danger', false);

        echo $this->getFormattedError($error, 'PHP Exception');
    }

    /**
     * Returns an array of exception data to be rendered
     * @param object $instance
     * @return array
     */
    protected function getExceptionMessageArray($instance)
    {
        return array(
            'code' => $instance->getCode(),
            'file' => $instance->getFile(),
            'line' => $instance->getLine(),
            'message' => $instance->getMessage()
        );
    }

    /**
     * Formats an error message
     * @param array $error
     * @param string $header
     * @return string
     */
    public function getFormattedError($error, $header = '')
    {
        $message = "";

        if ($header !== '') {
            $message .= "<h3>$header</h3>\n";
        }

        $message .= "Message: {$error['message']}<br>";
        $message .= "Code: {$error['code']}<br>";
        $message .= "File: {$error['file']}<br>";
        $message .= "Line: {$error['line']}<br>";

        return $message;
    }

    /**
     * Returns an array of collected PHP errors
     * @return array
     */
    public function getPhpErrors()
    {
        return $this->php_errors;
    }

}
