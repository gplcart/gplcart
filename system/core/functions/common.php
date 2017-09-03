<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Returns the core version
 * @param bool $major
 * @return string
 */
function gplcart_version($major = false)
{
    return $major ? strtok(GC_VERSION, '.') : GC_VERSION;
}

/**
 * Converts human readable file sizes to numeric bytes
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
function gplcart_is_valid_domain($domain)
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
function gplcart_is_absolute_url($url)
{
    $pattern = "/^(?:ftp|https?|feed):\/\/(?:(?:(?:[\w\.\-\+!$&'\(\)*\+,;=]|%[0-9a-f]{2})+:)*
        (?:[\w\.\-\+%!$&'\(\)*\+,;=]|%[0-9a-f]{2})+@)?(?:
        (?:[a-z0-9\-\.]|%[0-9a-f]{2})+|(?:\[(?:[0-9a-f]{0,4}:)*(?:[0-9a-f]{0,4})\]))(?::[0-9]+)?(?:[\/|\?]
        (?:[\w#!:\.\?\+=&@$'~*,;\/\(\)\[\]\-]|%[0-9a-f]{2})*)?$/xi";

    return (bool) preg_match($pattern, $url);
}

/**
 * Whether the path is an absolute server path
 * @param string $path
 * @return bool
 */
function gplcart_is_absolute_path($path)
{
    return strpos($path, GC_ROOT_DIR) === 0;
}

/**
 * Parses and extracts arguments from a path string
 * @param string $string
 * @param string $pattern
 * @return boolean|array
 */
function gplcart_parse_path($string, $pattern)
{
    $arguments = array();

    if (preg_match("~^$pattern$~i", $string, $arguments) === 1) {
        array_shift($arguments);
        return array_values($arguments);
    }

    return false;
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
    if (gplcart_is_absolute_path($file)) {
        return $file;
    }

    return GC_ROOT_DIR . "/$file";
}

/**
 * Converts the file path to a relative path
 * @param string $file
 * @return string
 */
function gplcart_relative_path($file)
{
    if (gplcart_is_absolute_path($file)) {
        return trim(substr($file, strlen(GC_ROOT_DIR)), '/\\');
    }

    return $file;
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

/**
 * Central static variable storage
 * Mostly taken from Drupal
 * @param string|null|array $cid
 * @param mixed $default_value
 * @param boolean $reset
 * @return mixed
 */
function &gplcart_static($cid, $default_value = null, $reset = false)
{
    $name = gplcart_cache_key($cid);

    static $data = array(), $default = array();

    if (isset($data[$name]) || array_key_exists($name, $data)) {
        if ($reset) {
            $data[$name] = $default[$name];
        }

        return $data[$name];
    }

    if (isset($name)) {
        if ($reset) {
            return $data;
        }

        $default[$name] = $data[$name] = $default_value;
        return $data[$name];
    }

    foreach ($default as $name => $value) {
        $data[$name] = $value;
    }

    return $data;
}

/**
 * Reset static cache
 * @param string|array|null $cid
 */
function gplcart_static_clear($cid = null)
{
    gplcart_static(gplcart_cache_key($cid), null, true);
}

/**
 * Generates a cache key from an array of arguments like ('prefix' => array(...))
 * @param string|array|null $data
 * @return string|null
 */
function gplcart_cache_key($data)
{
    if (!isset($data)) {
        return null;
    }

    if (!is_array($data)) {
        return (string) $data;
    }

    list($key, $hash) = each($data);

    settype($hash, 'array');
    ksort($hash);

    return $key . '.' . md5(json_encode($hash));
}
