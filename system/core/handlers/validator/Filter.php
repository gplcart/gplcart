<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Filter as FilterModel;
use core\models\UserRole as UserRoleModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate HTML filter data
 */
class Filter extends BaseValidator
{

    /**
     * Filter model instance
     * @var \core\models\Filter $filter
     */
    protected $filter;

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Constructor
     * @param FilterModel $filter
     * @param UserRoleModel $role
     */
    public function __construct(FilterModel $filter, UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
        $this->filter = $filter;
    }

    /**
     * Performs full filter data validation
     * @param array $submitted
     * @param array $options
     * @return boolean|array
     */
    public function filter(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateFilter($options);
        $this->validateStatus($options);
        $this->validateName($options);
        $this->validateDescription($options);
        $this->validateRoleFilter($options);

        return $this->getResult();
    }

    /**
     * Validates a filter to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateFilter(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->filter->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Filter'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a user role
     * @param array $options
     * @return boolean|null
     */
    protected function validateRoleFilter(array $options)
    {
        $value = $this->getSubmitted('role_id', $options);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setSubmitted('role_id', 0, $options);
            return true;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Role'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('role_id', $error, $options);
            return false;
        }

        $role = $this->role->get($value);

        if (empty($role['status'])) {
            $vars = array('@name' => $this->language->text('Role'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('role_id', $error, $options);
            return false;
        }

        return true;
    }

}
