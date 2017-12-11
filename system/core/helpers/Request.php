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
     * $_SERVER environment information
     * @var array
     */
    protected $server;

    /**
     * HTTP GET variables
     * @var array
     */
    protected $get;

    /**
     * HTTP POST variables
     * @var array
     */
    protected $post;

    /**
     * HTTP Cookies
     * @var array
     */
    protected $cookie;

    /**
     * HTTP File Upload variables
     * @var array
     */
    protected $files;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setServerVars();
        $this->setGetVars();
        $this->setPostVars();
        $this->setCookieVars();
        $this->setFilesVars();
    }

    /**
     * Sets $_SERVER variables
     * @param null|array $data
     * @return $this
     */
    public function setServerVars($data = null)
    {
        $this->server = isset($data) ? $data : $_SERVER;
        return $this;
    }

    /**
     * Sets $_GET variables
     * @param null|array $data
     * @return $this
     */
    public function setGetVars($data = null)
    {
        $this->get = isset($data) ? $data : $_GET;
        return $this;
    }

    /**
     * Sets $_POST variables
     * @param null|array $data
     * @return $this
     */
    public function setPostVars($data = null)
    {
        $this->post = isset($data) ? $data : $_POST;
        return $this;
    }

    /**
     * Sets $_COOKIE variables
     * @param null|array $data
     * @return $this
     */
    public function setCookieVars($data = null)
    {
        $this->cookie = isset($data) ? $data : $_COOKIE;
        return $this;
    }

    /**
     * Sets $_FILES variables
     * @param null|array $data
     * @return $this
     */
    public function setFilesVars($data = null)
    {
        $this->files = isset($data) ? $data : $_FILES;
        return $this;
    }

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
    public function server($name = null, $default = '')
    {
        if (!isset($name)) {
            return $this->server;
        }

        if (!isset($this->server[$name])) {
            return $default;
        }

        return filter_var(trim($this->server[$name]), FILTER_SANITIZE_STRING);
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
        $post = $this->post;

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
        $get = $this->get;
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
        $files = $this->files;

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
        $cookie = $this->cookie;
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

        foreach (array_keys($this->cookie) as $key) {
            $this->deleteCookie($key);
        }

        return true;
    }

}
