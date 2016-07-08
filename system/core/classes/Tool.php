<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

/**
 * Collection of helper methods used for different purposes
 */
class Tool
{

    /**
     * Sorts an array by the weight element
     * @param array $array
     */
    public static function sortWeight(array &$array)
    {
        uasort($array, function ($a, $b) {
            $a_weight = (is_array($a) && isset($a['weight'])) ? $a['weight'] : 0;
            $b_weight = (is_array($b) && isset($b['weight'])) ? $b['weight'] : 0;

            if ($a_weight == $b_weight) {
                return 0;
            }
            return ($a_weight < $b_weight) ? -1 : 1;
        });
    }

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
    public static function getCookie($name = null, $default = null,
            $filter = true)
    {

        $cookie = empty($_COOKIE) ? array() : $_COOKIE;

        Tool::trimArray($cookie, $filter);

        if (isset($name)) {
            return isset($cookie[GC_COOKIE_PREFIX . $name]) ? $cookie[GC_COOKIE_PREFIX . $name] : $default;
        }

        return $cookie;
    }

    /**
     * Removes junk from array values
     * @param array $array
     * @param boolean $filter
     * @return array
     */
    public static function trimArray(array &$array, $filter = false)
    {
        if ($filter) {
            array_walk_recursive($array, function (&$v) {
                $v = filter_var(trim($v), FILTER_SANITIZE_STRING);
            });

            return $array;
        }

        array_walk_recursive($array, function (&$v) {
            $v = trim($v);
        });

        return $array;
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
     * Scans a directory and deletes its files that match a specific condition
     * @param string $directory
     * @param mixed $pattern Either an array of extensions
     * or a pattern for glob()
     * @param integer $lifespan
     * @return integer
     */
    public static function deleteFiles($directory, $pattern, $lifespan = 0)
    {
        $deleted = 0;
        foreach (static::scanFiles($directory, $pattern) as $file) {
            if ((filemtime($file) < GC_TIME - $lifespan) && unlink($file)) {
                $deleted++;
            }
        }

        return $deleted;
    }

    /**
     * Finds all files matching a given pattern in a given directory
     * @param string $path
     * @param string $pattern Either an array of allowed extensions or a pattern for glob()
     * @return array
     */
    public static function scanFiles($path, $pattern)
    {
        if (is_array($pattern)) {
            $extensions = implode(',', $pattern);
            return glob("$path/*.{{$extensions}}", GLOB_BRACE);
        }

        return glob("$path/$pattern");
    }

    /**
     * Allows to read a large file. Replacement of readfile()
     * @param string $filename
     * @param bool $retbytes
     * @param type $chunksize
     * @return boolean
     */
    public static function readfile($filename, $retbytes = true,
            $chunksize = 1048576)
    {
        $cnt = 0;
        $buffer = '';

        $handle = fopen($filename, 'rb');

        if ($handle === false) {
            return false;
        }

        while (!feof($handle)) {
            $buffer = fread($handle, $chunksize);
            echo $buffer;

            ob_flush();
            flush();

            if ($retbytes) {
                $cnt += strlen($buffer);
            }
        }

        $status = fclose($handle);

        if ($retbytes && $status) {
            return $cnt;
        }

        return $status;
    }

    /**
     * Recursively merges two arrays
     * @param array $array1
     * @param array $array2
     * @return array
     */
    public static function merge(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = self::merge($merged[$key], $value);
            } else {
                $merged [$key] = $value;
            }
        }
        return $merged;
    }

    /**
     * Returns a hashed string
     * @param string $string
     * @param type $salt
     * @param type $iterations
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
     * Creates a .htaccess file in a given directory
     * @param string $directory
     * @param boolean $private
     * @param string $content
     * @return boolean
     */
    public static function htaccess($directory, $private = true, $content = '')
    {
        if ($content === '') {
            $content = 'Options None' . PHP_EOL;
            $content .= 'Options +FollowSymLinks' . PHP_EOL;
            $content .= 'SetHandler Gplcart_Dont_Touch' . PHP_EOL;
            $content .= 'php_flag engine off' . PHP_EOL;
        }

        if ($private) {
            $content = 'Deny from all' . PHP_EOL . $content;
        }

        $file = $directory . '/.htaccess';

        if (file_put_contents($file, $content) !== false) {
            chmod($file, 0444);
            return true;
        }

        return false;
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
     * Checks if a timestamp is in a given time range
     * @param array $ranges An array of ranges,
     * e.g $ranges = ['Mon' => ['09:00 AM' => '12:00 AM']]
     * @param mixed $timestamp a UNIX-timestamp or null for the current time
     * @return boolean Returns true if in the range, false otherwise
     */
    public static function inTimeRange(array $ranges, $timestamp = null)
    {

        if (!isset($timestamp)) {
            $timestamp = GC_TIME;
        }

        $date = date_create();
        $current = date_timestamp_set($date, $timestamp);

        foreach ($ranges[date('D', $timestamp)] as $start => $end) {
            $start = date_create_from_format('h:i A', $start);
            $end = date_create_from_format('h:i A', $end);

            if (($start < $current) && ($current < $end)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Splits a text by new lines
     * @param string $string A text
     * @limit integer $limit If set the returned array will contain a maximum of
     * limit elements with the last element containing the rest of string
     * @return array
     */
    public static function stringToArray($string, $limit = null)
    {
        if (isset($limit)) {
            $array = explode("\n", str_replace("\r", "", $string), $limit);
        } else {
            $array = explode("\n", str_replace("\r", "", $string));
        }

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
     * Writes a CSV file
     * @param string $file An absolute path to the file
     * @param array $data An array of fields to be written
     * @param string $delimiter A field delimiter (one character)
     * @param string $enclosure A field enclosure character (one character)
     * @param integer $limit Max file size
     * @return boolean Returns true on success, false otherwise
     */
    public static function writeCsv($file, array $data, $delimiter = ',',
            $enclosure = '"', $limit = 0)
    {

        $handle = fopen($file, 'a+');

        if ($handle === false) {
            return false;
        }

        if (!empty($limit) && filesize($file) > $limit) {
            ftruncate($handle, 0);
            rewind($handle);
        }

        fputcsv($handle, $data, $delimiter, $enclosure);
        fclose($handle);

        return true;
    }

}
