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
     * Language code from URL
     * @var string
     */
    protected $langcode = '';

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setGet();
        $this->setPost();
        $this->setCookies();
        $this->setFiles();
    }

    /**
     * Sets $_GET variables
     * @param null|array $data
     * @return $this
     */
    public function setGet($data = null)
    {
        $this->get = isset($data) ? $data : $_GET;
        return $this;
    }

    /**
     * Sets $_POST variables
     * @param null|array $data
     * @return $this
     */
    public function setPost($data = null)
    {
        $this->post = isset($data) ? $data : $_POST;
        return $this;
    }

    /**
     * Sets $_COOKIE variables
     * @param null|array $data
     * @return $this
     */
    public function setCookies($data = null)
    {
        $this->cookie = isset($data) ? $data : $_COOKIE;
        return $this;
    }

    /**
     * Sets $_FILES variables
     * @param null|array $data
     * @return $this
     */
    public function setFiles($data = null)
    {
        $this->files = isset($data) ? $data : $_FILES;
        return $this;
    }

    /**
     * Sets a language code
     * @param string $langcode
     * @return $this
     */
    public function setLangcode($langcode)
    {
        $this->langcode = $langcode;
        return $this;
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
     * Returns a language suffix from the URL
     * @return string
     */
    public function getLangcode()
    {
        return $this->langcode;
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
