<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to work with HTTP requests
 */
class Request
{

    /**
     * Language code from URL
     * @var string
     */
    protected $langcode = '';

    /**
     * Returns the current host
     * @return string
     */
    public function host()
    {
        return $this->server('HTTP_HOST');
    }

    /**
     * Returns a data from $_SERVER variable
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function server($name, $default = '')
    {
        return isset($_SERVER[$name]) ? filter_var(trim($_SERVER[$name]), FILTER_SANITIZE_STRING) : $default;
    }

    /**
     * Returns the current base path
     * @param boolean $exclude_langcode
     * @return string
     */
    public function base($exclude_langcode = false)
    {
        $base = GC_BASE;

        if ($base !== '/') {
            $base .= '/';
        }

        if (!empty($this->langcode)) {
            $suffix = "{$this->langcode}/";
            $base .= $suffix;
        }

        if ($exclude_langcode && !empty($suffix)) {
            $base = substr($base, 0, -strlen($suffix));
        }

        return $base;
    }

    /**
     * Sets a language code
     * @param string $langcode
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;
    }

    /**
     * Returns a language suffix from the URL
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
    }

    /**
     * Returns the current URN, i.e path with query
     * @return string
     */
    public function urn()
    {
        return $this->server('REQUEST_URI', '');
    }

    /**
     * Returns the request method
     * @return string
     */
    public function method()
    {
        return strtoupper($this->server('REQUEST_METHOD', 'GET'));
    }

    /**
     * Returns an address of the page which referred the user agent to the current page
     * @return string
     */
    public function referrer()
    {
        return $this->server('HTTP_REFERER');
    }

    /**
     * Returns IP from which the user is viewing the current page
     * @return string IP address
     */
    public function ip()
    {
        return $this->server('REMOTE_ADDR');
    }

    /**
     * Whether the current request is AJAX
     * @return bool
     */
    public function isAjax()
    {
        return strtolower($this->server('HTTP_X_REQUESTED_WITH')) === 'xmlhttprequest';
    }

    /**
     * Returns the current HTTP scheme
     * @return string HTTP scheme
     */
    public function scheme()
    {
        return $this->isSecure() ? 'https://' : 'http://';
    }

    /**
     * Whether the current connection is secure
     * @return bool
     */
    public function isSecure()
    {
        return $this->server('HTTPS', 'off') !== 'off';
    }

    /**
     * Returns the current user agent
     * @return string
     */
    public function agent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * Returns a content type of the request
     * @return string
     */
    public function type()
    {
        return $this->server('CONTENT_TYPE');
    }

    /**
     * Returns a content length of the request
     * @return integer
     */
    public function length()
    {
        return $this->server('CONTENT_LENGTH', 0);
    }

