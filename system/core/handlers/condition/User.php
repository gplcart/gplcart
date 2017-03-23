<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\User as UserModel,
    gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check user conditions
 */
class User
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param ConditionModel $condition
     * @param UserModel $user
     */
    public function __construct(ConditionModel $condition, UserModel $user)
    {
        $this->user = $user;
        $this->condition = $condition;
    }

    /**
     * Returns true if a user ID condition is met
     * @param array $condition
     * @return boolean
     */
    public function id(array $condition)
    {
        static $user_id = null;

        if ($user_id === null) {
            $user_id = (int) $this->user->getSession('user_id');
        }

        return $this->condition->compare($user_id, $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a user role condition is met
     * @param array $condition
     * @return boolean
     */
    public function roleId(array $condition)
    {
        static $role_id = null;

        if ($role_id === null) {
            $role_id = (int) $this->user->getSession('role_id');
        }

        return $this->condition->compare($role_id, $condition['value'], $condition['operator']);
    }

}
