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
 * @param string $path
 * @param string $prefix
 * @return bool
 */
function gplcart_path_starts($path, $prefix)
{
    $haystack = str_replace('\\', '/', $path);
    $needle = str_replace('\\', '/', $prefix);

    return strpos($haystack, $needle) === 0;
}

/**
 * Perform a regular expression match on the given path string
 * @param string $path
 * @param string $pattern
 * @param array $arguments
 * @return boolean
 */
function gplcart_path_match($path, $pattern, &$arguments = array())
{
    if (empty($path)) {
        $path = '/';
    }

    $matches = array();
    if (preg_match("~^$pattern$~i", $path, $matches) === 1) {
        array_shift($matches);
        $arguments = array_values($matches);
        return true;
    }

    return false;
}

/**
 * Whether the path is an absolute server path
 * @param string $path
 * @param mixed|string $prefix
 * @return bool
 */
function gplcart_path_is_absolute($path, $prefix = GC_DIR)
{
    return gplcart_path_starts($path, $prefix);
}

/**
 * Returns an absolute path to the file
 * @param string $file
 * @param mixed|string $prefix
 * @return string
 */
function gplcart_path_absolute($file, $prefix = GC_DIR)
{
    if (gplcart_path_is_absolute($file, $prefix)) {
        return $file;
    }

    return "$prefix/$file";
}

/**
 * Converts the file path to a relative path
 * @param string $path
 * @param mixed|string $prefix
 * @return string
 */
function gplcart_path_relative($path, $prefix = GC_DIR)
{
    if (gplcart_path_starts($path, $prefix)) {
        $path_normalized = gplcart_path_normalize($path);
        $prefix_normalized = gplcart_path_normalize($prefix);
        return trim(substr($path_normalized, strlen($prefix_normalized)), '/');
    }

    return $path;
}

/**
 * Converts backward slashes to forward slashes and trims trailing slashes
 * @param string $path
 * @return string
 */
function gplcart_path_normalize($path)
{
    return trim(str_replace('\\', '/', $path), '/');
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

/**
 * Saves PHP configuration in a file
 * @param string $file
 * @param mixed $data
 * @return bool
 */
function gplcart_config_set($file, $data)
{
    if (file_exists($file)) {
        chmod($file, 0644);
    }

    $result = file_put_contents($file, '<?php return ' . var_export($data, true) . ';');

    if ($result !== false) {
        chmod($file, 0444);
        return true;
    }

    return false;
}

/**
 * Returns a configuration data from the file
 * @param string $file
 * @return mixed
 */
function gplcart_config_get($file)
{
    static $data = array();

    if (isset($data[$file])) {
        return $data[$file];
    }

    $data[$file] = null;

    if (is_file($file)) {
        $data[$file] = require $file;
    }

    return $data[$file];
}

/**
 * Deletes a configuration file
 * @param string $file
 * @return boolean
 */
function gplcart_config_delete($file)
{
    if (is_file($file)) {
        chmod($file, 0644);
        return unlink($file);
    }

    return false;
}
