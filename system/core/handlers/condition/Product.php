<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

use gplcart\core\models\Product as ProductModel;
use gplcart\core\models\Condition as ConditionModel;

/**
 * Provides methods to check product conditions
 */
class Product
{

    /**
     * Condition model instance
     * @var \gplcart\core\models\Condition $condition
     */
    protected $condition;

    /**
     * Product model instance
     * @var \gplcart\core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param ConditionModel $condition
     * @param ProductModel $product
     */
    public function __construct(ConditionModel $condition, ProductModel $product)
    {
        $this->product = $product;
        $this->condition = $condition;
    }

    /**
     * Returns true if a product ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function id(array $condition, array $data)
    {
        if (empty($data['product_id'])) {
            return false;
        }

        return $this->condition->compare($data['product_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a product category ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $condition, array $data)
    {
        if (empty($data['product_id']) || empty($data['category_id'])) {
            return false;
        }

        return $this->condition->compare($data['category_id'], $condition['value'], $condition['operator']);
    }

    /**
     * Returns true if a product brand condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $condition, array $data)
    {
        if (empty(empty($data['product_id']) || $data['brand_category_id'])) {
            return false;
        }

        return $this->condition->compare($data['brand_category_id'], $condition['value'], $condition['operator']);
    }

}
