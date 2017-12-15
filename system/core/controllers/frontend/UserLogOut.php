<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\UserAccess as UserAccessModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to logging out users
 */
class UserLogOut extends FrontendController
{

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAccess $user_access
     */
    protected $user_access;

    /**
     * @param UserAccessModel $user_access
     */
    public function __construct(UserAccessModel $user_access)
    {
        parent::__construct();

        $this->user_access = $user_access;
    }

    /**
     * Logs out a user
     */
    public function userLogOut()
    {
        $result = $this->user_access->logout();
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
