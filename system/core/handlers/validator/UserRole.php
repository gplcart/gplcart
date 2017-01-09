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
    public function userRole(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateUserRole();
        $this->validatePermissionsUserRole();
        $this->validateRedirectUserRole();
        $this->validateStatus();
        $this->validateName();

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
     * @return boolean|null
     */
    protected function validatePermissionsUserRole()
    {
        $value = $this->getSubmitted('permissions');

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
            $this->setError('permissions', $error);
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
            $vars = array('@max' => 255, '@field' => $this->language->text('Redirect'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('redirect', $error);
            return false;
        }

        return true;
    }

}
