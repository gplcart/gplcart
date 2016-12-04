<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Collection of helper methods to work with arrays
 */
class Arr
{

    /**
     * Sorts an array by the weight
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
     * Removes junk from array values
     * @param array $array
     * @param boolean $filter
     * @return array
     */
    public static function trim(array &$array, $filter = false)
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
    public static function merge(array &$array1, array &$array2)
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                $merged[$key] = static::merge($merged[$key], $value);
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
    public static function getValue(array &$array, $parents, $glue = '.')
    {
        $ref = &$array;

        if (is_string($parents)) {
            $parents = explode($glue, $parents);
        }

        foreach ($parents as $parent) {
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
     * @param array $parents
     * @param mixed $value
     * @param string $glue
     */
    public static function setValue(array &$array, $parents, $value, $glue = '.')
    {
        $ref = &$array;

        if (is_string($parents)) {
            $parents = explode($glue, $parents);
        }

        foreach ($parents as $parent) {
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
    public static function unsetValue(array &$array, $parents, $glue = '.')
    {
        if (is_string($parents)) {
            $parents = explode($glue, $parents);
        }

        $key = array_shift($parents);

        if (empty($parents)) {
            unset($array[$key]);
        } else {
            static::unsetValue($array[$key], $parents);
        }
    }

    /**
     * Transforms a multi-dimensional array into simple 2 dimensional array
     * @param array $array
     * @return array
     */
    public static function flatten(array $array)
    {
        $return = array();
        array_walk_recursive($array, function ($a) use (&$return) {
            $return[] = $a;
        });

        return $return;
    }

}
