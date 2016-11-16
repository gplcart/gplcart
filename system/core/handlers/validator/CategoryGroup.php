<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\CategoryGroup as ModelsCategoryGroup;
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
     * @param ModelsCategoryGroup $category_group
     */
    public function __construct(ModelsCategoryGroup $category_group)
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
        $this->validateCategoryGroup($submitted);
        $this->validateTitle($submitted);
        $this->validateTranslation($submitted);
        $this->validateStoreId($submitted);
        $this->validateTypeCategoryGroup($submitted);

        return $this->getResult();
    }

    /**
     * Validates a category group to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCategoryGroup(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->category_group->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Category group')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates category group type
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTypeCategoryGroup(array &$submitted)
    {
        if (isset($this->errors['store_id'])) {
            return null;
        }

        if (!empty($submitted['update']) && !isset($submitted['type'])) {
            return null;
        }

        if (empty($submitted['type'])) {
            $this->errors['type'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Type')
            ));

            return false;
        }

        $arguments = array(
            'type' => $submitted['type'],
            'store_id' => $submitted['store_id']);

        $list = $this->category_group->getList($arguments);

        // Remove own ID when updating the group
        if (isset($submitted['update']['category_group_id'])) {
            unset($list[$submitted['update']['category_group_id']]);
        }

        if (empty($list)) {
            return true;
        }

        $this->errors['type'] = $this->language->text('Category group of this type already exists for this store');
        return false;
    }

}
