<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\exceptions;

use gplcart\core\Exception as CoreException;

class DatabaseException extends CoreException
{

    /**
     * @param string|null $message
     * @param integer $code
     * @param CoreException $previous
     */
    public function __construct($message = null, $code = 0,
            CoreException $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
