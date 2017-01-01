<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various user related data
 */
class User extends BaseValidator
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
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
        $this->submitted = &$submitted;

        $this->validateUser($options);
        $this->validateStatus($options);
        $this->validateNameUser($options);
        $this->validateEmail($options);
        $this->validateEmailUniqueUser($options);
        $this->validatePasswordUser($options);
        $this->validatePasswordLengthUser($options);
        $this->validatePasswordOldUser($options);
        $this->validateStoreId($options);
        $this->validateRoleUser($options);

        return $this->getResult();
    }

    /**
     * Performs full login data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function login(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateEmail($options);
        $this->validatePasswordUser($options);

        return $this->getResult();
    }

    /**
     * Performs password reset validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function resetPassword(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $email = $this->getSubmitted('email', $options);
        $password = $this->getSubmitted('password', $options);

        if (isset($password)) {
            $this->validatePasswordLengthUser($submitted);
        } else if (isset($email)) {
            $this->validateEmail($submitted);
            $this->validateEmailExistsUser($submitted);
        }

        return $this->getResult();
    }

    /**
     * Validates a user to be updated
     * @param array $options
     * @return boolean
     */
    protected function validateUser(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->user->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('User'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a user name
     * @param array $options
     * @return boolean|null
     */
    protected function validateNameUser(array $options)
    {
        $value = $this->getSubmitted('name', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Name'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('name', $error, $options);
            return false;
        }

        $updating = $this->getUpdating();

        if (isset($updating['name']) && ($updating['name'] === $value)) {
            return true;
        }

        $user = $this->user->getByName($value);

        if (empty($user['user_id'])) {
            return true;
        }

        $vars = array('@object' => $this->language->text('Name'));
        $error = $this->language->text('@object already exists', $vars);
        $this->setError('name', $error, $options);
        return false;
    }

    /**
     * Validates uniqueness of submitted E-mail
     * @param array $options
     * @return boolean|null
     */
    protected function validateEmailUniqueUser(array $options)
    {
        $value = $this->getSubmitted('email', $options);

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

        $vars = array('@object' => $this->language->text('E-mail'));
        $error = $this->language->text('@object already exists', $vars);
        $this->setError('email', $error, $options);
        return false;
    }

    /**
     * Validates an email and checks the responding user enabled
     * @param array $options
     * @return boolean|null
     */
    protected function validateEmailExistsUser(array $options)
    {
        $value = $this->getSubmitted('email', $options);

        if ($this->isError('email') || !isset($value)) {
            return null;
        }

        $user = $this->user->getByEmail($value);

        if (empty($user['status'])) {
            $vars = array('@name' => $this->language->text('E-mail'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('email', $error, $options);
            return false;
        }

        $this->setSubmitted('user', $user);
        return true;
    }

    /**
     * Validates a user password
     * @param array $options
     * @return boolean|null
     */
    protected function validatePasswordUser(array $options)
    {
        $value = $this->getSubmitted('password', $options);

        if ($this->isUpdating() && (!isset($value) || $value === '')) {
            return null;
        }

        if (empty($value)) {
            $vars = array('@field' => $this->language->text('Password'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('password', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates password length
     * @param array $options
     * @return boolean|null
     */
    protected function validatePasswordLengthUser(array $options)
    {
        $value = $this->getSubmitted('password', $options);

        if ($this->isError('password', $options) || !isset($value)) {
            return null;
        }

        $length = mb_strlen($value);
        $limit = $this->user->getPasswordLength();

        if ($length < $limit['min'] || $length > $limit['max']) {
            $vars = array('@min' => $limit['min'], '@max' => $limit['max'], '@field' => $this->language->text('Password'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('password', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an old user password
     * @param array $options
     * @return boolean|null
     */
    protected function validatePasswordOldUser(array $options)
    {
        if (!$this->isUpdating()) {
            return null;
        }

        $password = $this->getSubmitted('password', $options);

        if (!isset($password) || $password === '') {
            return null;
        }

        $old_password = $this->getSubmitted('password_old', $options);

        if (!isset($old_password) || $old_password === '') {
            $vars = array('@field' => $this->language->text('Old password'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('password_old', $error, $options);
            return false;
        }

        $updating = $this->getUpdating();
        $hash = gplcart_string_hash($old_password, $updating['hash'], 0);

        if (!gplcart_string_equals($updating['hash'], $hash)) {
            $error = $this->language->text('Old and new password not matching');
            $this->setError('password_old', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a user role
     * @param array $options
     * @return boolean|null
     */
    protected function validateRoleUser(array $options)
    {
        $value = $this->getSubmitted('role_id', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (isset($value) && !is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Role'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('role_id', $error, $options);
            return false;
        }

        if (empty($value)) {
            return true;
        }

        $role = $this->role->get($value);

        if (empty($role)) {
            $vars = array('@name' => $this->language->text('Role'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('role_id', $error, $options);
            return false;
        }

        return true;
    }

}
