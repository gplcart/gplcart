<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\User as UserModel;
use gplcart\core\models\Condition as ConditionModel;

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
    public function userId(array $condition)
    {
        $user_id = (int) $this->user->getSession('user_id');

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareNumeric($user_id, $value, $condition['operator']);
    }

    /**
     * Returns true if a user role condition is met
     * @param array $condition
     * @return boolean
     */
    public function userRole(array $condition)
    {
        $role_id = (string) $this->user->getSession('role_id');

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        return $this->condition->compareNumeric($role_id, $value, $condition['operator']);
    }

}
