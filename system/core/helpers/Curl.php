<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides wrappers for CURL functions
 */
class Curl
{

    /**
     * CURL handle
     * @var resource
     */
    protected $handle;

    /**
     * An array of headers to be sent
     * @var array
     */
    protected $headers = array();

    /**
     * An array of CURL options
     * @var array
     */
    protected $options = array();

    /**
     * An array of request parameters
     * @var array
     */
    protected $params = array();

    /**
     * An array of cookies
     * @var array
     */
    protected $cookies = array();

    /**
     * An array of response information
     * @var array
     */
    protected $info = array();

    /**
     * A path to cookie file
     * @var string
     */
    protected $cookie_file;

    /**
     * The request response
     * @var mixed
     */
    protected $response;

    /**
     * @throws \RuntimeException
     */
    public function __construct()
    {
        if (!extension_loaded('curl')) {
            throw new \RuntimeException('CURL extension is not loaded');
        }
    }

    /**
     * Close CURL handle (if any)
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Initialize CURL handle
     * @return $this
     * @throws \RuntimeException
     */
    public function open()
    {
        $this->handle = curl_init();

        if (!is_resource($this->handle)) {
            throw new \RuntimeException('Failed to initialize CURL');
        }

        $this->setDefaultOptions();
        return $this;
    }

    /**
     * Close CURL handle
     */
    public function close()
    {
        if (is_resource($this->handle)) {
            curl_close($this->handle);
        }

        $this->handle = null;
    }

    /**
     * Sets connection timeout, in seconds
     * @param int $value
     * @return $this
     */
    public function setConnectTimeOut($value)
    {
        $this->setOption(CURLOPT_CONNECTTIMEOUT, (int) $value);
        return $this;
    }

    /**
     * Whether to follow HTTP 3xx redirects
     * @param bool $value
     * @return $this
     */
    public function setFollowRedirects($value)
    {
        $this->setOption(CURLOPT_FOLLOWLOCATION, (bool) $value);
        return $this;
    }

    /**
     * Sets HTTP referer
     * @param string $value
     * @return $this
     */
    public function setReferer($value)
    {
        $this->setOption(CURLOPT_REFERER, $value);
        return $this;
    }

    /**
     * Sets user agent
     * @param string $value
     * @return $this
     */
    public function setUserAgent($value)
    {
        $this->setOption(CURLOPT_USERAGENT, $value);
        return $this;
    }

    /**
     * Sets HTTP server authentication methods to try
     * @param int $value
     * @return $this
     */
    public function setAuthType($value = CURLAUTH_BASIC)
    {
        $this->setOption(CURLOPT_HTTPAUTH, $value);
        return $this;
    }

    /**
     * Sets user credentials for HTTP Authentication
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function setAuthCredentials($username, $password)
    {
        $this->setOption(CURLOPT_USERPWD, "$username:$password");
        return $this;
    }

    /**
     * Sets cookie file
     * @param string $filepath
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function setCookieFile($filepath)
    {
        if (!is_writable($filepath)) {
            throw new \InvalidArgumentException('Invalid cookie file');
        }

        $this->cookie_file = $filepath;
        return $this;
    }

    /**
     * Sets header
     * @param string|array $header
     * @param null|string $value
     * @return $this
     */
    public function setHeader($header, $value = null)
    {
        if (is_array($header)) {
            $this->headers = array_merge($this->headers, $header);
        } else {
            $this->headers[$header] = $value;
        }

        return $this;
    }

    /**
     * Set an option
     * @param array|string $option
     * @param mixed $value
     * @return $this
     */
    public function setOption($option, $value = null)
    {
        if (is_array($option)) {
            $this->options = array_merge($this->options, $option);
        } else {
            $this->options[$option] = $value;
        }

        return $this;
    }

    /**
     * Sets cookie
     * @param string|array $cookie
     * @param null|string $value
     * @return $this
     */
    public function setCookie($cookie, $value = null)
    {
        if (is_array($cookie)) {
            $this->cookies = array_merge($this->cookies, $cookie);
        } else {
            $this->cookies[$cookie] = $value;
        }

        return $this;
    }

    /**
     * Sets the request method
     * @param string $method
     * @return $this
     */
    public function setRequestMethod($method)
    {
        switch (strtoupper($method)) {
            case 'HEAD':
                $this->setOption(CURLOPT_NOBODY, true);
                break;
            case 'GET':
                $this->setOption(CURLOPT_HTTPGET, true);
                break;
            case 'POST':
                $this->setOption(CURLOPT_POST, true);
                break;
            default:
                $this->setOption(CURLOPT_CUSTOMREQUEST, $method);
        }

        return $this;
    }

    /**
     * Sets request URL
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->setOption(CURLOPT_URL, $url);
        return $this;
    }

    /**
     * Sets request parameters
     * @param array|string $param
     * @param null|string $value
     */
    public function setParams($param, $value = null)
    {
        if (is_array($param)) {
            $this->params = array_merge($this->params, $param);
        } else {
            $this->params[$param] = $value;
        }

        return $this;
    }

    /**
     * Unset cookie file
     * @return $this
     */
    public function unsetCookieFile()
    {
        $this->cookie_file = null;
        return $this;
    }

