<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to output data
 */
class Response
{

    /**
     * Array of defined HTTP headers
     * @var array
     */
    protected $headers = array();

    /**
     * Sends HTTP headers
     */
    public function sendHeaders()
    {
        if (!headers_sent()) {
            foreach ($this->headers as $header) {
                header($header, true);
            }
        }

        $this->headers = array();
    }

    /**
     * Adds a header
     * @param string|int $name
     * @param string $value
     * @return $this
     */
    public function addHeader($name, $value = null)
    {
        if (is_numeric($name)) {
            $status = $this->getStatuses($name);
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
     * @param null|int $status
     * @return array|string
     */
    public function getStatuses($status = null)
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
     * Adds headers from options
     * @param array $options
     * @return $this
     */
    protected function addOptionalHeaders($options)
    {
        if (!empty($options['headers'])) {
            foreach ((array) $options['headers'] as $header) {
                list($name, $value) = array_pad((array) $header, 2, null);
                $this->addHeader($name, $value);
            }
        }

        return $this;
    }

    /**
     * Output HTML page
     * @param string $html
     * @param array $options
     */
    public function outputHtml($html, $options = array())
    {
        $this->addHeader('Content-Type', 'text/html; charset=utf-8')
                ->addOptionalHeaders($options)
                ->sendHeaders();

        echo $html;
        exit;
    }

    /**
     * Output JSON string
     * @param array $data
     * @param array $options
     */
    public function outputJson($data, $options = array())
    {
        $this->addHeader('Content-Type', 'application/json')
                ->addOptionalHeaders($options)
                ->sendHeaders();

        echo gplcart_json_encode($data);
        exit;
    }

    /**
     * Downloads a file
     * @param string $file Absolute path to the file
     * @param string $filename An alternative filename
     * @param array $options
     * @return null
     */
    public function download($file, $filename = '', $options = array())
    {
        $readfile = empty($options['text']);

        if ($readfile && !is_file($file)) {
            return null;
        }

        if ($readfile && empty($filename)) {
            $filename = basename($file);
        }

        $size = $readfile ? filesize($file) : strlen($file);

        $this->addHeader('Expires', 0)
                ->addHeader('Pragma', 'public')
                ->addHeader('Content-Length', $size)
                ->addHeader('Cache-Control', 'must-revalidate')
                ->addHeader('Content-Description', 'File Transfer')
                ->addHeader('Content-Type', 'application/octet-stream')
                ->addHeader('Content-Disposition', 'attachment; filename=' . $filename)
                ->addOptionalHeaders($options)
                ->sendHeaders();

        if ($readfile) {
            readfile($file);
        } else {
            echo $file;
        }

        exit;
    }

    /**
     * Output 403 error page
     * @param bool $message
     */
    public function outputError403($message = true)
    {
        $this->addHeader(403)->sendHeaders();

        if ($message) {
            echo $this->getError403();
        }

        exit;
    }

    /**
     * Output 404 error page
     * @param boolean $message
     */
    public function outputError404($message = true)
    {
        $this->addHeader(404)->sendHeaders();

        if ($message) {
            echo $this->getError404();
        }

        exit;
    }

    /**
     * Returns 403 error message
     * @return string
     */
    protected function getError403()
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
        $text .= str_repeat(' ', 512);
        return $text;
    }

    /**
     * Returns 404 error message
     * @return string
     */
    protected function getError404()
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
        $text .= str_repeat(' ', 512);
        return $text;
    }

}
