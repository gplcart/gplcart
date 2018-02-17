<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\condition;

/**
 * Provides methods to check trigger scope conditions
 */
class Scope extends Base
{

    /**
     * Whether the product scope condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function product(array $condition, array $data)
    {
        $is_product = (int) isset($data['product_id']);
        $value = (int) filter_var(reset($condition['value']), FILTER_VALIDATE_BOOLEAN);
        return $this->compare($is_product, $value, $condition['operator']);
    }

    /**
     * Whether the cart scope condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function cart(array $condition, array $data)
    {
        $is_cart = (int) isset($data['cart_id']);
        $value = (int) filter_var(reset($condition['value']), FILTER_VALIDATE_BOOLEAN);
        return $this->compare($is_cart, $value, $condition['operator']);
    }

    /**
     * Whether the order scope condition is met
     * @param array $condition
     * @param array $data
     * @return boolean
     */
    public function order(array $condition, array $data)
    {
        $is_order = (int) isset($data['order_id']);
        $value = (int) filter_var(reset($condition['value']), FILTER_VALIDATE_BOOLEAN);
        return $this->compare($is_order, $value, $condition['operator']);
    }

}
