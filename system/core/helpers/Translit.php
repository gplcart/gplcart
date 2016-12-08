<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

use BadMethodCallException;

/**
 * Wrapper methods for translit library
 */
class Translit
{

    /**
     * Library class instance
     * @var \Translit $lib
     */
    protected $lib;

    /**
     * Constructor
     */
    public function __construct()
    {
        require_once GC_LIBRARY_DIR . '/translit/Translit.php';
        $this->lib = new \Translit;
    }

    /**
     * Access to library methods
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, array $arguments)
    {
        if (is_callable(array($this->lib, $method))) {
            return call_user_func_array(array($this->lib, $method), $arguments);
        }

        throw new BadMethodCallException("No such method exists: $method");
    }

}
