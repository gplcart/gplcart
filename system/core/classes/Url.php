<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

use core\classes\Request;

/**
 * Provides methods to work with URLs
 */
class Url {

    /**
     * Request class instance
     * @var \core\classes\Request $request
     */
    protected $request;

    /**
     * Constructor
     * @param Request $request
     */
    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     * Redirects the user to a new location
     * @param string $url
     * @param array $options
     * @param boolean $full
     */
    public function redirect($url = '', array $options = array(), $full = false) {
        if (!empty($url) && ($full || $this->isAbsolute($url))) {
            header("Location: $url");
            exit;
        }

        $target = $this->request->get('target');

        if (!empty($target)) {
            $url = parse_url($target, PHP_URL_PATH);
            $options = parse_url($target, PHP_URL_QUERY);
        }

        header('Location: ' . $this->get($url, $options));
        exit;
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
            $exclude_langcode = false) {
        if (!empty($path)) {
            $url = $this->request->base($exclude_langcode) . trim($path, '/');
        } else {
            $url = $this->request->urn();
        }

        $url = strtok($url, '?');

        if ($absolute) {
            if (strpos($url, '://') !== false) {
                $url = $path;
            } else {
                $scheme = $this->request->scheme();
                $url = $scheme . $this->request->host() . $url;
            }
        }

        $url = rtrim($url, '/');

        if (!empty($options)) {
            $url .= '?' . http_build_query($options);
        }

        return $url;
    }

    /**
     * Returns an array containing all the components of the current path
     * @param string $path
     * @return array
     */
    public function segments($path = '') {
        if (empty($path)) {
            $path = $this->path();
        }

        return explode('/', trim($path, '/'));
    }

    /**
     * Returns an internal path without query
     * @param string $path
     * @return string
     */
    public function path($path = '') {
        if (empty($path)) {
            $path = $this->request->urn();
        }

        return substr(strtok($path, '?'), strlen($this->request->base()));
    }

    /**
     * Returns true if the path is an admin area
     * @return boolean
     */
    public function isBackend() {
        $segments = $this->segments();
        return (isset($segments[0]) && $segments[0] === 'admin');
    }

    /**
     * Returns true if the path is a public area
     * @return boolean
     */
    public function isFrontend() {
        return !$this->isBackend();
    }

    /**
     * Returns true if the path is admin dashboard
     * @return boolean
     */
    public function isDashboard() {
        $segments = $this->segments();
        return ((isset($segments[0]) && $segments[0] === 'admin') && empty($segments[1]));
    }

    /**
     * Returns true if the path is /install
     * @return boolean
     */
    public function isInstall() {
        $segments = $this->segments();
        return (isset($segments[0]) && $segments[0] === 'install');
    }

    /**
     * Returns true if the path is front page
     * @return type
     */
    public function isFront() {
        $segments = $this->segments();
        return empty($segments[0]);
    }

    /**
     * Returns a user ID from the path if it is /account* path
     * @return boolean
     */
    public function isAccount() {
        $segments = $this->segments();
        if ((reset($segments) === 'account') && (isset($segments[1]) && is_numeric($segments[1]))) {
            return (int) $segments[1];
        }
        return false;
    }

    /**
     * Returns an internal URL from a server path
     * @param string $server_path
     * @return string
     */
    public function fromPath($server_path) {
        $url = $this->request->base() . trim(str_replace(GC_ROOT_DIR, '', $server_path), '/');
        return $url;
    }

    /**
     * Check if the URL is a valid absolute URL
     * @param string $url
     * @return boolean
     */
    protected function isAbsolute($url) {
        return (bool) preg_match("/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi", $url);
    }

}
