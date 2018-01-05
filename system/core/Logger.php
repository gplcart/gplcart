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
    protected $error_to_exception = false;

    /**
     * Whether to log error backtrace
     * @var bool
     */
    protected $log_backtrace = true;

    /**
     * Whether to print error backtrace
     * @var bool
     */
    protected $print_backtrace = false;

    /**
     * Whether to print PHP errors
     * @var bool
     */
    protected $print_error = false;

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
     * @return $this
     */
    public function errorToException($convert = true)
    {
        $this->error_to_exception = (bool) $convert;
        return $this;
    }

    /**
     * Enable / disable error backtrace logging
     * @param type $value
     * @return $this
     */
    public function logBacktrace($value = true)
    {
        $this->log_backtrace = (bool) $value;
        return $this;
    }

    /**
     * Enable / disable error output
     * @param bool $value
     * @return $this
     */
    public function printError($value = true)
    {
        $this->print_error = (bool) $value;
        return $this;
    }

    /**
     * Whether to add a backtrace to each printed error
     * @param bool $value
     * @return $this
     */
    public function printBacktrace($value = true)
    {
        $this->print_backtrace = (bool) $value;
        return $this;
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
        $message = '';
        if (is_string($data)) {
            $message = $data;
        } elseif (isset($data['message'])) {
            $message = (string) $data['message'];
        }

        $values = array(
            'text' => $message,
            'data' => (array) $data,
            'translatable' => $translatable,
            'type' => mb_substr($type, 0, 255),
            'severity' => mb_substr($severity, 0, 255)
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
        if (!$this->isDb()) {
            return false;
        }

        $data += array(
            'time' => GC_TIME,
            'log_id' => gplcart_string_random(6)
        );

        try {
            $result = (bool) $this->db->insert('log', $data);
        } catch (Exception $ex) {
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
        if (!$this->isDb()) {
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
     * @return bool
     */
    public function errorHandler($code, $message, $file = '', $line = '')
    {
        if ($this->error_to_exception) {

            if (ob_get_length() > 0) {
                ob_end_clean(); // Fix templates
            }

            throw new Exception($message);
        }

        $error = array(
            'code' => $code,
            'file' => $file,
            'line' => $line,
            'message' => $message,
            'backtrace' => $this->log_backtrace ? gplcart_backtrace() : array()
        );

        $print = $this->print_error || !$this->isDb();

        if (in_array($code, $this->getFatalErrorTypes())) {
            if ($print) {
                return false; // Let it fall through to the standard PHP error handler
            }
            $this->log('php_shutdown', $error, 'danger', false);
            return true;
        }

        $key = md5(json_encode($error));

        if (isset($this->errors[$key])) {
            return true;
        }

        $this->errors[$key] = $error;

        if ($print) {
            echo $this->getFormattedError($error);
        }

        $this->log('php_error', $error, 'warning', false);
        return true;
    }

    /**
     * Shutdown handler
     */
    public function shutdownHandler()
    {
        $error = error_get_last();

        if (isset($error['type']) && in_array($error['type'], $this->getFatalErrorTypes())) {
            $this->log('php_shutdown', $error, 'danger', false);
        }
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
            'backtrace' => $this->log_backtrace ? gplcart_backtrace() : array()
        );

        $this->log('php_exception', $error, 'danger', false);
        echo $this->getFormattedError($error, 'Exception');
    }

    /**
     * Formats an error message
     * @param array $error
     * @param string $header
     * @return string
     */
    public function getFormattedError(array $error, $header = '')
    {
        $output = "<table style='background:#f2dede;color:#a94442;width:100%;'>";

        if (!empty($header)) {
            $output .= "<tr><td colspan='2'><h1 style='padding:0;margin:0;'>$header</h3></td></tr>\n";
        }

        $output .= "<tr><td>Message </td><td>{$error['message']}</td></tr>\n";

        if (isset($error['code'])) {
            $output .= "<tr><td>Code </td><td>{$error['code']}</td></tr>\n";
        }

        if (isset($error['type'])) {
            $output .= "<tr><td>Type </td><td>{$error['type']}</td></tr>\n";
        }

        $output .= "<tr><td>File </td><td>{$error['file']}</td></tr>\n";
        $output .= "<tr><td>Line </td><td>{$error['line']}</td></tr>\n";

        if ($this->print_backtrace && !empty($error['backtrace'])) {
            $error['backtrace'] = implode("<br>\n", $error['backtrace']);
            $output .= "<tr><td>Backtrace </td><td>{$error['backtrace']}</td></tr>\n";
        }

        $output .= '</table>';
        return GC_CLI ? strip_tags($output) : $output;
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
     * Returns an array of PHP error types which are fatal
     * @return array
     */
    public function getFatalErrorTypes()
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
     * Whether the database is ready
     * @return bool
     */
    protected function isDb()
    {
        return $this->db instanceof \gplcart\core\Database && $this->db->isInitialized();
    }

}
