<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\UserRole as UserRoleModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate user roles
 */
class UserRole extends BaseValidator
{

    /**
     * Review model instance
     * @var \core\models\UserRole $role
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
     * Performs full user role data validation
     * @param array $submitted
     */
    public function userRole(array &$submitted)
    {
        $this->validateUserRole($submitted);
        $this->validatePermissionsUserRole($submitted);
        $this->validateStatus($submitted);
        $this->validateName($submitted);

        return $this->getResult();
    }

    /**
     * Validates a user role to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateUserRole(array &$submitted)
    {
        if (empty($submitted['update']) || !is_numeric($submitted['update'])) {
            return null;
        }

        $data = $this->role->get($submitted['update']);

        if (empty($data)) {
            $options = array('@name' => $this->language->text('Role'));
            $this->errors['role_id'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        $submitted['update'] = $data;
        return true;
    }

    /**
     * Validates permissions data
     * @param array $submitted
     * @return boolean
     */
    protected function validatePermissionsUserRole(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['permissions'])) {
            return null;
        }

        if (empty($submitted['permissions'])) {
            $submitted['permissions'] = array();
        }

        $permissions = $this->role->getPermissions();
        $difference = array_diff($submitted['permissions'], array_keys($permissions));

        if (!empty($difference)) {
            $options = array('@name' => implode(',', $difference));
            $this->errors['permissions'] = $this->language->text('Object @name does not exist', $options);
            return false;
        }

        return true;
    }

}
