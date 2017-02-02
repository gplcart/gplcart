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
    public function productId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $value = (array) $condition['value'];

        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ($data['cart']['items'] as $item) {
            if ($this->condition->compareNumeric((int) $item['product_id'], $value, $condition['operator'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Returns true if a product category ID condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function categoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        if (empty($product_ids)) {
            return false;
        }

        $args = array('product_id' => $product_ids, 'status' => 1);
        $products = $this->product->getList($args);

        if (empty($products)) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ((array) $products as $product) {
            $matched = $this->condition->compareNumeric((int) $product['category_id'], $value, $condition['operator']);
            if ($matched) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if a product brand condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function brandCategoryId(array $condition, array $data)
    {
        if (empty($data['cart']['items'])) {
            return false;
        }

        $product_ids = array();
        foreach ($data['cart']['items'] as $item) {
            $product_ids[] = $item['product_id'];
        }

        if (empty($product_ids)) {
            return false;
        }

        $products = $this->product->getList(array('product_id' => $product_ids, 'status' => 1));

        if (empty($products)) {
            return false;
        }

        $value = (array) $condition['value'];
        if (!in_array($condition['operator'], array('=', '!='))) {
            $value = (int) reset($value);
        }

        foreach ((array) $products as $product) {
            $matched = $this->condition->compareNumeric((int) $product['brand_category_id'], $value, $condition['operator']);
            if ($matched) {
                return true;
            }
        }

        return false;
    }

}
