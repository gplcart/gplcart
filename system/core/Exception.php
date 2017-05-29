<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception as BaseException;

/**
 * Base system exception class
 */
class Exception extends BaseException
{

    /**
     * @param mixed $message
     * @param integer $code
     * @param mixed $previous
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
