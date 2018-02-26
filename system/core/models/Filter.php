<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\models;

use gplcart\core\Hook;

/**
 * Manages basic behaviors and data related to HTML filters
 */
class Filter
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Filter a text string
     * @param string $text
     * @param string|array $filter
     * @return string
     */
    public function run($text, $filter)
    {
        if (is_string($filter)) {
            $filter = $this->get($filter);
        }

        $filtered = null;
        $this->hook->attach('filter', $text, $filter, $filtered, $this);

        if (isset($filtered)) {
            return (string) $filtered;
        }

        return $this->filter($text);
    }

    /**
     * Default and the most secure XSS filter
     * @param string $text
     * @return string
     */
    protected function filter($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Returns a filter
     * @param string $filter_id
     * @return array
     */
    public function get($filter_id)
    {
        $filters = $this->getHandlers();
        return empty($filters[$filter_id]) ? array() : $filters[$filter_id];
    }

    /**
     * Returns a filter for the given user role ID
     * @param integer $role_id
     * @return array
     */
    public function getByRole($role_id)
    {
        foreach ($this->getHandlers() as $filter) {
            if (in_array($role_id, (array) $filter['role_id'])) {
                return $filter;
            }
        }

        return array();
    }

    /**
     * Returns an array of defined filters
     * @return array
     */
    public function getHandlers()
    {
        $filters = &gplcart_static('filter.handlers');

        if (isset($filters)) {
            return $filters;
        }

        $filters = array();
        $this->hook->attach('filter.handlers', $filters, $this);

        foreach ($filters as $id => &$filter) {
            $filter['filter_id'] = $id;
            $filter += array(
                'role_id' => array(),
                'status' => true,
                'module' => null
            );
        }

        return $filters;
    }

}
