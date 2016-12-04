<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Collection of helper methods used for different purposes
 */
class Tool
{

    /**
     * Sets a cookie
     * @param string $name
     * @param string $value
     * @param integer $lifespan
     * @return boolean
     */
    public static function setCookie($name, $value, $lifespan = 31536000)
    {
        return setcookie(GC_COOKIE_PREFIX . $name, $value, GC_TIME + $lifespan, '/');
    }

    /**
     * Returns a cookie
     * @param string $name
     * @param mixed $default
     * @param bool $filter
     * @return mixed
     */
    public static function getCookie(
    $name = null, $default = null, $filter = true
    )
    {
        $cookie = empty($_COOKIE) ? array() : $_COOKIE;
        Arr::trim($cookie, $filter);

        if (isset($name)) {
            return isset($cookie[GC_COOKIE_PREFIX . $name]) ? $cookie[GC_COOKIE_PREFIX . $name] : $default;
        }

        return $cookie;
    }

    /**
     * Deletes a cookie
     * @param string $name
     * @return boolean
     */
    public static function deleteCookie($name = null)
    {
        if (!isset($name)) {
            foreach ((array) $_COOKIE as $key => $value) {
                if (0 === strpos($key, GC_COOKIE_PREFIX)) {
                    static::deleteCookie($key);
                }
            }

            return true;
        }

        if (isset($_COOKIE[GC_COOKIE_PREFIX . $name])) {
            unset($_COOKIE[GC_COOKIE_PREFIX . $name]);
            return setcookie(GC_COOKIE_PREFIX . $name, '', GC_TIME - 3600, '/');
        }

        return false;
    }

    /**
     * Returns a hashed string
     * @param string $string
     * @param string $salt
     * @param integer $iterations
     * @return string
     */
    public static function hash($string, $salt = '', $iterations = 10)
    {
        if ($salt === '') {
            $salt = self::randomString();
        }

        if (!empty($iterations)) {
            $salt = sprintf("$2a$%02d$", $iterations) . $salt;
        }

        return crypt($string, $salt);
    }

    /**
     * Generates a random string
     * @param integer $size
     * @return string
     */
    public static function randomString($size = 32)
    {
        return bin2hex(openssl_random_pseudo_bytes($size));
    }

    /**
     * Compares two hashed strings
     * @param string $str1
     * @param string $str2
     * @return boolean
     */
    public static function hashEquals($str1, $str2)
    {
        if (function_exists('hash_equals')) {
            return hash_equals($str1, $str2);
        }

        if (strlen($str1) != strlen($str2)) {
            return false;
        }

        $res = $str1 ^ $str2;
        $ret = 0;

        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }

        return !$ret;
    }

    /**
     * Replaces placeholders an the string
     * @param string $pattern
     * @param array $placeholders
     * @param array $data
     * @return string
     */
    public static function replacePlaceholders($pattern, array $placeholders,
            array $data)
    {
        foreach ($placeholders as $placeholder => $data_key) {
            if (!isset($data[$data_key]) || !is_string($data[$data_key])) {
                unset($placeholders[$placeholder]);
                continue;
            }

            $placeholders[$placeholder] = $data[$data_key];
        }

        return $placeholders ? strtr($pattern, $placeholders) : '';
    }

    /**
     * Generates an array of time zones and their local data
     * @return array
     */
    public static function timezones()
    {
        $zones = array();
        $timestamp = GC_TIME;
        $default_timezone = date_default_timezone_get();

        foreach (timezone_identifiers_list() as $zone) {
            date_default_timezone_set($zone);
            $zones[$zone] = '(UTC/GMT ' . date('P', $timestamp) . ') ' . $zone;
        }

        date_default_timezone_set($default_timezone);
        return $zones;
    }

    /**
     * Splits a text by new lines
     * @param string $string
     * @param null|int $limit
     * @return array
     */
    public static function stringToArray($string, $limit = null)
    {
        $array = explode("\n", str_replace("\r", "", $string), $limit);
        return array_filter(array_map('trim', $array));
    }

    /**
     * Formats a string by replacing variable placeholders
     * @param string $string A string containing placeholders
     * @param array $arguments An associative array of replacements
     * @return string
     */
    public static function formatString($string, array $arguments = array())
    {
        foreach ($arguments as $key => $value) {
            switch ($key[0]) {
                case '@':
                    $arguments[$key] = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
                    break;
                case '!':
                    // Html
                    break;
                case '%':
                default:
                    $arguments[$key] = '<i class="placeholder">' . htmlspecialchars($value, ENT_QUOTES, 'UTF-8') . '</i>';
            }
        }

        return strtr($string, $arguments);
    }

    /**
     * Validates a domain name, e.g domain.com
     * @param string $domain
     * @return boolean
     */
    public static function validDomain($domain)
    {
        $pattern = '/^(?!\-)'
                . '(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.)'
                . '{1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';

        return (bool) preg_match($pattern, $domain);
    }

    /**
     * Parses and extracts arguments from a string
     * @param string $string
     * @param string $pattern
     * @return boolean|array
     */
    public static function patternMatch($string, $pattern)
    {
        $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

        if (preg_match($pattern, $string, $params)) {
            array_shift($params);
            return array_values($params);
        }

        return false;
    }

    /**
     * Converts string to boolean type
     * @param string $value
     * @return boolean
     */
    public static function toBool($value)
    {
        if (!is_string($value)) {
            return (bool) $value;
        }

        $v = strtolower($value);

        $map = array(
            'y' => true,
            'n' => false,
            'yes' => true,
            'no' => false,
            'true' => true,
            'false' => false,
            '1' => true,
            '0' => false,
            'on' => true,
            'off' => false,
        );

        return isset($map[$v]) ? $map[$v] : false;
    }

}
