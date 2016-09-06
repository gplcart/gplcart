<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use Exception as BaseException;

/**
 * Base system exception class
 */
class Exception extends BaseException
{

    /**
     * Constructor
     * @param mixed $message
     * @param integer $code
     * @param Base $previous
     */
    public function __construct($message = null, $code = 0,
            BaseException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
