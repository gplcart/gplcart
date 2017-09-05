<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

/**
 * Provides methods to work with URLs
 */
class Url
{

    /**
     * Request class instance
     * @var \gplcart\core\helpers\Request $request
     */
    protected $request;

    /**
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Redirects the user to a new location
     * @param string|null $url
     * @param array $options
     * @param boolean $full
     * @param boolean $exclude_langcode
     * @return null
     */
    final public function redirect($url = '', $options = array(), $full = false,
            $exclude_langcode = false)
    {
        if (!isset($url)) {
            return null;
        }

        if (!empty($url) && ($full || $this->isAbsolute($url))) {
            header("Location: $url");
            exit;
        }

        $target = $this->request->get('target', '', 'string');

        if (!empty($target)) {
            $url = (string) parse_url($target, PHP_URL_PATH);
            $parsed = parse_url($target, PHP_URL_QUERY);
            $options = is_array($parsed) ? $parsed : array();
        }

        header('Location: ' . $this->get($url, $options, false, $exclude_langcode));
        exit;
    }

    /**
     * Check if the URL is a valid absolute URL
     * @param string $url
     * @return boolean
     */
    public function isAbsolute($url)
    {
        $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

        return preg_match($pattern, $url) === 1;
    }

    /**
     * Returns an internal or external URL
     * @param string $path
     * @param array $options
     * @param boolean $absolute
     * @param boolean $exclude_langcode
     * @return string
     */
    public function get($path = '', $options = array(), $absolute = false,
            $exclude_langcode = false)
    {
        $pass_absolute = false;

        if (!empty($path)) {
            if ($absolute && $this->isAbsolute($path)) {
                $url = $path;
                $pass_absolute = true;
            } else {
                $url = $this->request->base($exclude_langcode) . trim($path, '/');
            }
        } else {
            $url = $this->request->urn();
        }

        $url = strtok($url, '?');

        if ($absolute && !$pass_absolute) {
            $host = $this->request->host();
            $scheme = $this->request->scheme();
            $url = "$scheme$host$url";
        }

        if (!empty($options)) {
            $url .= '?' . http_build_query($options);
        }

        return $url;
    }

    /**
     * Returns a URL with appended language code
     * @param string $code
     * @param string $path
     * @param array $options
     * @return string
     */
    public function language($code, $path = '', $options = array())
    {
        $segments = $this->segments(true, $path);
        $langcode = $this->request->getLangcode();

        // Remove an existing language code
        if (isset($segments[0]) && $segments[0] === $langcode) {
            unset($segments[0]);
        }

        // Prepend language code
        array_unshift($segments, $code);

        $url = $this->request->base(true);
        $url .= trim(implode('/', $segments), '/');

        if (!empty($options)) {
            $url .= '?' . http_build_query($options);
        }

        return $url;
    }

    /**
     * Returns true if the path is a public area
     * @return boolean
     */
    public function isFrontend()
    {
        return !$this->isBackend();
    }

    /**
     * Returns true if the path is an admin area
     * @return boolean
     */
    public function isBackend()
    {
        $segments = $this->segments();
        return isset($segments[0]) && $segments[0] === 'admin';
    }

    /**
     * Returns an array containing all the components of the current path
     * @param boolean $append_langcode
     * @param string $path
     * @return array
     */
    public function segments($append_langcode = false, $path = '')
    {
        if (empty($path)) {
            $path = $this->path($append_langcode);
        }

        return explode('/', trim($path, '/'));
    }

    /**
     * Returns an internal path without query
     * @param boolean $append_langcode
     * @return string
     */
    public function path($append_langcode = false)
    {
        $urn = $this->request->urn();
        return substr(strtok($urn, '?'), strlen($this->request->base($append_langcode)));
    }

    /**
     * Returns true if the path is admin dashboard
     * @return boolean
     */
    public function isDashboard()
    {
        $segments = $this->segments();
        return isset($segments[0]) && $segments[0] === 'admin' && empty($segments[1]);
    }

    /**
     * Returns true if the path is a /install page
     * @return boolean
     */
    public function isInstall()
    {
        $segments = $this->segments();
        return isset($segments[0]) && $segments[0] === 'install';
    }

    /**
     * Returns true if the path is front page
     * @return boolean
     */
    public function isFront()
    {
        $segments = $this->segments();
        return empty($segments[0]);
    }

    /**
     * Whether the path belongs to an account URL
     * @return boolean
     */
    public function isAccount()
    {
        $id = $this->getAccountId();
        return is_integer($id);
    }

    /**
     * Returns a user ID from the path
     * @return boolean|integer
     */
    public function getAccountId()
    {
        $segments = $this->segments();
        if (reset($segments) === 'account' && isset($segments[1]) && ctype_digit($segments[1])) {
            return (int) $segments[1];
        }
        return false;
    }

}
