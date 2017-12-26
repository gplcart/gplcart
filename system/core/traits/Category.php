<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\traits;

/**
 * Category methods
 */
trait Category
{

    /**
     * Returns a list of categories per store to use directly in <select>
     * @param \gplcart\core\models\Category $category_model
     * @param \gplcart\core\models\CategoryGroup $category_group_model
     * @param array $options
     * @return array
     */
    public function getCategoryOptionsByStore($category_model, $category_group_model, array $options)
    {
        $groups = (array) $category_group_model->getList($options);

        $list = array();
        foreach ($groups as $group) {
            $op = array('category_group_id' => $group['category_group_id']);
            $list[$group['title']] = $this->getCategoryOptions($category_model, $op);
        }

        return $list;
    }

    /**
     * Returns a list of categories to use directly in <select>
     * @param \gplcart\core\models\Category $category_model
     * @param array $options
     * @return array
     */
    public function getCategoryOptions($category_model, array $options)
    {
        $options += array(
            'status' => 1,
            'parent_id' => 0,
            'hierarchy' => true
        );

        $categories = $category_model->getTree($options);

        if (empty($categories)) {
            return array();
        }

        $list = array();
        foreach ($categories as $category) {

            $title = $category['title'];

            if (!empty($options['hierarchy'])) {
                $title = str_repeat('— ', $category['depth']) . $title;
            }

            $list[$category['category_id']] = $title;
        }

        return $list;
    }

}
