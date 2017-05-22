<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\exceptions;

use gplcart\core\exceptions\AuthorizationException;

class OauthAuthorizationException extends AuthorizationException
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