    /**
     * Removes a header
     * @param string $header
     * @return $this
     */
    public function unsetHeader($header)
    {
        unset($this->headers[$header]);
        return $this;
    }

    /**
     * Removes an option
     * @param string $option
     * @return $this
     */
    public function unsetOption($option)
    {
        unset($this->options[$option]);
        return $this;
    }

    /**
     * Remove a cookie
     * @param string $name
     * @return $this
     */
    public function unsetCookie($name)
    {
        unset($this->cookies[$name]);
        return $this;
    }

    /**
     * Re-initiates the cURL handle
     * @return $this
     */
    public function reset()
    {
        $this->close();
        $this->info = array();
        $this->response = null;
        $this->open();
        return $this;
    }

    /**
     * Re-initiates the cURL handle and reset all the data
     * @return $this
     */
    public function resetAll()
    {
        $this->headers = array();
        $this->options = array();
        $this->params = array();
        $this->cookies = array();
        $this->cookie_file = null;
        $this->reset();
        return $this;
    }

    /**
     * Performs an HTTP query
     * @param string $url
     * @param string $method
     * @param array $params
     * @return $this
     * @throws \RuntimeException
     */
    public function request($url, $method = 'GET', array $params = array())
    {
        $this->open()
                ->setUrl($url)
                ->setParams($params)
                ->setRequestMethod($method)
                ->setRequestOptions();

        $this->response = curl_exec($this->handle);

        if ($this->response === false) {
            throw new \RuntimeException('Failed to execute CURL');
        }

        $this->info = curl_getinfo($this->handle);
        $this->close();
        return $this;
    }

    /**
     * Makes the HEAD request
     * @param string $url
     * @param array $params
     * @return string
     */
    public function head($url, array $params = array())
    {
        return $this->request($url, 'HEAD', $params)->getResponse();
    }

    /**
     * Makes the POST request
     * @param string $url
     * @param array $params
     * @return string
     */
    public function post($url, array $params = array())
    {
        return $this->request($url, 'POST', $params)->getResponse();
    }

    /**
     * Makes the PUT request
     * @param string $url
     * @param array $params
     * @return string
     */
    public function put($url, array $params = array())
    {
        return $this->request($url, 'PUT', $params)->getResponse();
    }

    /**
     * Makes GET query
     * @param string $url
     * @param array $params
     * @return string
     */
    public function get($url, array $params = array())
    {
        return $this->request($url, 'GET', $params)->getResponse();
    }

    /**
     * Returns the request info
     * @param null|string $key
     * @return mixed
     * @throws \InvalidArgumentException
     */
    public function getInfo($key = null)
    {
        if (!isset($key)) {
            return $this->info;
        }

        if (array_key_exists($key, $this->info)) {
            return $this->info[$key];
        }

        throw new \InvalidArgumentException("No such key: $key");
    }

    /**
     * Returns the response body
     * @return string
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Returns an array of defined request parameters
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns an array of defined request options
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Returns an array of defined cookies
     * @return array
     */
    public function getCookies()
    {
        return $this->cookies;
    }

    /**
     * Returns a path to cookie file
     * @return string
     */
    public function getCookieFile()
    {
        return $this->cookie_file;
    }

    /**
     * Returns CURL handle
     * @return resource
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Sets default options
     */
    protected function setDefaultOptions()
    {
        $this->options = array(
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_USERAGENT => 'GPLCart',
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false
        );
    }

    /**
     * Prepare request headers
     * @return array
     */
    protected function prepareHeaders()
    {
        $headers = array();
        foreach ($this->headers as $key => $value) {
            $headers[] = "$key: $value";
        }

        return $headers;
    }

    /**
     * Prepare request cookies
     * @return string
     */
    protected function prepareCookies()
    {
        $string = '';
        foreach ($this->cookies as $key => $value) {
            $string .= "$key=$value; ";
        }

        return $string;
    }

    /**
     * Sets request options
     * @throws \RuntimeException
     */
    protected function setRequestOptions()
    {
        if (!empty($this->params)) {
            if (isset($this->options[CURLOPT_HTTPGET])) {
                $this->setGetRequestUrl();
            } else {
                $this->setOption(CURLOPT_POSTFIELDS, http_build_query($this->params));
            }
        }

        if (!empty($this->headers)) {
            $this->setOption(CURLOPT_HTTPHEADER, $this->prepareHeaders());
        }

        if (!empty($this->cookie_file)) {
            $this->setOption(CURLOPT_COOKIEFILE, $this->cookie_file);
            $this->setOption(CURLOPT_COOKIEJAR, $this->cookie_file);
        }

        if (!empty($this->cookies)) {
            $this->setOption(CURLOPT_COOKIE, $this->prepareCookies());
        }

        if (!curl_setopt_array($this->handle, $this->options)) {
            throw new \RuntimeException('Failed to set CURL options');
        }
    }

    /**
     * Prepare GET request URL
     */
    protected function setGetRequestUrl()
    {
        $url = trim($this->options[CURLOPT_URL], "&?");
        $parsed = parse_url($url);
        $query = http_build_query($this->params);

        if (isset($parsed['query'])) {
            $url .= "&$query";
        } else {
            $url .= "?$query";
        }

        $this->setOption(CURLOPT_URL, $url);
    }

}
