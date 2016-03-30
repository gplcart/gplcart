<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\exceptions;

use core\Exception;

class SystemFailure extends Exception
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

    /**
     * Common exception handler
     */
    public function exceptionHandler($exception)
    {
        $this->log();
        $message = get_class($exception) . ": " . $exception->getMessage() . "\n\n";
        $message .= $this->getFormattedMessage();
        echo $message;
    }

}
