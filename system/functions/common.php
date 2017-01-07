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
    $pattern = '/^' . str_replace('/', '\/', $pattern) . '$/';

    if (preg_match($pattern, $string, $params)) {
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
 * Wrapper for Kint's ddd() debugger
 * @param mixed $data
 */
function ddd($data)
{
    $file = GC_VENDOR_DIR . '/gplcart/libraries/kint/Kint.class.php';
    require_once $file;

    if (Kint::enabled()) {
        Kint::dump($data);
        exit;
    }
}

/**
 * Wrapper for Kint's d() debugger
 * @param mixed $data
 */
function d($data)
{
    $file = GC_VENDOR_DIR . '/gplcart/libraries/kint/Kint.class.php';
    require_once $file;

    if (Kint::enabled()) {
        Kint::dump($data);
    }
}

/**
 * Returns an absolute path to the file
 * @param string $file
 * @return string
 */
function gplcart_absolute_path($file)
{
    return GC_ROOT_DIR . "/$file";
}

/**
 * Extracts an array of components from strings like ">= 1.0.0"
 * @param string $data
 * @return array
 */
function gplcart_version_components($data)
{
    $string = str_replace(' ', '', $data);

    $matches = array();
    preg_match_all('/(^(==|=|!=|<>|>|<|>=|<=)?(?=\d))(.*)/', $string, $matches);

    if (empty($matches[3][0])) {
        return array();
    }

    $operator = empty($matches[2][0]) ? '=' : $matches[2][0];
    return array($operator, $matches[3][0]);
}

/**
 * Returns PHP info as a string
 * @return string
 */
function gplcart_phpinfo()
{
    ob_start();
    phpinfo(INFO_MODULES);
    $result = ob_get_clean();

    // remove auth data
    if (isset($_SERVER['AUTH_USER'])) {
        $result = str_replace($_SERVER['AUTH_USER'], '***', $$result);
    }

    if (isset($_SERVER['AUTH_PASSWORD'])) {
        $result = str_replace($_SERVER['AUTH_PASSWORD'], '***', $result);
    }

    $result = preg_replace('%^.*<body>(.*)</body>.*$%ms', '$1', $result);
    $result = str_replace('<table', '<table class="table"', $result);

    return $result;
}

/**
 * Measures script execution time in seconds
 * @return string
 */
function gplcart_timer()
{
    $time = microtime(true) - GC_START;
    return number_format($time, 3);
}
