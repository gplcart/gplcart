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
function gplcart_bytes($value)
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
 * Whether the path starts with the given substring
 * @param string $prefix
 * @param string $path
 * @return bool
 */
function gplcart_path_starts($prefix, $path)
{
    $haystack = str_replace('\\', '/', $path);
    $needle = str_replace('\\', '/', $prefix);

    return strpos($haystack, $needle) === 0;
}

/**
 * Parses and extracts arguments from a path string
 * @param string $string
 * @param string $pattern
 * @return boolean|array
 */
function gplcart_path_parse($string, $pattern)
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
 * Whether the path is an absolute server path
 * @param string $path
 * @return bool
 */
function gplcart_path_is_absolute($path)
{
    return gplcart_path_starts(GC_ROOT_DIR, $path);
}

/**
 * Returns an absolute path to the file
 * @param string $file
 * @return string
 */
function gplcart_path_absolute($file)
{
    if (gplcart_path_is_absolute($file)) {
        return $file;
    }

    return GC_ROOT_DIR . "/$file";
}

/**
 * Converts the file path to a relative path
 * @param string $absolute
 * @return string
 */
function gplcart_path_relative($absolute, $prefix = GC_ROOT_DIR)
{
    if (gplcart_path_starts($prefix, $absolute)) {
        $prefix_normalized = str_replace('\\', '/', $prefix);
        $absolute_normalized = str_replace('\\', '/', $absolute);
        return trim(substr($absolute_normalized, strlen($prefix_normalized)), '/\\');
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

/**
 * Central static variable storage. Taken from Drupal
 * @param string|null $name
 * @param mixed $default_value
 * @param boolean $reset
 * @return mixed
 */
function &gplcart_static($name, $default_value = null, $reset = false)
{
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
 * @param string|null $cid
 */
function gplcart_static_clear($cid = null)
{
    gplcart_static($cid, null, true);
}
