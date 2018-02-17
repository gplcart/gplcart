<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Translation;
use gplcart\core\models\User as UserModel;
use gplcart\core\models\UserRole;

class User
{

    /**
     * Translation UI model instance
     * @var \gplcart\core\models\Translation $translation
     */
    protected $translation;

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
     * @param UserModel $user
     * @param UserRole $role
     * @param Translation $translation
     */
    public function __construct(UserModel $user, UserRole $role, Translation $translation)
    {
        $this->user = $user;
        $this->role = $role;
        $this->translation = $translation;
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
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $existing = array_filter($values, function ($user_id) {
            if ($user_id == 0) {
                return true; // 0 also valid if we check that user is logged in
            }
            $user = $this->user->get($user_id);
            return isset($user['user_id']);
        });

        if ($count != count($existing)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('User')));
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
            return $this->translation->text('@field has invalid value', array(
                '@field' => $this->translation->text('Condition')));
        }

        $exists = array_filter($values, function ($role_id) {

            if ($role_id == 0) {
                return true;
            }

            $role = $this->role->get($role_id);
            return isset($role['role_id']);
        });

        if ($count != count($exists)) {
            return $this->translation->text('@name is unavailable', array(
                '@name' => $this->translation->text('Role')));
        }

        return true;
    }

}
