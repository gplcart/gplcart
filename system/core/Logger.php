<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

/**
 * Provides methods to log various errors and events
 */
class Logger
{

    /**
     * Collected PHP errors
     * @var array
     */
    protected $errors = array();

    /**
     * Database instance
     * @var \gplcart\core\Database $db
     */
    protected $db;

    /**
     * Sets the database instance
     * @param \gplcart\core\Database $db
     */
    public function setDb($db)
    {
        $this->db = $db;
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
        if (!$this->db instanceof Database) {
            return false;
        }

        $message = '';

        if (is_string($data)) {
            $message = $data;
        } elseif (isset($data['message'])) {
            $message = $data['message'];
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

        try {
            $result = (bool) $this->db->insert('log', $data);
        } catch (\Exception $ex) {
            $result = false;
        }

        return $result;
    }

    /**
     * Returns an array of logged PHP errors in the database
     * @param integer|null $limit
     * @return array
     */
    public function selectPhpErrors($limit = null)
    {
        $sql = "SELECT * FROM log WHERE type LIKE ? ORDER BY time DESC";

        if (isset($limit)) {
            settype($limit, 'integer');
            $sql .= " LIMIT 0,$limit";
        }

        try {
            $results = $this->db->fetchAll($sql, array('php_%'), array('unserialize' => 'data'));
        } catch (\Exception $ex) {
            return array();
        }

        $list = array();
        foreach ($results as $result) {
            $list[] = $result['data'];
        }

        return $list;
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

        $key = md5(json_encode($error));

        if (!isset($this->errors[$key])) {
            $this->errors[$key] = $error;
            $this->log('php_error', $error, 'warning', false);
        }
    }

    /**
     * Shutdown handler
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if (in_array($error['type'], $this->getFatalErrorTypes())) {
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
     * @param \Exception $exception
     */
    public function exceptionHandler(\Exception $exception)
    {
        $error = $this->getExceptionMessageArray($exception);
        $this->log('php_exception', $error, 'danger', false);
        echo $this->getFormattedError($error, 'PHP Exception');
    }

    /**
     * Returns an array of exception data to be rendered
     * @param \Exception $instance
     * @return array
     */
    protected function getExceptionMessageArray(\Exception $instance)
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

        $message .= "Message: {$error['message']}<br>\n";

        if (isset($error['code'])) {
            $message .= "Code: {$error['code']}<br>\n";
        }

        if (isset($error['type'])) {
            $message .= "Type: {$error['type']}<br>\n";
        }

        $message .= "File: {$error['file']}<br>\n";
        $message .= "Line: {$error['line']}<br>\n";

        if (GC_CLI) {
            return strip_tags($message);
        }

        return $message;
    }

    /**
     * Returns an array of collected PHP errors
     * @param boolean $format
     * @return array
     */
    public function getPhpErrors($format = true)
    {
        if (!$format) {
            return $this->errors;
        }

        $formatted = array();
        foreach ($this->errors as $error) {
            $formatted[] = $this->getFormattedError($error);
        }

        return $formatted;
    }

}
