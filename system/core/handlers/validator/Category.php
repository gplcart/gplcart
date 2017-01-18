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
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCategory();
        $this->validateWeight();
        $this->validateStatus();
        $this->validateTitle();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateDescriptionCategory();
        $this->validateGroupCategory();
        $this->validateParentCategory();
        $this->validateTranslation();
        $this->validateUserId();
        $this->validateImages();
        $this->validateAliasCategory();

        return $this->getResult();
    }

    /**
     * Validates a category to be updated
     * @return boolean|null
     */
    protected function validateCategory()
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
     * @return boolean|null
     */
    protected function validateGroupCategory()
    {
        $category_group_id = $this->getSubmitted('category_group_id');

        if ($this->isUpdating() && !isset($category_group_id)) {
            return null;
        }

        if (empty($category_group_id)) {
            $vars = array('@field' => $this->language->text('Category group'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('category_group_id', $error);
            return false;
        }

        if (!is_numeric($category_group_id)) {
            $vars = array('@field' => $this->language->text('Category group'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('category_group_id', $error);
            return false;
        }

        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $vars = array('@name' => $this->language->text('Category group'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('category_group_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates parent category group ID
     * @return boolean|null
     */
    protected function validateParentCategory()
    {
        $parent_id = $this->getSubmitted('parent_id');

        if (empty($parent_id)) {
            return null;
        }

        if (!is_numeric($parent_id)) {
            $vars = array('@field' => $this->language->text('Parent category'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('parent_id', $error);
            return false;
        }

        $category = $this->getSubmitted('category');

        if (isset($category['category_id']) && $category['category_id'] == $parent_id) {
            $vars = array('@field' => $this->language->text('Parent category'));
            $error = $this->language->text('@field has invalid value', $vars);
            $this->setError('parent_id', $error);
            return false;
        }

        $parent_category = $this->category->get($parent_id);

        if (empty($parent_category['category_id'])) {
            $vars = array('@name' => $this->language->text('Parent category'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('parent_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates first and second description
     * @return boolean
     */
    protected function validateDescriptionCategory()
    {
        $description_1 = $this->getSubmitted('description_1');
        $description_2 = $this->getSubmitted('description_2');

        if (isset($description_1) && mb_strlen($description_1) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('First description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description_1', $error);
        }

        if (isset($description_2) && mb_strlen($description_2) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Second description'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('description_2', $error);
        }

        return empty($error);
    }

    /**
     * Validates/creates an alias
     * @return boolean|null
     */
    protected function validateAliasCategory()
    {
        if ($this->isError()) {
            return null;
        }

        $updating = $this->getUpdating();
        $alias = $this->getSubmitted('alias');

        if (isset($alias)//
                && isset($updating['alias'])//
                && ($updating['alias'] === $alias)) {
            return true; // Do not check own alias on update
        }

        if (empty($alias) && isset($updating['category_id'])) {
            $data = $this->getSubmitted();
            $alias = $this->category->createAlias($this->alias, $data, 'category');
            $this->setSubmitted('alias', $alias);
            return true;
        }

        return $this->validateAlias();
    }

}
