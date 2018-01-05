<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Contains methods to work with lists (sorting, filtering)
 */
trait Listing
{

    /**
     * Limit the list using the limit
     * @param array $list
     */
    protected function limitList(array &$list, array $limit)
    {
        list($from, $to) = $limit;
        $list = array_slice($list, $from, $to, true);
    }

    /**
     * Sort the list by a field
     * @param array $list
     * @param array $allowed
     * @param array $query
     * @param array $default
     * @return array
     */
    protected function sortList(array &$list, array $allowed, array $query, array $default = array())
    {
        if (empty($default)) {
            $default = array('sort' => '', 'order' => '');
        } else {
            $order = reset($default);
            $field = key($default);
            $default = array('sort' => $field, 'order' => $order);
        }

        $query += $default;

        if (in_array($query['sort'], $allowed)) {
            uasort($list, function ($a, $b) use ($query) {
                return $this->callbackSortList($a, $b, $query);
            });
        }

        return $list;
    }

    /**
     * Filter the list by a field
     * @param array $list
     * @param array $allowed_fields
     * @param array $query
     * @return array
     */
    protected function filterList(array &$list, array $allowed_fields, array $query)
    {
        $filter = array_intersect_key($query, array_flip($allowed_fields));

        if (empty($filter)) {
            return $list;
        }

        $filtered = array_filter($list, function ($item) use ($filter) {
            return $this->callbackFilterList($item, $filter);
        });

        return $list = $filtered;
    }

    /**
     * Callback for array_filter() function
     * @param array $item
     * @param array $filter
     * @return bool
     */
    protected function callbackFilterList(array $item, $filter)
    {
        foreach ($filter as $field => $term) {

            if (empty($item[$field])) {
                $item[$field] = '0';
            }

            if (!is_string($item[$field])) {
                $item[$field] = (string) (int) !empty($item[$field]);
            }

            if (stripos($item[$field], $term) === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Callback function for uasort()
     * @param array $a
     * @param array $b
     * @param array $query
     * @return int
     */
    protected function callbackSortList($a, $b, array $query)
    {
        $arg1 = isset($a[$query['sort']]) ? (string) $a[$query['sort']] : '0';
        $arg2 = isset($b[$query['sort']]) ? (string) $b[$query['sort']] : '0';

        $diff = strnatcasecmp($arg1, $arg2);
        return $query['order'] === 'asc' ? $diff : -$diff;
    }

}
