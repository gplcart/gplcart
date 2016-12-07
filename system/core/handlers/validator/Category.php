<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Category as CategoryModel;
use core\models\CategoryGroup as CategoryGroupModel;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various data related to categories
 */
class Category extends BaseValidator
{

    /**
     * Category model instance
     * @var \core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Constructor
     * @param CategoryModel $category
     * @param CategoryGroupModel $category_group
     */
    public function __construct(CategoryModel $category,
            CategoryGroupModel $category_group)
    {

        parent::__construct();

        $this->category = $category;
        $this->category_group = $category_group;
    }

    /**
     * Performs full category data validation
     * @param array $submitted
     * @param array $options
     */
    public function category(array &$submitted, array $options = array())
    {
        $this->validateCategory($submitted);
        $this->validateWeight($submitted);
        $this->validateStatus($submitted);
        $this->validateTitle($submitted);
        $this->validateMetaTitle($submitted);
        $this->validateMetaDescription($submitted);
        $this->validateDescriptionCategory($submitted);
        $this->validateGroupCategory($submitted);
        $this->validateParentCategory($submitted);
        $this->validateTranslation($submitted);
        $this->validateImages($submitted);
        $this->validateAliasCategory($submitted);

        return $this->getResult();
    }

    /**
     * Validates a category to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateCategory(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {
            $data = $this->category->get($submitted['update']);
            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Category')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a category group ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateGroupCategory(array $submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['category_group_id'])) {
            return null;
        }

        if (empty($submitted['category_group_id']) || !is_numeric($submitted['category_group_id'])) {
            $options = array('@field' => $this->language->text('Category group'));
            $this->errors['category_group_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $category_group = $this->category_group->get($submitted['category_group_id']);

        if (empty($category_group)) {
            $this->errors['category_group_id'] = $this->language->text('Category group does not exist');
            return false;
        }

        return true;
    }

    /**
     * Validates parent category group ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateParentCategory(array $submitted)
    {
        if (empty($submitted['parent_id'])) {
            return null;
        }

        if (!is_numeric($submitted['parent_id'])) {
            $options = array('@field' => $this->language->text('Parent category'));
            $this->errors['parent_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (isset($submitted['category']['category_id']) //
                && $submitted['category']['category_id'] == $submitted['parent_id']) {
            $this->errors['parent_id'] = $this->language->text('Invalid parent category ID');
            return false;
        }

        $category = $this->category->get($submitted['parent_id']);

        if (empty($category)) {
            $options = array('@id' => $submitted['parent_id']);
            $this->errors['parent_id'] = $this->language->text('Category ID @id does not exist', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates description #1 and #2
     * @param array $submitted
     */
    protected function validateDescriptionCategory(array &$submitted)
    {
        if (isset($submitted['description_1']) && mb_strlen($submitted['description_1']) > 65535) {
            $this->errors['description_1'] = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 65535,
                '@field' => $this->language->text('First description')
            ));
        }

        if (isset($submitted['description_2']) && mb_strlen($submitted['description_2']) > 65535) {
            $this->errors['description_2'] = $this->language->text('@field must not be longer than @max characters', array(
                '@max' => 65535,
                '@field' => $this->language->text('Second description')
            ));
        }
    }

    /**
     * Validates / creates an alias
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateAliasCategory(array &$submitted)
    {
        if (!empty($this->errors)) {
            return null;
        }

        if (isset($submitted['alias'])//
                && isset($submitted['update']['alias'])//
                && ($submitted['update']['alias'] === $submitted['alias'])) {
            return true; // Do not check own alias on update
        }

        if (empty($submitted['alias']) && isset($submitted['update']['category_id'])) {
            $submitted['alias'] = $this->category->createAlias($submitted);
            return true;
        }

        return $this->validateAlias($submitted);
    }

}
