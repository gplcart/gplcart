<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\CategoryGroup as CategoryGroupModel;

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
     * @param array $options
     * @return boolean|array
     */
    public function categoryGroup(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateCategoryGroup();
        $this->validateTitle();
        $this->validateTranslation();
        $this->validateStoreId();
        $this->validateTypeCategoryGroup();

        $this->unsetSubmitted('update');

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
            $this->setErrorUnavailable('update', $this->translation->text('Category group'));
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
        $field = 'type';

        if ($this->isExcluded($field) || $this->isError('store_id')) {
            return null;
        }

        $type = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        $store_id = $this->getSubmitted('store_id');

        if (!isset($store_id)) {
            $this->setErrorRequired($field, $this->translation->text('Store'));
            return false;
        }

        $label = $this->translation->text('Type');

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
        $list = $this->category_group->getList(array('type' => $type, 'store_id' => $store_id));

        if (isset($updating['category_group_id'])) {
            unset($list[$updating['category_group_id']]);
        }

        if (empty($list)) {
            return true;
        }

        $error = $this->translation->text('Category group of this type already exists for this store');
        $this->setError('type', $error);
        return false;
    }

}
