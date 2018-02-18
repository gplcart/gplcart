<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\UserAction;

/**
 * Handles incoming requests and outputs data related to logging out users
 */
class UserLogOut extends Controller
{

    /**
     * User access model instance
     * @var \gplcart\core\models\UserAction $user_action
     */
    protected $user_action;

    /**
     * @param UserAction $user_action
     */
    public function __construct(UserAction $user_action)
    {
        parent::__construct();

        $this->user_action = $user_action;
    }

    /**
     * Logs out a user
     */
    public function userLogOut()
    {
        $result = $this->user_action->logout();
        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
