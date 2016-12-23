<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Hook;

/**
 * Provides methods to work with 3-d party libraries
 */
class Library
{
    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Constructor
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;
    }

    /**
     * Returns an array of libraries
     * @return array
     */
    public function getList()
    {
        $libraries = &gplcart_cache('libraries');

        if (isset($libraries)) {
            return $libraries;
        }

        $libraries = include_once GC_CONFIG_LIBRARY;

        $this->hook->fire('library', $libraries);
        return $libraries;
    }

}
