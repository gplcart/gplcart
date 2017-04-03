<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Filter as FilterModel,
    gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate HTML filter data
 */
class Filter extends ComponentValidator
{

    /**
     * Filter model instance
     * @var \gplcart\core\models\Filter $filter
     */
    protected $filter;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateFilter();
        $this->validateStatusComponent();
        $this->validateNameComponent();
        $this->validateDescriptionComponent();
        $this->validateRoleFilter();

        return $this->getResult();
    }

    /**
     * Validates a filter to be updated
     * @return boolean|null
     */
    protected function validateFilter()
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
     * @return boolean|null
     */
    protected function validateRoleFilter()
    {
        $value = $this->getSubmitted('role_id');

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        if (empty($value)) {
            $this->setSubmitted('role_id', 0);
            return true;
        }

        if (!is_numeric($value)) {
            $vars = array('@field' => $this->language->text('Role'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('role_id', $error);
            return false;
        }

        $role = $this->role->get($value);

        if (empty($role['status'])) {
            $vars = array('@name' => $this->language->text('Role'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('role_id', $error);
            return false;
        }

        return true;
    }

}
