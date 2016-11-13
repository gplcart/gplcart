<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\classes\Tool;
use core\models\UserRole as ModelsUserRole;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various user related data
 */
class User extends BaseValidator
{

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
     * @param ModelsUserRole $role
     */
    public function __construct(ModelsUserRole $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Performs full validation of submitted user data
     * @param array $submitted
     * @return array|boolean
     */
    public function user(array &$submitted)
    {
        $this->validateUser($submitted);
        $this->validateStatus($submitted);
        $this->validateNameUser($submitted);
        $this->validateEmailUser($submitted);
        $this->validateEmailUniqueUser($submitted);
        $this->validatePasswordUser($submitted);
        $this->validatePasswordLengthUser($submitted);
        $this->validatePasswordOldUser($submitted);
        $this->validateStoreId($submitted);
        $this->validateRoleUser($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Performs full login data validation
     * @param array $submitted
     * @return array|boolean
     */
    public function login(array &$submitted)
    {
        $this->validateEmailUser($submitted);
        $this->validatePasswordUser($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Performs password reset validation
     * @param array $submitted
     */
    public function resetPassword(array &$submitted)
    {
        if (isset($submitted['password'])) {
            $this->validatePasswordLengthUser($submitted);
        } else if (isset($submitted['email'])) {
            $this->validateEmailUser($submitted);
            $this->validateEmailExistsUser($submitted);
        }

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates a user to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateUser(array &$submitted)
    {
        if (empty($submitted['update']) || !is_numeric($submitted['update'])) {
            return null;
        }

        $data = $this->user->get($submitted['update']);

        if (empty($data)) {
            $options = array('@name' => $this->language->text('User'));
            $this->errors['update'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        $submitted['update'] = $data;
        return true;
    }

    /**
     * Validates a user name
     * @param array $submitted
     * @return boolean
     */
    protected function validateNameUser(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['name'])) {
            return null;
        }

        if (empty($submitted['name'])) {
            $this->errors['name'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Name')
            ));
            return false;
        }

        if (isset($submitted['update']['name'])//
                && ($submitted['update']['name'] === $submitted['name'])) {
            return true;
        }

        $user = $this->user->getByName($submitted['name']);

        if (empty($user)) {
            return true;
        }

        $this->errors['name'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('Name')));
        return false;
    }

    /**
     * Validates a user E-mail
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailUser(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['email'])) {
            return null;
        }

        if (empty($submitted['email'])) {
            $options = array('@field' => $this->language->text('E-mail'));
            $this->errors['email'] = $this->language->text('@field is required', $options);
            return false;
        }

        if (!filter_var($submitted['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors['email'] = $this->language->text('Invalid E-mail');
            return false;
        }

        return true;
    }

    /**
     * Validates uniqueness of submitted E-mail
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailUniqueUser(array &$submitted)
    {
        if ($this->isError('email') || !isset($submitted['email'])) {
            return null;
        }

        if (isset($submitted['update']['email'])//
                && ($submitted['update']['email'] === $submitted['email'])) {
            return true;
        }

        $user = $this->user->getByEmail($submitted['email']);

        if (empty($user)) {
            return true;
        }

        $this->errors['email'] = $this->language->text('@object already exists', array(
            '@object' => $this->language->text('E-mail')));
        return false;
    }

    /**
     * Validates an email and checks the responding user enabled
     * @param array $submitted
     * @return boolean
     */
    protected function validateEmailExistsUser(array &$submitted)
    {
        if ($this->isError('email') || !isset($submitted['email'])) {
            return null;
        }

        $user = $this->user->getByEmail($submitted['email']);

        if (!empty($user['status'])) {
            $submitted['user'] = $user;
            return true;
        }

        $vars = array('@name' => $this->language->text('E-mail'));
        $error = $this->language->text('Object @name does not exist', $vars);
        $this->setError('email', $error);
        return false;
    }

    /**
     * Validates a user password
     * @param array $submitted
     * @return boolean
     */
    protected function validatePasswordUser(array $submitted)
    {
        if (!empty($submitted['update'])//
                && (!isset($submitted['password']) || $submitted['password'] === '')) {
            return null;
        }

        if (empty($submitted['password'])) {
            $options = array('@field' => $this->language->text('Password'));
            $this->errors['password'] = $this->language->text('@field is required', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates password length
     * @param array $submitted
     * @return boolean
     */
    protected function validatePasswordLengthUser(array $submitted)
    {
        if ($this->isError('password') || !isset($submitted['password'])) {
            return null;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($submitted['password']);

        if ($length < $limit['min'] || $length > $limit['max']) {
            $vars = array('@min' => $limit['min'], '@max' => $limit['max'], '@field' => $this->language->text('Password'));
            $this->errors['password'] = $this->language->text('@field must be @min - @max characters long', $vars);
            return false;
        }

        return true;
    }

    /**
     * Validates an old user password
     * @param array $submitted
     * @return boolean
     */
    protected function validatePasswordOldUser(array $submitted)
    {
        if (empty($submitted['update'])) {
            return null;
        }

        if (!isset($submitted['password']) || $submitted['password'] === '') {
            return null;
        }

        if (!isset($submitted['password_old']) || $submitted['password_old'] === '') {
            $options = array('@field' => $this->language->text('Old password'));
            $this->errors['password_old'] = $this->language->text('@field is required', $options);
            return false;
        }

        $hash = Tool::hash($submitted['password_old'], $submitted['update']['hash'], false);

        if (Tool::hashEquals($submitted['update']['hash'], $hash)) {
            return true;
        }

        $this->errors['password_old'] = $this->language->text('Old and new password not matching');
        return false;
    }

    /**
     * Validates a user role
     * @param array $submitted
     * @return boolean
     */
    protected function validateRoleUser(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['role_id'])) {
            return null;
        }

        if (isset($submitted['role_id']) && !is_numeric($submitted['role_id'])) {
            $options = array('@field' => $this->language->text('Role'));
            $this->errors['role_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (empty($submitted['role_id'])) {
            return true;
        }

        $role = $this->role->get($submitted['role_id']);

        if (empty($role)) {
            $options = array('@name' => $this->language->text('Role'));
            $this->errors['role_id'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        return true;
    }

}
