<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\CategoryGroup as CategoryGroupModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate category groups
 */
class CategoryGroup extends BaseValidator
{

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Constructor
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryGroupModel $category_group)
    {
        parent::__construct();
        $this->category_group = $category_group;
    }

    /**
     * Performs full category group data validation
     * @param array $submitted
     * @param array $options
     */
    public function categoryGroup(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateCategoryGroup($options);
        $this->validateTitle($options);
        $this->validateTranslation($options);
        $this->validateStoreId($options);
        $this->validateTypeCategoryGroup($options);

        return $this->getResult();
    }

    /**
     * Validates a category group to be updated
     * @param array $options
     * @return boolean
     */
    protected function validateCategoryGroup(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->category_group->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Category group'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates category group type
     * @param array $options
     * @return boolean|null
     */
    protected function validateTypeCategoryGroup(array $options)
    {
        if ($this->isError('store_id', $options)) {
            return null;
        }

        $type = $this->getSubmitted('type', $options);
        $store_id = $this->getSubmitted('store_id', $options);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $types = $this->category_group->getTypes();

        if (!isset($types[$type])) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error, $options);
            return false;
        }

        $updating = $this->getUpdating();
        $arguments = array('type' => $type, 'store_id' => $store_id);
        $list = $this->category_group->getList($arguments);

        // Remove own ID when updating the group
        if (isset($updating['category_group_id'])) {
            unset($list[$updating['category_group_id']]);
        }

        if (empty($list)) {
            return true;
        }

        $error = $this->language->text('Category group of this type already exists for this store');
        $this->setError('type', $error, $options);
        return false;
    }

}
