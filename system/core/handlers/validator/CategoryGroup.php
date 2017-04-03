<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

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
     * @return boolean|null
     */
    protected function validateTypeCategoryGroup()
    {
        if ($this->isError('store_id')) {
            return null;
        }

        $type = $this->getSubmitted('type');
        $store_id = $this->getSubmitted('store_id');

        if ($this->isUpdating() && !isset($type)) {
            return null;
        }

        if (empty($type)) {
            $vars = array('@field' => $this->language->text('Type'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('type', $error);
            return false;
        }

        $types = $this->category_group->getTypes();

        if (!isset($types[$type])) {
            $vars = array('@name' => $this->language->text('Type'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('type', $error);
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
