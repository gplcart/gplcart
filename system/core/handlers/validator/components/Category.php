<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Category as CategoryModel,
    gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate various data related to categories
 */
class Category extends ComponentValidator
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
        $this->validateWeightComponent();
        $this->validateStatusComponent();
        $this->validateTitleComponent();
        $this->validateMetaTitleComponent();
        $this->validateMetaDescriptionComponent();
        $this->validateDescriptionCategory();
        $this->validateGroupCategory();
        $this->validateParentCategory();
        $this->validateTranslationComponent();
        $this->validateUserIdComponent();
        $this->validateImagesComponent();
        $this->validateAliasComponent();
        $this->validateUploadImagesComponent('category');

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
            $this->setErrorUnavailable('update', $this->language->text('Category'));
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
        $field = 'category_group_id';
        $label = $this->language->text('Category group');
        $category_group_id = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($category_group_id)) {
            return null;
        }

        if (empty($category_group_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($category_group_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category_group = $this->category_group->get($category_group_id);

        if (empty($category_group)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'parent_id';
        $label = $this->language->text('Parent category');
        $parent_id = $this->getSubmitted($field);

        if (empty($parent_id)) {
            return null;
        }

        if (!is_numeric($parent_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category = $this->getSubmitted('category');

        if (isset($category['category_id']) && $category['category_id'] == $parent_id) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $parent_category = $this->category->get($parent_id);

        if (empty($parent_category['category_id'])) {
            $this->setErrorUnavailable($field, $label);
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
        $fields = array(
            'description_1' => $this->language->text('First description'),
            'description_2' => $this->language->text('Second description')
        );

        $errors = 0;
        foreach ($fields as $field => $label) {
            $value = $this->getSubmitted($field);
            if (isset($value) && mb_strlen($value) > 65535) {
                $errors++;
                $this->setErrorLengthRange($field, $label, 0, 65535);
            }
        }

        return empty($errors);
    }

}
