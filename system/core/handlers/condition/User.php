<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\User as UserModel;
use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check user conditions
 */
class User extends BaseHandler
{

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * @param UserModel $user
     */
    public function __construct(UserModel $user)
    {
        $this->user = $user;
    }

    /**
     * Whether the user ID condition is met
     * @param array $condition
     * @return boolean
     */
    public function id(array $condition)
    {
        $user_id = $this->user->getId();
        return $this->compare($user_id, $condition['value'], $condition['operator']);
    }

    /**
     * Whether the user role condition is met
     * @param array $condition
     * @return boolean
     */
    public function roleId(array $condition)
    {
        $role_id = $this->user->getRoleId();
        return $this->compare($role_id, $condition['value'], $condition['operator']);
    }

}
