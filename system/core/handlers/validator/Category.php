<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Language as ModelsLanguage;
use core\models\CategoryGroup as ModelsCategoryGroup;

/**
 * Provides methods to validate various data related to categories
 */
class Category
{

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Category group model instance
     * @var \core\models\CategoryGroup $category_group
     */
    protected $category_group;

    /**
     * Constructor
     * @param ModelsCategoryGroup $category_group
     * @param ModelsLanguage $language
     */
    public function __construct(ModelsCategoryGroup $category_group,
            ModelsLanguage $language)
    {
        $this->language = $language;
        $this->category_group = $category_group;
    }

    /**
     * Validates category group type
     * @param string $value
     * @param array $options
     * @return boolean
     */
    public function groupTypeUnique($value, array $options = array())
    {
        if (empty($value)) {
            return true;
        }

        $arguments = array(
            'type' => $value,
            'store_id' => $options['store_id']);

        $category_groups = $this->category_group->getList($arguments);

        if (isset($options['category_group_id'])) {
            unset($category_groups[$options['category_group_id']]);
        }

        if (empty($category_groups)) {
            return true;
        }

        return $this->language->text('Category group with this type already exists for this store');
    }

}
