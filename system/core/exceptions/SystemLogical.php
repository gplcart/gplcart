<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\exceptions;

use core\Exception;

class SystemLogical extends Exception
{

    /**
     * Constructor
     * @param string|null $message
     * @param integer $code
     * @param Exception $previous
     */
    public function __construct($message = null, $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
