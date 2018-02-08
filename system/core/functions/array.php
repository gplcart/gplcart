<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Sorts an array by the weight
 * @param array $array
 * @param string $key
 * @param bool $asc
 */
function gplcart_array_sort(array &$array, $key = 'weight', $asc = true)
{
    uasort($array, function ($a, $b) use ($key, $asc) {

        $arg1 = is_array($a) && isset($a[$key]) ? $a[$key] : 0;
        $arg2 = is_array($b) && isset($b[$key]) ? $b[$key] : 0;

        if ($arg1 == $arg2) {
            return 0;
        }

        if ($asc) {
            return ($arg1 < $arg2) ? -1 : 1;
        }

        return ($arg1 < $arg2) ? 1 : -1;
    });
}

/**
 * Removes junk from array values
 * @param array $array
 * @param boolean $filter
 * @return array
 */
function gplcart_array_trim(array &$array, $filter = false)
{
    array_walk_recursive($array, function (&$value) use ($filter) {
        $value = trim($value);
        if ($filter) {
            $value = filter_var($value, FILTER_SANITIZE_STRING);
        }
    });

    return $array;
}

/**
 * Recursively merges two arrays
 * @param array $array1
 * @param array $array2
 * @return array
 */
function gplcart_array_merge(array &$array1, array &$array2)
{
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
        if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
            $merged[$key] = gplcart_array_merge($merged[$key], $value);
        } else {
            $merged [$key] = $value;
        }
    }
    return $merged;
}

/**
 * Returns a value from a nested array with variable depth
 * @param array $array
 * @param array|string $parents
 * @param string $glue
 * @return mixed
 */
function gplcart_array_get(&$array, $parents, $glue = '.')
{
    if (is_string($parents)) {
        $parents = explode($glue, $parents);
    }

    $ref = &$array;

    foreach ((array) $parents as $parent) {
        if (is_array($ref) && array_key_exists($parent, $ref)) {
            $ref = &$ref[$parent];
        } else {
            return null;
        }
    }
    return $ref;
}

/**
 * Sets a value in a nested array with variable depth
 * @param array $array
 * @param array|string $parents
 * @param mixed $value
 * @param string $glue
 */
function gplcart_array_set(&$array, $parents, $value, $glue = '.')
{
    if (is_string($parents)) {
        $parents = explode($glue, $parents);
    }

    $ref = &$array;

    foreach ((array) $parents as $parent) {
        if (isset($ref) && !is_array($ref)) {
            $ref = array();
        }

        $ref = &$ref[$parent];
    }

    $ref = $value;
}

/**
 * Removes a value in a nested array with variable depth
 * @param array $array
 * @param array|string $parents
 * @param string $glue
 */
function gplcart_array_unset(&$array, $parents, $glue = '.')
{
    if (is_string($parents)) {
        $parents = explode($glue, $parents);
    }

    $key = array_shift($parents);

    if (empty($parents)) {
        unset($array[$key]);
    } else {
        gplcart_array_unset($array[$key], $parents);
    }
}

/**
 * Transforms a multi-dimensional array into simple 2 dimensional array
 * @param array $array
 * @return array
 */
function gplcart_array_flatten(array $array)
{
    $return = array();
    array_walk_recursive($array, function ($a) use (&$return) {
        $return[] = $a;
    });

    return $return;
}

/**
 * Split the given array into n number of pieces
 * @param array $list
 * @param integer $p
 * @return array
 */
function gplcart_array_split(array $list, $p)
{
    $listlen = count($list);
    $partlen = floor($listlen / $p);
    $partrem = $listlen % $p;

    $mark = 0;
    $partition = array();

    for ($px = 0; $px < $p; $px++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }

    return $partition;
}

/**
 * Generates a hash from an array
 * @param array $data
 * @return string
 */
function gplcart_array_hash(array $data)
{
    $hash = reset($data);
    $key = key($data);

    settype($hash, 'array');
    $array = gplcart_array_flatten($hash);

    sort($array);

    return "$key." . md5(json_encode($array));
}
