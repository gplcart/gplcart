<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * TODO: Extend namespaces version of detector instead of using __call()
 */

namespace core\classes;

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
    public function __call($method, $arguments)
    {
        if (is_callable(array($this->detector, $method))) {
            return call_user_func_array(array($this->detector, $method), $arguments);
        }

        throw new \BadMethodCallException("No such method exists: $method");
    }
}
