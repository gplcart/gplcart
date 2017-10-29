<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\handlers\condition\Base as BaseHandler;

/**
 * Provides methods to check product conditions
 */
class Product extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Whether the product ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function id(array $condition, array $data)
    {
        if (empty($data['product_id'])) {
            return false;
        }

        return $this->compare($data['product_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the product category ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $condition, array $data)
    {
        if (empty($data['product_id']) || empty($data['category_id'])) {
            return false;
        }

        return $this->compare($data['category_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Whether the product brand condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $condition, array $data)
    {
        if (empty(empty($data['product_id']) || $data['brand_category_id'])) {
            return false;
        }

        return $this->compare($data['brand_category_id'], $condition['value'], $condition['operator']);
    }

}
