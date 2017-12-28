<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception;

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
     * Whether to convert PHP errors to exceptions
     * @var bool
     */
    protected $error_to_exception = true;

    /**
     * Sets the database instance
     * @param \gplcart\core\Database $db
     * @return $this
     */
    public function setDb($db)
    {
        $this->db = $db;
        return $this;
    }

    /**
     * Enable/disable converting PHP errors to exceptions
     * @param bool $convert
     */
    public function errorToException($convert = true)
    {
        $this->error_to_exception = (bool) $convert;
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
        if (!$this->db->isInitialized()) {
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
     * Returns an array of logged PHP errors from the database
     * @param integer|null $limit
     * @return array
     */
    public function selectErrors($limit = null)
    {
        if (!$this->db->isInitialized()) {
            return array();
        }

        $sql = "SELECT * FROM log WHERE type LIKE ? ORDER BY time DESC";

        if (isset($limit)) {
            settype($limit, 'integer');
            $sql .= " LIMIT 0,$limit";
        }

        try {
            $results = $this->db->fetchAll($sql, array('php_%'), array('unserialize' => 'data'));
        } catch (Exception $ex) {
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
     * @throws Exception
     */
    public function errorHandler($code, $message, $file = '', $line = '')
    {
        $error = array(
            'code' => $code,
            'file' => $file,
            'line' => $line,
            'message' => $message,
            'backtrace' => $this->backtrace()
        );

        $key = md5(json_encode($error));

        if (!isset($this->errors[$key])) {

            $this->errors[$key] = $error;

            if ($this->error_to_exception) {
                throw new Exception($this->getFormattedError($error));
            }

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
     * Returns an array of PHP error types which are fatal
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
     * @param Exception $exc
     */
    public function exceptionHandler(Exception $exc)
    {
        $error = array(
            'code' => $exc->getCode(),
            'file' => $exc->getFile(),
            'line' => $exc->getLine(),
            'message' => $exc->getMessage(),
            'backtrace' => $this->backtrace()
        );

        $this->log('php_exception', $error, 'danger', false);
        echo $this->getFormattedError($error, 'PHP Exception');
    }

    /**
     * Formats an error message
     * @param array $error
     * @param string $header
     * @return string
     */
    public function getFormattedError($error, $header = '')
    {
        $parts = array("Message: {$error['message']}");

        if (isset($error['code'])) {
            $parts[] = "Code: {$error['code']}";
        }

        if (isset($error['type'])) {
            $parts[] = "Type: {$error['type']}";
        }

        $parts[] = "File: {$error['file']}";
        $parts[] = "Line: {$error['line']}";

        $message = implode("<br>\n", $parts);

        if ($header !== '') {
            $message = "<h3>$header</h3>$message";
        }

        return GC_CLI ? strip_tags($message) : $message;
    }

    /**
     * Returns an array of collected PHP errors
     * @param boolean $format
     * @return array
     */
    public function getErrors($format = true)
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

    /**
     * Generates a backtrace
     * @return array
     */
    public function backtrace()
    {
        $e = new Exception;

        $trace = array_reverse(explode("\n", $e->getTraceAsString()));
        array_shift($trace);
        array_pop($trace);
        $length = count($trace);
        $root = str_replace('/', DIRECTORY_SEPARATOR, GC_DIR) . DIRECTORY_SEPARATOR;

        $result = array();
        for ($i = 0; $i < $length; $i++) {
            $result[] = str_replace($root, '', substr($trace[$i], strpos($trace[$i], ' ')));
        }

        return $result;
    }

}
