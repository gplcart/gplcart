<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to output a data to the user
 */
class Response
{

    /**
     * Array of defined headers
     * @var array
     */
    protected $headers = array();

    /**
     * Sends headers
     * @return boolean
     */
    protected function sendHeaders()
    {
        if (headers_sent()) {
            $this->headers = array();
            return false;
        }

        foreach ($this->headers as $header) {
            header($header, true);
        }
        return true;
    }

    /**
     * Adds a header
     * @param mixed $name Header name or numeric code (for status headers)
     * @param string $value Optional second part of header name
     * @return \gplcart\core\helpers\Response
     */
    protected function addHeader($name, $value = null)
    {
        if (is_numeric($name)) {
            $status = $this->statuses($name);
            if (!empty($status)) {
                $this->headers[] = "{$_SERVER['SERVER_PROTOCOL']} $name $status";
            }
        } elseif (isset($value)) {
            $this->headers[] = "$name: $value";
        }

        return $this;
    }

    /**
     * Returns an array of standard HTTP statuses
     * @return array|string
     */
    public function statuses($status = null)
    {
        $statuses = array(
            100 => 'Continue',
            101 => 'Switching Protocols',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Large',
            415 => 'Unsupported Media Type',
            416 => 'Requested range not satisfiable',
            417 => 'Expectation Failed',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported'
        );

        if (!isset($status)) {
            return $statuses;
        }

        return isset($statuses[$status]) ? $statuses[$status] : '';
    }

    /**
     * Prints a JSON string
     * @param array $data
     * @param array $options
     */
    final public function json($data, $options = array())
    {
        $this->addHeader('Content-Type', 'application/json');
        $this->addOptionalHeaders($options);
        $this->sendHeaders();

        exit(json_encode($data));
    }

    /**
     * Adds headers from deliver options
     * @param array $options
     */
    protected function addOptionalHeaders($options)
    {
        if (!empty($options['headers'])) {
            foreach ((array) $options['headers'] as $header) {
                list($name, $value) = array_pad((array) $header, 2, null);
                $this->addHeader($name, $value);
            }
        }
    }

    /**
     * Displays a static HTML
     * @param string $html
     * @param array $options
     */
    final public function html($html, $options = array())
    {
        $this->addHeader('Content-Type', 'text/html; charset=utf-8');
        $this->addOptionalHeaders($options);
        $this->sendHeaders();

        exit($html);
    }

    /**
     * Downloads a file
     * @param string $file Absolute path to the file
     * @param string $filename An alternative filename
     * @param array $options
     */
    public function download($file, $filename = '', $options = array())
    {
        if (is_file($file)) {

            if ($filename === '') {
                $filename = basename($file);
            }

            $this->addHeader('Content-Description', 'File Transfer');
            $this->addHeader('Content-Type', 'application/octet-stream');
            $this->addHeader('Content-Disposition', 'attachment; filename=' . $filename);
            $this->addHeader('Expires', 0);
            $this->addHeader('Cache-Control', 'must-revalidate');
            $this->addHeader('Pragma', 'public');
            $this->addHeader('Content-Length', filesize($file));

            $this->addOptionalHeaders($options);
            $this->sendHeaders();
            readfile($file);
            exit;
        }
    }

    /**
     * Outputs a file
     * @param string $file
     * @param array $options
     */
    public function file($file, $options = array())
    {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimetype = finfo_file($finfo, $file);
        finfo_close($finfo);

        if (empty($mimetype)) {
            $this->error404(false);
        }

        $this->addHeader('Content-type', $mimetype);
        $this->addHeader('Content-Length', filesize($file));
        $this->addOptionalHeaders($options);
        $this->sendHeaders();

        readfile($file);
        exit;
    }

    /**
     * Displays the 403 Access Denied message
     * @param string $message
     */
    final public function error403($message = true)
    {
        $this->addHeader(403)->sendHeaders();

        if (!$message) {
            exit;
        }

        $text = $this->getMessageError403();
        exit($text);
    }

    /**
     * Displays the 404 Not Found error
     * @param boolean $message
     */
    final public function error404($message = true)
    {
        $this->addHeader(404)->sendHeaders();

        if (!$message) {
            exit;
        }

        $text = $this->getMessageError404();
        exit($text);
    }

    /**
     * Returns a string with the error 403 text
     * @return string
     */
    protected function getMessageError403()
    {
        $text = '<html>';
        $text .= '<head>';
        $text .= '<title>403 Error - Permission Denied</title>';
        $text .= '</head>';
        $text .= '<body>';
        $text .= '<h1>403 - Permission Denied</h1>';
        $text .= '<p>You do not have permission to retrieve the URL or link you requested<p>';
        $text .= '</body>';
        $text .= '</html>';
        // IE hack. Content length must not be shorter than 512 chars
        $text .= str_repeat(' ', 512);

        return $text;
    }

    /**
     * Returns a string with the error 404 text
     * @return string
     */
    protected function getMessageError404()
    {
        $text = '<html>';
        $text .= '<head>';
        $text .= '<title>404 Error - Page Not Found</title>';
        $text .= '</head>';
        $text .= '<body>';
        $text .= '<h1>404 Error - Page Not Found</h1>';
        $text .= '<p>Page you requested cannot be found.<p>';
        $text .= '</body>';
        $text .= '</html>';
        // IE hack. Content length must not be shorter than 512 chars
        $text .= str_repeat(' ', 512);

        return $text;
    }

}
