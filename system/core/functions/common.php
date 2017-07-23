<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Converts human readable file syzes to numeric bytes
 * @param string $value
 * @return integer
 */
function gplcart_to_bytes($value)
{
    $bytes = (int) $value;
    $unit = strtolower(substr($value, -1, 1));

    switch ($unit) {
        case 'g':
            $bytes *= 1024;
        case 'm':
            $bytes *= 1024;
        case 'k':
            $bytes *= 1024;
    }

    return $bytes;
}

/**
 * Returns XSS-safe JSON string
 * @param mixed $data
 * @param bool $pretty
 * @return string
 */
function gplcart_json_encode($data, $pretty = false)
{
    $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT;

    if ($pretty) {
        $options = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_PRETTY_PRINT;
    }

    return json_encode($data, $options);
}

/**
 * Validates a domain name, e.g domain.com
 * @param string $domain
 * @return boolean
 */
function gplcart_valid_domain($domain)
{
    $pattern = '/^(?!\-)'
            . '(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.)'
            . '{1,126}(?!\d+)[a-zA-Z\d]{1,63}$/';

    return (bool) preg_match($pattern, $domain);
}

/**
 * Whether the URL is absolute, e.g starts with http://, https:// etc
 * @param string $url
 * @return boolean
 */
function gplcart_absolute_url($url)
{
    $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

    return (bool) preg_match($pattern, $url);
}

/**
 * Parses and extracts arguments from a string
 * @param string $string
 * @param string $pattern
 * @return boolean|array
 */
function gplcart_parse_pattern($string, $pattern)
{
    $params = array();
    if (preg_match("~^$pattern$~i", $string, $params) === 1) {
        array_shift($params);
        return array_values($params);
    }
    return false;
}

/**
 * Validates $_SERVER['HTTP_HOST'] variable
 * @return boolean
 */
function gplcart_valid_host($host)
{
    return (strlen($host) <= 1000 //
            && substr_count($host, '.') <= 100 //
            && substr_count($host, ':') <= 100 //
            && preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $host) === 1);
}

/**
 * Generates an array of time zones and their local data
 * @return array
 */
function gplcart_timezones()
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
 * Returns an absolute path to the file
 * @param string $file
 * @return string
 */
function gplcart_absolute_path($file)
{
    if (strpos($file, GC_ROOT_DIR) === 0) {
        return $file;
    }

    return GC_ROOT_DIR . "/$file";
}

/**
 * Converts file from absolute to relative
 * @param string $absolute
 * @return string
 */
function gplcart_relative_path($absolute)
{
    $prefix = GC_ROOT_DIR;
    if (substr($absolute, 0, strlen($prefix)) == $prefix) {
        return ltrim(substr($absolute, strlen($prefix)), '/\\');
    }
    return $absolute;
}

/**
 * Safe type casting
 * @param mixed $value
 * @param null|string $type
 * @param mixed $default
 * @return bool
 */
function gplcart_settype(&$value, $type, $default)
{
    if ($value === $default || empty($type)) {
        return false;
    }

    if (is_array($value) && $type === 'string') {
        $value = $default;
        return false;
    }

    if (settype($value, $type)) {
        return true;
    }

    $value = $default;
    return false;
}
