<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\User as UserModel;
use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\models\Language as LanguageModel;

class User
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * User model instance
     * @var \gplcart\core\models\User $user
     */
    protected $user;

    /**
     * Constructor
     * @param UserRoleModel $role
     * @param LanguageModel $language
     * @param UserModel $user
     */
    public function __construct(UserRoleModel $role, LanguageModel $language,
            UserModel $user)
    {
        $this->user = $user;
        $this->role = $role;
        $this->language = $language;
    }

    /**
     * Validates the user ID condition
     * @param array $values
     * @return boolean|string
     */
    public function id(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($user_id) {
            if ($user_id == 0) {
                return true; // 0 also valid if we check a user is logged in
            }
            $user = $this->user->get($user_id);
            return isset($user['user_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('User'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the role ID condition
     * @param array $values
     * @return boolean|string
     */
    public function roleId(array $values)
    {
        $count = count($values);
        $ids = array_filter($values, 'ctype_digit');

        if ($count != count($ids)) {
            $vars = array('@field' => $this->language->text('Condition'));
            return $this->language->text('@field has invalid value', $vars);
        }

        $exists = array_filter($values, function ($role_id) {
            if ($role_id == 0) {
                return true;
            }
            $role = $this->role->get($role_id);
            return isset($role['role_id']);
        });

        if ($count != count($exists)) {
            $vars = array('@name' => $this->language->text('Role'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