    /**
     * Returns a language of the request
     * @return string
     */
    public function language()
    {
        return substr($this->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
    }

    /**
     * Returns a data from POST request
     * @param string|array $name
     * @param mixed $default
     * @param bool|string $filter
     * @param null|string $type
     * @return mixed
     */
    public function post($name = null, $default = null, $filter = true, $type = null)
    {
        $post = $_POST;

        if (empty($post)) {
            $post = array();
        }

        if ($filter !== 'raw') {
            gplcart_array_trim($post, (bool) $filter);
        }

        if (isset($name)) {
            $result = gplcart_array_get($post, $name);
            $return = isset($result) ? $result : $default;
        } else {
            $return = $post;
        }

        gplcart_settype($return, $type, $default);

        return $return;
    }

    /**
     * Returns a data from GET request
     * @param string $name
     * @param mixed $default
     * @param null|string $type
     * @return mixed
     */
    public function get($name = null, $default = null, $type = null)
    {
        $get = $_GET;

        if (empty($get)) {
            $get = array();
        }

        gplcart_array_trim($get, true);

        if (isset($name)) {
            $result = gplcart_array_get($get, $name);
            $return = isset($result) ? $result : $default;
        } else {
            $return = $get;
        }

        gplcart_settype($return, $type, $default);

        return $return;
    }

    /**
     * Returns a data from FILES request
     * @param string $name
     * @param mixed $default
     * @return mixed
     */
    public function file($name = null, $default = null)
    {
        $files = $_FILES;

        if (isset($name)) {
            return !empty($files[$name]['name']) ? $files[$name] : $default;
        }

        return $files;
    }

    /**
     * Returns a data from COOKIE
     * @param string $name
     * @param mixed $default
     * @param null|string $type
     * @return mixed
     */
    public function cookie($name = null, $default = null, $type = null)
    {
        $cookie = $_COOKIE;

        if (empty($cookie)) {
            $cookie = array();
        }

        gplcart_array_trim($cookie, true);

        if (isset($name)) {
            $return = isset($cookie[$name]) ? $cookie[$name] : $default;
        } else {
            $return = $cookie;
        }

        gplcart_settype($return, $type, $default);

        return $return;
    }

    /**
     * Sets a cookie
     * @param string $name
     * @param string $value
     * @param integer $lifespan
     * @return boolean
     */
    public function setCookie($name, $value, $lifespan = 31536000)
    {
        return setcookie($name, $value, GC_TIME + $lifespan, '/');
    }

    /**
     * Deletes a cookie
     * @param string $name
     * @return boolean
     */
    public function deleteCookie($name = null)
    {
        if (isset($name)) {
            if (isset($_COOKIE[$name])) {
                unset($_COOKIE[$name]);
                return setcookie($name, '', GC_TIME - 3600, '/');
            }
            return false;
        }

        foreach (array_keys($_COOKIE) as $key) {
            $this->deleteCookie($key);
        }

        return true;
    }

    /**
     * Performs an HTTP request
     * @param string $url
     * @param array $options
     * @return array
     * @throws \InvalidArgumentException
     */
    public function send($url, array $options = array())
    {
        $uri = parse_url($url);

        $errno = $errstr = $socket = '';
        $this->prepareSendData($socket, $options, $uri);

        $fp = stream_socket_client($socket, $errno, $errstr, $options['timeout']);

        if (!empty($errstr)) {
            throw new \InvalidArgumentException($errstr);
        }

        $path = isset($uri['path']) ? $uri['path'] : '/';
        if (isset($uri['query'])) {
            $path .= "?{$uri['query']}";
        }

        $request = "{$options['method']} $path HTTP/1.0\r\n";
        foreach ($options['headers'] as $name => $value) {
            $request .= "$name: " . trim($value) . "\r\n";
        }
        $request .= "\r\n{$options['data']}";

        fwrite($fp, $request);

        $response = '';
        while (!feof($fp)) {
            $response .= fgets($fp, 1024);
        }

        fclose($fp);

        return $this->prepareSendResponse($response);
    }

    /**
     * Prepare an array of options for sending an HTTP request
     * @param string $socket
     * @param array $options
     * @param mixed $uri
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function prepareSendData(&$socket, array &$options, $uri)
    {
        if (empty($uri['scheme'])) {
            throw new \InvalidArgumentException('Missing URL scheme');
        }

        $options += array(
            'headers' => array(),
            'method' => 'GET',
            'data' => null,
            'timeout' => 30
        );

        $options['headers'] += array('User-Agent' => 'GPLCart');

        settype($options['timeout'], 'float');

        if ($uri['scheme'] === 'http') {
            $port = isset($uri['port']) ? $uri['port'] : 80;
            $socket = "tcp://{$uri['host']}:$port";
            if (!isset($options['headers']['Host'])) {
                $options['headers']['Host'] = $uri['host'] . ($port != 80 ? ':' . $port : '');
            }
        } else if ($uri['scheme'] === 'https') {
            $port = isset($uri['port']) ? $uri['port'] : 443;
            $socket = "ssl://{$uri['host']}:$port";
            if (!isset($options['headers']['Host'])) {
                $options['headers']['Host'] = $uri['host'] . ($port != 443 ? ':' . $port : '');
            }
        } else {
            throw new \InvalidArgumentException("Invalid schema '{$uri['scheme']}'");
        }

        if (is_array($options['data'])) {
            $options['data'] = http_build_query($options['data']);
        }

        $content_length = strlen($options['data']);
        if ($content_length > 0 || $options['method'] === 'POST' || $options['method'] === 'PUT') {
            $options['headers']['Content-Length'] = $content_length;
        }

        if (isset($uri['user'])) {
            $options['headers']['Authorization'] = 'Basic ' . base64_encode($uri['user'] . (isset($uri['pass']) ? ':' . $uri['pass'] : ':'));
        }

        return $options;
    }

    /**
     * Converts a response string to an array containing the response body and status data
     * @param string $response
     * @return array
     */
    protected function prepareSendResponse($response)
    {
        list($header, $data) = preg_split("/\r\n\r\n|\n\n|\r\r/", $response, 2);

        $headers = preg_split("/\r\n|\n|\r/", $header);
        $status = explode(' ', trim(reset($headers)), 3);

        $result = array('status' => '');
        $result['http'] = $status[0];
        $result['code'] = $status[1];

        if (isset($status[2])) {
            $result['status'] = $status[2];
        }

        return array('data' => $data, 'status' => $result);
    }

}
