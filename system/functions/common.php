<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Deletes a variable from the static storage
 * Taken from Drupal
 * @param string|null $name
 */
function gplcart_cache_clear($name = null)
{
    gplcart_cache($name, null, true);
}

/**
 * Central static variable storage
 * Taken from Drupal
 * @param string $name
 * @param mixed $default_value
 * @param boolean $reset
 * @return mixed
 */
function &gplcart_cache($name, $default_value = null, $reset = false)
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
 * Converts human readable file syzes to numeric bytes
 * @param type $value
 * @return int
 */
function gplcart_to_bytes($value)
{
    $unit = strtolower(substr($value, -1, 1));
    $value = (int) $value;
    switch ($unit) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }

    return $value;
}
