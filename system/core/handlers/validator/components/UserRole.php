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
 * Provides methods to validate user roles
 */
class UserRole extends ComponentValidator
{

    /**
     * Review model instance
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
     * Performs full user role data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function userRole(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateUserRole();
        $this->validatePermissionsUserRole();
        $this->validateRedirectUserRole();
        $this->validateStatus();
        $this->validateName();

        $this->unsetSubmitted('update');

        return $this->getResult();
    }

    /**
     * Validates a user role to be updated
     * @return boolean|null
     */
    protected function validateUserRole()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->role->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->translation->text('Role'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates permissions data
     * @return boolean|null
     */
    protected function validatePermissionsUserRole()
    {
        $field = 'permissions';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value)) {
            $this->setSubmitted($field, array());
            return null;
        }

        if (!is_array($value)) {
            $this->setErrorInvalid($field, $this->translation->text('Permissions'));
            return false;
        }

        $permissions = $this->role->getPermissions();
        $invalid = array_diff($value, array_keys($permissions));

        if (!empty($invalid)) {
            $this->setErrorUnavailable($field, implode(',', $invalid));
            return false;
        }

        return true;
    }

    /**
     * Validates a redirect path
     * @return boolean
     */
    protected function validateRedirectUserRole()
    {
        $value = $this->getSubmitted('redirect');

        if (isset($value) && mb_strlen($value) > 255) {
            $this->setErrorLengthRange('redirect', $this->translation->text('Redirect'), 0, 255);
            return false;
        }

        return true;
    }

}
