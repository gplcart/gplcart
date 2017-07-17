<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\CategoryGroup as CategoryGroupModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate category groups
 */
class CategoryGroup extends ComponentValidator
{

    /**
     * Category group model instance
     * @var \gplcart\core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
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
     * @return boolean|array
     */
    public function categoryGroup(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCategoryGroup();
        $this->validateTitleComponent();
        $this->validateTranslationComponent();
        $this->validateStoreIdComponent();
        $this->validateTypeCategoryGroup();

        return $this->getResult();
    }

    /**
     * Validates a category group to be updated
     * @return boolean|null
     */
    protected function validateCategoryGroup()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->category_group->get($id);

        if (empty($data)) {
            $this->setErrorUnavailable('update', $this->language->text('Category group'));
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates category group type
     * @return boolean|null
     */
    protected function validateTypeCategoryGroup()
    {
        if ($this->isError('store_id')) {
            return null;
        }

        $field = 'type';
        $label = $this->language->text('Type');

        $type = $this->getSubmitted($field);
        $store_id = $this->getSubmitted('store_id');

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $types = $this->category_group->getTypes();

        if (!isset($types[$type])) {
            $this->setErrorUnavailable($field, $label);
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
        $this->setError('type', $error);
        return false;
    }

}
