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
    public function server($var, $default = '')
    {
        $data = filter_input_array(INPUT_SERVER, FILTER_SANITIZE_STRING);
        return isset($data[$var]) ? trim($data[$var]) : $default;
    }

    /**
     * Returns the current base path
     * @staticvar array $cache
     * @param boolean $exclude_langcode
     * @return array
     */
    public function base($exclude_langcode = false)
    {
        static $cache = array();

        if (isset($cache[$exclude_langcode])) {
            return $cache[$exclude_langcode];
        }

        $base = str_replace(array('\\', ' '), array('/', '%20'), dirname($this->server('SCRIPT_NAME')));
        $base = $base === '/' ? '/' : "$base/";

        if (!empty($this->langcode)) {
            $suffix = $this->langcode . '/';
            $base .= $suffix;
        }

        if ($exclude_langcode && !empty($suffix)) {
            $base = substr($base, 0, -strlen($suffix));
        }

        $cache[$exclude_langcode] = $base;
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
     * Returns request method
     * @return string
     */
    public function method()
    {
        return strtoupper($this->server('REQUEST_METHOD', 'GET'));
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
     * Returns request language
     * @return string
     */
    public function language()
    {
        return substr($this->server('HTTP_ACCEPT_LANGUAGE'), 0, 2);
    }

    /**
     * Returns a data from the POST request
     * @param string|array $name
     * @param mixed $default
     * @param bool|string $filter
     * @param null|string $type
     * @return mixed
     */
    public function post($name = null, $default = null, $filter = true,
            $type = null)
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

        return $this->setType($return, $type, $default);
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

        return $this->setType($return, $type, $default);
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
     * Returns a data from the GET request
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

        return $this->setType($return, $type, $default);
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

    /**
     * Type casting
     * @param mixed $value
     * @param null|string $type
     * @param mixed $default
     * @return mixed
     */
    protected function setType($value, $type, $default)
    {
        if (!isset($type)) {
            return $value;
        }

        if (is_array($value) && $type === 'string') {
            return $default;
        }

        if (settype($value, $type)) {
            return $value;
        }

        return $default;
    }

}
