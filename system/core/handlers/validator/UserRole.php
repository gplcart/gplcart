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
 * Provides methods to validate user roles
 */
class UserRole extends BaseValidator
{

    /**
     * Review model instance
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
     * Performs full user role data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function userRole(array &$submitted, array $options)
    {
        $this->submitted = &$submitted;

        $this->validateUserRole($options);
        $this->validatePermissionsUserRole($options);
        $this->validateStatus($options);
        $this->validateName($options);

        return $this->getResult();
    }

    /**
     * Validates a user role to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateUserRole(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->role->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Role'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates permissions data
     * @param array $options
     * @return boolean|null
     */
    protected function validatePermissionsUserRole(array $options)
    {
        $value = $this->getSubmitted('permissions', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $value = array();
        }

        $permissions = $this->role->getPermissions();
        $difference = array_diff($value, array_keys($permissions));

        if (!empty($difference)) {
            $vars = array('@name' => implode(',', $difference));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('permissions', $error, $options);
            return false;
        }

        return true;
    }

}
