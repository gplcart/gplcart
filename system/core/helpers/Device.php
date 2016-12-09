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
 * Wrapper methods for device detector library
 */
class Device
{

    /**
     * Library class instance
     * @var \Mobile_Detect $lib
     */
    protected $lib;

    /**
     * Constructor
     */
    public function __construct()
    {
        gplcart_require_library('mobile-detect/Mobile_Detect.php');
        $this->lib = new \Mobile_Detect;
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
