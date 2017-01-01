<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate various data related to categories
 */
class Category extends BaseValidator
{

    /**
     * Category model instance
     * @var \gplcart\core\models\Category $category
     */
    protected $category;

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
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
     * @return boolean|array
     */
    public function category(array &$submitted, array $options = array())
    {
        $this->submitted = &$submitted;

        $this->validateCategory($options);
        $this->validateWeight($options);
        $this->validateStatus($options);
        $this->validateTitle($options);
        $this->validateMetaTitle($options);
        $this->validateMetaDescription($options);
        $this->validateDescriptionCategory($options);
        $this->validateGroupCategory($options);
        $this->validateParentCategory($options);
        $this->validateTranslation($options);
        $this->validateUserId($options);
        $this->validateImages($options);
        $this->validateAliasCategory($options);

        return $this->getResult();
    }

    /**
     * Validates a category to be updated
     * @param array $options
     * @return boolean|null
     */
    protected function validateCategory(array $options)
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->category->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Category'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a category group ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateGroupCategory(array $options)
    {
        $category_group_id = $this->getSubmitted('category_group_id', $options);

        if ($this->isUpdating() && !isset($category_group_id)) {
            return null;
        }

        if (empty($category_group_id)) {
            $vars = array('@field' => $this->language->text('Category group'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('category_group_id', $error, $options);
            return false;
        }

        if (!is_numeric($category_group_id)) {
            $vars = array('@field' => $this->language->text('Category group'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('category_group_id', $error, $options);
            return false;
        }

        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $vars = array('@name' => $this->language->text('Category group'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('category_group_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates parent category group ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateParentCategory(array $options)
    {
        $parent_id = $this->getSubmitted('parent_id', $options);

        if (empty($parent_id)) {
            return null;
        }

        if (!is_numeric($parent_id)) {
            $vars = array('@field' => $this->language->text('Parent category'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('parent_id', $error, $options);
            return false;
        }

        $category = $this->getSubmitted('category');

        if (isset($category['category_id']) && $category['category_id'] == $parent_id) {
            $vars = array('@field' => $this->language->text('Parent category'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('parent_id', $error, $options);
            return false;
        }

        $parent_category = $this->category->get($parent_id);

        if (empty($parent_category['category_id'])) {
            $vars = array('@name' => $this->language->text('Parent category'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('parent_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates first and second description
     * @param array $options
     * @return boolean
     */
    protected function validateDescriptionCategory(array $options)
    {
        $description_1 = $this->getSubmitted('description_1', $options);
        $description_2 = $this->getSubmitted('description_2', $options);

        if (isset($description_1) && mb_strlen($description_1) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('First description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description_1', $error, $options);
        }

        if (isset($description_2) && mb_strlen($description_2) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Second description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description_2', $error, $options);
        }

        return empty($error);
    }

    /**
     * Validates/creates an alias
     * @param array $options
     * @return boolean|null
     */
    protected function validateAliasCategory(array $options)
    {
        if ($this->isError()) {
            return null;
        }

        $updating = $this->getUpdating();
        $alias = $this->getSubmitted('alias', $options);

        if (isset($alias)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $alias)) {
            return true; // Do not check own alias on update
        }

        if (empty($alias) && isset($updating['category_id'])) {
            $data = $this->getSubmitted();
            $alias = $this->category->createAlias($data);
            $this->setSubmitted('alias', $alias, $options);
            return true;
        }

        return $this->validateAlias($options);
    }

}
