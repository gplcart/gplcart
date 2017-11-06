<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'cart_id' => 1,
        'store_id' => 1,
        'created' => 1234567890,
        'modified' => 1234567891,
        'product_id' => 1,
        'quantity' => 1,
        'order_id' => 1,
        'user_id' => 'anonymous',
        'sku' => 'TEST',
        'data' => serialize(array('test' => true))
    ),
    array(
        'cart_id' => 2,
        'store_id' => 1,
        'created' => 1234567890,
        'modified' => 1234567891,
        'product_id' => 2,
        'quantity' => 1,
        'order_id' => 2,
        'user_id' => 1,
        'sku' => 'TEST2',
        'data' => serialize(array('test' => true))
    ),
);
