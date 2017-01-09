<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to work with various server data
 */
class Request
{

    const COOKIE_PREFIX = 'gplcart_';

    public function __construct()
    {
        // Empty
    }

    /**
     * Base path
     * @var string
     */
    protected $base;

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
     * @param string $var
     * @param mixed $default
     * @return mixed
     */
    protected function server($var, $default = '')
    {
        return isset($_SERVER[$var]) ? trim($_SERVER[$var]) : $default;
    }

    /**
     * Returns the current base path
     * @param boolean $exclude_langcode
     * @return string
     */
    public function base($exclude_langcode = false)
    {
        if (!isset($this->base)) {
            $base = str_replace(array('\\', ' '), array('/', '%20'), dirname($this->server('SCRIPT_NAME')));
            $this->base = ($base == "/") ? "/" : $base . "/";
        }

        $base = $this->base . $this->langcode;

        if ($exclude_langcode && !empty($this->langcode)) {
            $base = substr($base, 0, -strlen($this->langcode));
        }

        return $base;
    }

    /**
     * Sets a language code
     * @param string $code
     */
    public function setBaseSuffix($code)
    {
        $this->langcode = $code . '/';
    }

    /**
     * Returns a language suffix from the URL
     * @return string
     */
    public function getBaseSuffix()
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
     * Returns request method
     * @return string
     */
    public function method()
    {
        $method = $this->server('REQUEST_METHOD', 'GET');
        if (isset($_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'])) {
            $method = $_SERVER['HTTP_X_HTTP_METHOD_OVERRIDE'];
        } elseif (isset($_REQUEST['_method'])) {
            $method = $_REQUEST['_method'];
        }

        return strtoupper($method);
    }

    /**
     * Returns an address of the page which referred the user agent to the current page
     * @return string HTTP referrer
     */
    public function referrer()
    {
        return $this->server('HTTP_REFERER');
    }

    /**
     * Returns an IP from which the user is viewing the current page
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
        return ($this->server('HTTP_X_REQUESTED_WITH') === 'XMLHttpRequest');
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
        return ($this->server('HTTPS', 'off') !== 'off');
    }

    /**
     * Returns contents of the User-Agent: header from the current request
     * @return string
     */
    public function agent()
    {
        return $this->server('HTTP_USER_AGENT');
    }

    /**
     * Returns a content type
     * @return string Content type
     */
    public function type()
    {
        return $this->server('CONTENT_TYPE');
    }

    /**
     * Returns a content length
     * @return integer Content length in bytes
     */
    public function length()
    {
        return $this->server('CONTENT_LENGTH', 0);
    }

    /**
     * Returns a data from the POST request
     * @param string $name
     * @param mixed $default
     * @param bool|string $filter
     * @return mixed
     */
    public function post($name = null, $default = null, $filter = true)
    {
        $post = empty($_POST) ? array() : $_POST;

        if ($filter !== 'raw') {
            $this->sanitize($post, (bool) $filter);
        }

        if (isset($name)) {
            return isset($post[$name]) ? $post[$name] : $default;
        }

        return $post;
    }

    /**
     * Cleans up an array of values
     * @param array $array
     * @param bool $filter
     */
    protected function sanitize(array &$array, $filter = true)
    {
        gplcart_array_trim($array, $filter);
    }

    /**
     * Returns a data from the $_REQUEST superglobal
     * @param string $name
     * @param mixed $default
     * @param bool $filter
     * @return mixed
     */
    public function request($name = null, $default = null, $filter = true)
    {
        $request = empty($_REQUEST) ? array() : $_REQUEST;

        if ($filter !== 'raw') {
            $this->sanitize($request, $filter);
        }

        if (isset($name)) {
            return isset($request[$name]) ? $request[$name] : $default;
        }

        return $request;
    }

    /**
     * Returns a data from COOKIE
     * @param string $name
     * @param mixed $default
     * @param bool $filter
     * @return mixed
     */
    public function cookie($name = null, $default = null, $filter = true)
    {
        $cookie = empty($_COOKIE) ? array() : $_COOKIE;

        gplcart_array_trim($cookie, $filter);

        if (isset($name)) {
            return isset($cookie[self::COOKIE_PREFIX . $name]) ? $cookie[self::COOKIE_PREFIX . $name] : $default;
        }

        return $cookie;
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
        return setcookie(self::COOKIE_PREFIX . $name, $value, GC_TIME + $lifespan, '/');
    }

    /**
     * Deletes a cookie
     * @param string $name
     * @param bool $check_prefix
     * @return boolean
     */
    public function deleteCookie($name = null, $check_prefix = true)
    {
        if (!isset($name)) {
            foreach (array_keys($_COOKIE) as $key) {
                if (strpos($key, self::COOKIE_PREFIX) === 0) {
                    $this->deleteCookie($key, false);
                }
            }
            return true;
        }

        if ($check_prefix) {
            $name = self::COOKIE_PREFIX . $name;
        }

        if (isset($_COOKIE[$name])) {
            unset($_COOKIE[$name]);
            return setcookie($name, '', GC_TIME - 3600, '/');
        }

        return false;
    }

    /**
     * Returns a data from the GET request
     * @param string $name
     * @param mixed $default
     * @param bool $filter
     * @return mixed
     */
    public function get($name = null, $default = null, $filter = true)
    {
        $get = empty($_GET) ? array() : $_GET;

        $this->sanitize($get, $filter);

        if (isset($name)) {
            return isset($get[$name]) ? urldecode($get[$name]) : $default;
        }

        return $get;
    }

    /**
     * Returns a data from the FILES request
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

}
