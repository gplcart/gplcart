<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\classes;

use BadMethodCallException;

/**
 * Wrapper methods for device detector class
 * TODO: Extend namespaces version of detector instead of using __call()
 */
class Device
{

    /**
     * Mobile detector class instance
     * @var \Mobile_Detect $detector
     */
    protected $detector;

    /**
     * Constructor
     */
    public function __construct()
    {
        require_once GC_LIBRARY_DIR . '/mobile-detect/Mobile_Detect.php';
        $this->detector = new \Mobile_Detect;
    }

    /**
     * Access to detector's methods
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws BadMethodCallException
     */
    public function __call($method, array $arguments)
    {
        if (is_callable(array($this->detector, $method))) {
            return call_user_func_array(array($this->detector, $method), $arguments);
        }

        throw new BadMethodCallException("No such method exists: $method");
    }
}
