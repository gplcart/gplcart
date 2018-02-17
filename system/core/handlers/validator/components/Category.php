<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component;
use gplcart\core\models\Category as CategoryModel;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;

/**
 * Provides methods to validate various data related to categories
 */
class Category extends Component
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
    public function __construct(CategoryModel $category, CategoryGroupModel $category_group)
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
        $this->validateBool('status');
        $this->validateTitle();
        $this->validateMetaTitle();
        $this->validateMetaDescription();
        $this->validateDescriptionCategory();
        $this->validateGroupCategory();
        $this->validateParentCategory();
        $this->validateTranslation();
        $this->validateImages();
        $this->validateAlias();
        $this->validateUploadImages('category');

        $this->unsetSubmitted('update');

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
            $this->setErrorUnavailable('update', $this->translation->text('Category'));
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

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            return null;
        }

        $label = $this->translation->text('Category group');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category_group = $this->category_group->get($value);

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
        $value = $this->getSubmitted($field);

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Parent category');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $category = $this->getSubmitted('category');

        if (isset($category['category_id']) && $category['category_id'] == $value) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        $parent_category = $this->category->get($value);

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
            'description_1' => $this->translation->text('First description'),
            'description_2' => $this->translation->text('Second description')
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
