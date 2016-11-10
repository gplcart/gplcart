<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

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
        $this->validatePasswordUser($submitted);
        $this->validateStoreId($submitted);
        $this->validateRoleUser($submitted);

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
     * Validates a user password
     * @param array $submitted
     * @return boolean
     */
    protected function validatePasswordUser(array $submitted)
    {
        if (!empty($submitted['update']) && empty($submitted['password'])) {
            return null;
        }

        if (empty($submitted['password'])) {
            $options = array('@field' => $this->language->text('Password'));
            $this->errors['password'] = $this->language->text('@field is required', $options);
            return false;
        }

        $limit = $this->user->getPasswordLength();
        $length = mb_strlen($submitted['password']);

        if ($length < $limit['min'] || $length > $limit['max']) {
            $options = array('@min' => $limit['min'], '@max' => $limit['max'], '@field' => $this->language->text('Password'));
            $this->errors['password'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
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

        $role = $this->role->get($submitted['role_id']);

        if (empty($role)) {
            $options = array('@name' => $this->language->text('Role'));
            $this->errors['role_id'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        return true;
    }

}
