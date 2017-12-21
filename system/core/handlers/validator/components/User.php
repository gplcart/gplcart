<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate various user related data
 */
class User extends ComponentValidator
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * @param UserRoleModel $role
     */
    public function __construct(UserRoleModel $role)
    {
        parent::__construct();
        $this->role = $role;
    }

    /**
     * Performs full validation of submitted user data
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function user(array &$submitted, array $options)
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateUser();
        $this->validateStatus();
        $this->validateName();
        $this->validateEmail();
        $this->validateEmailUniqueUser();
        $this->validatePasswordUser();
        $this->validatePasswordLengthUser();
        $this->validatePasswordOldUser();
        $this->validateStoreId();
        $this->validateRoleUser();

        return $this->getResult();
    }

    /**
     * Performs full login data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function login(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateEmail();
        $this->validatePasswordUser();

        return $this->getResult();
    }

    /**
     * Performs password reset validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function resetPassword(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $email = $this->getSubmitted('email');
        $password = $this->getSubmitted('password');

        if (isset($password)) {
            $this->validateStatusUser();
            $this->validatePasswordLengthUser();
        } else if (isset($email)) {
            $this->validateEmail();
            $this->validateEmailExistsUser();
        }

        return $this->getResult();
    }

    /**
     * Validates user status
     * @return boolean
     */
    protected function validateStatusUser()
    {
        $user = $this->getSubmitted('user');

        if (is_numeric($user)) {
            $user = $this->user->get($user);
        }

        if (empty($user['status']) || empty($user['user_id'])) {
            $this->setErrorUnavailable('user', $this->translation->text('User'));
            return false;
        }

        $this->setSubmitted('user', $user);
        return true;
    }

    /**
     * Validates a user to be updated
     * @return boolean
     */
    protected function validateUser()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->user->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('User'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates uniqueness of submitted E-mail
     * @return boolean|null
     */
    protected function validateEmailUniqueUser()
    {
        $value = $this->getSubmitted('email');

        if ($this->isError('email') || !isset($value)) {
            return null;
        }

        $updating = $this->getUpdating();

        if (isset($updating['email']) && ($updating['email'] === $value)) {
            return true;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user)) {
            return true;
        }

        $this->setErrorExists('email', $this->translation->text('E-mail'));
        return false;
    }

    /**
     * Validates an email and checks the responding user enabled
     * @return boolean|null
     */
    protected function validateEmailExistsUser()
    {
        $value = $this->getSubmitted('email');

        if ($this->isError('email') || !isset($value)) {
            return null;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user['status'])) {
            $this->setErrorUnavailable('email', $this->translation->text('E-mail'));
            return false;
        }

        $this->setSubmitted('user', $user);
        return true;
    }

    /**
     * Validates a user password
     * @return boolean|null
     */
    protected function validatePasswordUser()
    {
        $value = $this->getSubmitted('password');

        if ($this->isUpdating() && (!isset($value) || $value === '')) {
            return null;
        }

        if (empty($value)) {
            $this->setErrorRequired('password', $this->translation->text('Password'));
            return false;
        }
        return true;
    }

    /**
     * Validates password length
     * @return boolean|null
     */
    protected function validatePasswordLengthUser()
    {
        $value = $this->getSubmitted('password');

        if ($this->isError('password')) {
            return null;
        }

        if ($this->isUpdating() && (!isset($value) || $value === '')) {
            return null;
        }

        $length = mb_strlen($value);
        list($min, $max) = $this->user->getPasswordLength();

        if ($length < $min || $length > $max) {
            $this->setErrorLengthRange('password', $this->translation->text('Password'), $min, $max);
            return false;
        }
        return true;
    }

    /**
     * Validates an old user password
     * @return boolean|null
     */
    protected function validatePasswordOldUser()
    {
        if (!$this->isUpdating() || !empty($this->options['admin'])) {
            return null;
        }

        $password = $this->getSubmitted('password');

        if (!isset($password) || $password === '') {
            return null;
        }

        $old_password = $this->getSubmitted('password_old');

        if (!isset($old_password) || $old_password === '') {
            $this->setErrorRequired('password_old', $this->translation->text('Old password'));
            return false;
        }

        $updating = $this->getUpdating();
        $hash = gplcart_string_hash($old_password, $updating['hash'], 0);

        if (!gplcart_string_equals($updating['hash'], $hash)) {
            $error = $this->translation->text('Old and new password not matching');
            $this->setError('password_old', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a user role
     * @return boolean|null
     */
    protected function validateRoleUser()
    {
        $field = 'role_id';
        $label = $this->translation->text('Role');
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $role = $this->role->get($value);

        if (empty($role)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        return true;
    }

}
