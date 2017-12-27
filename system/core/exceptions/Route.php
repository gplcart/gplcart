<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\exceptions;

use LogicException;

class Route extends LogicException
{

    /**
     * @param string|null $message
     * @param integer $code
     * @param CoreException $previous
     */
    public function __construct($message = null, $code = 0, $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

}
