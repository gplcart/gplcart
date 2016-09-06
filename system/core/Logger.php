<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\classes\Tool;

/**
 * Provides methods to log various errors and events
 */
class Logger
{

    /**
     * Collected PHP errors
     * @var array
     */
    protected static $errors;

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
        $fields = array(
            date('M d, Y G:iA'),
            $severity,
            $type,
            strip_tags($message)
        );

        return Tool::writeCsv($file, $fields, ',', '"', $limit);
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
        $config = Container::instance('core\\Config');
        $database = $config->getDb();

        if (empty($database)) {
            return false;
        }

        if (is_string($data)) {
            $message = $data;
        } elseif (isset($data['message'])) {
            $message = $data['message'];
            unset($data['message']);
        }

        if (empty($message)) {
            return false;
        }

        $values = array(
            'time' => GC_TIME,
            'text' => $message,
            'log_id' => uniqid(),
            'data' => serialize((array) $data),
            'translatable' => (int) $translatable,
            'type' => mb_substr($type, 0, 255, 'UTF-8'),
            'severity' => mb_substr($severity, 0, 255, 'UTF-8')
        );

        $result = $database->insert('log', $values);
        return (bool) $result;
    }

    /**
     * Error handler
     * @param integer $errno
     * @param string $errstr
     * @param string $errfile
     * @param integer $errline
     */
    public function errorHandler($errno, $errstr, $errfile = '', $errline = '')
    {
        $error['code'] = $errno;
        $error['file'] = $errfile;
        $error['line'] = $errline;
        $error['message'] = $errstr;

        $this->log('php_error', $error, 'warning', false);
        $message = $this->getFormattedError($error);
        error_log($message, 0);
        static::$errors['warning'][] = $message;
    }

    /**
     * Shutdown handler
     */
    public function shutdownHandler()
    {
        $lasterror = error_get_last();

        $error_types = array(
            E_ERROR,
            E_PARSE,
            E_CORE_ERROR,
            E_USER_ERROR,
            E_CORE_WARNING,
            E_COMPILE_ERROR,
            E_COMPILE_WARNING,
            E_RECOVERABLE_ERROR,
        );

        if (in_array($lasterror['type'], $error_types)) {

            $error['message'] = $lasterror['message'];
            $error['code'] = $lasterror['type'];
            $error['file'] = $lasterror['file'];
            $error['line'] = $lasterror['line'];

            error_log($this->getFormattedError($error), 0);
            $this->log('php_shutdown', $error, 'danger', false);
        }
    }

    /**
     * Common exception handler
     */
    public function exceptionHandler($exception)
    {
        $error = $exception->getMessageArray();
        $this->log('php_exception', $error, 'danger', false);

        $message = $this->getFormattedError($error, 'PHP Exception');
        error_log($message, 0);
        echo $message;
    }

    /**
     * Formats an error message
     * @param string $error
     * @param string $header
     * @return string
     */
    public function getFormattedError($error, $header = '')
    {
        $message = "";

        if ($header !== '') {
            $message .= "<h3>$header</h3>\n";
        }

        $message .= "<p><strong>Message:</strong> {$error['message']}</p>\n";
        $message .= "<p><strong>Code:</strong> {$error['code']}</p>\n";
        $message .= "<p><strong>File:</strong> {$error['file']}</p>\n";
        $message .= "<p><strong>Line:</strong> {$error['line']}</p>\n";

        return $message;
    }

    /**
     * Returns an array of collected errors
     * @return array
     */
    public function getErrors()
    {
        return static::$errors;
    }

}
