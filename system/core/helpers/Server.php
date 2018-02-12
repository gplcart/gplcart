<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to work with server and execution environment data
 */
class Server
{

    /**
     * $_SERVER environment information
     * @var array
     */
    protected $server;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->set();
    }

    /**
     * Sets $_SERVER variables
     * @param null|array $data
     * @return $this
     */
    public function set($data = null)
    {
        $this->server = isset($data) ? $data : $_SERVER;
        return $this;
    }

    /**
     * Returns a server data
     * @param string $name
     * @param mixed $default
     * @param bool $sanitize
     * @return mixed
     */
    public function get($name = null, $default = '', $sanitize = true)
    {
        if (!isset($name)) {
            return $this->server;
        }

        if (!array_key_exists($name, $this->server)) {
            return $default;
        }

        if (is_array($this->server[$name])) {
            gplcart_array_trim($this->server[$name], $sanitize);
        } else {
            $this->server[$name] = trim($this->server[$name]);
            if ($sanitize) {
                $this->server[$name] = filter_var($this->server[$name], FILTER_SANITIZE_STRING);
            }
        }

        return $this->server[$name];
    }

    /**
     * Returns the current host
     * @return string
     */
    public function httpHost()
    {
        return $this->get('HTTP_HOST');
    }

    /**
     * Returns the current URN, i.e path with query
     * @return string
     */
    public function requestUri()
    {
        return $this->get('REQUEST_URI', '');
    }

    /**
     * Returns the request method
     * @return string
     */
    public function requestMethod()
    {
        return strtoupper($this->get('REQUEST_METHOD', 'GET'));
    }

    /**
     * Returns an address of the page which referred the user agent to the current page
     * @return string
     */
    public function httpReferrer()
    {
        return $this->get('HTTP_REFERER');
    }

    /**
     * Returns IP from which the user is viewing the current page
     * @return string IP address
     */
    public function remoteAddr()
    {
        return $this->get('REMOTE_ADDR');
    }

    /**
     * Returns the current HTTP scheme
     * @return string HTTP scheme
     */
    public function httpScheme()
    {
        return $this->isSecureConnection() ? 'https://' : 'http://';
    }

    /**
     * Returns a language of the request
     * @return string
     */
    public function httpLanguage()
    {
        return substr($this->get('HTTP_ACCEPT_LANGUAGE'), 0, 2);
    }

    /**
     * Returns the current user agent
     * @return string
     */
    public function userAgent()
    {
        return $this->get('HTTP_USER_AGENT');
    }

    /**
     * Returns a content type of the request
     * @return string
     */
    public function contentType()
    {
        return $this->get('CONTENT_TYPE');
    }

    /**
     * Returns a content length of the request
     * @return integer
     */
    public function contentLength()
    {
        return $this->get('CONTENT_LENGTH', 0);
    }

    /**
     * Array of arguments passed to script
     * @return array
     */
    public function cliArgs()
    {
        return $this->get('argv', array());
    }

    /**
     * Whether the current request is AJAX
     * @return bool
     */
    public function isAjaxRequest()
    {
        return strtolower($this->get('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Whether the current connection is secure
     * @return bool
     */
    public function isSecureConnection()
    {
        return $this->get('HTTPS', 'off') !== 'off';
    }

    /**
     * Returns an HTTP header
     * @param string $name
     * @param mixed $default
     * @param bool $sanitize
     * @return mixed
     */
    public function header($name, $default = null, $sanitize = true)
    {
        $key = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->get($key, $default, $sanitize);
    }

}
