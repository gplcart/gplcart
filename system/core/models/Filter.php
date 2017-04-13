<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Model,
    gplcart\core\Cache;

/**
 * Manages basic behaviors and data related to HTML filters
 */
class Filter extends Model
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Filter a text string
     * @param string $text
     * @param integer|array $filter
     * @return string
     */
    public function run($text, $filter)
    {
        if (is_string($filter)) {
            $filter = $this->get($filter);
        }

        $filtered = null;
        $this->hook->fire('filter', $text, $filter, $filtered);

        if (isset($filtered)) {
            return $filtered;
        }

        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns a filter
     * @param string $filter_id
     * @return array
     */
    public function get($filter_id)
    {
        $filters = $this->getList();
        return empty($filters[$filter_id]) ? array() : $filters[$filter_id];
    }

    /**
     * Returns a filter for the given user role ID
     * @param integer $role_id
     * @return array
     */
    public function getByRole($role_id)
    {
        foreach ($this->getList() as $filter) {
            if (in_array($role_id, $filter['role_id'])) {
                return $filter;
            }
        }
        return array();
    }

    /**
     * Returns an array of defined filters
     * @return array
     */
    public function getList()
    {
        $filters = &Cache::memory(__METHOD__);

        if (isset($filters)) {
            return $filters;
        }

        $filters = array();
        $this->hook->fire('filter.list', $filters);
        return $filters;
    }

}
