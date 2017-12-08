<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to logging out users
 */
class UserLogOut extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Logs out a user
     */
    public function userLogOut()
    {
        $result = $this->user->logout();
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
