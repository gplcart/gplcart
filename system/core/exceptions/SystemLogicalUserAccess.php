<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\exceptions;

use core\exceptions\SystemLogical;

class SystemLogicalUserAccess extends SystemLogical
{

    /**
     * Constructor
     * @param string|null $message
     * @param integer $code
     * @param Exception $previous
     */
    public function __construct($message = null, $code = 0, SystemLogical $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
