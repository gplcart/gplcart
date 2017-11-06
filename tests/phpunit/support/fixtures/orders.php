<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'order_id' => 1,
        'store_id' => 1,
        'shipping_address' => 1,
        'payment_address' => 2,
        'created' => 1234567890,
        'modified' => 1234567891,
        'total' => 9999,
        'creator' => 1234567890,
        'transaction_id' => 1,
        'currency' => 'USD',
        'user_id' => 1,
        'payment' => 'test_payment',
        'shipping' => 'test_shipping',
        'tracking_number' => 12345,
        'status' => 1,
        'comment' => 'Order comment',
        'volume' => 1,
        'weight' => 1,
        'size_unit' => 'm',
        'weight_unit' => 'kg',
        'data' => serialize(array('test' => true))
    ),
    array(
        'order_id' => 2,
        'store_id' => 2,
        'shipping_address' => 3,
        'payment_address' => 4,
        'created' => 1234567890,
        'modified' => 1234567891,
        'total' => 1111,
        'creator' => 1234567890,
        'transaction_id' => 2,
        'currency' => 'USD',
        'user_id' => 'anonymous',
        'payment' => 'test_payment',
        'shipping' => 'test_shipping',
        'tracking_number' => 123456,
        'status' => 0,
        'comment' => 'Order comment',
        'volume' => 1,
        'weight' => 1,
        'size_unit' => 'm',
        'weight_unit' => 'kg',
        'data' => serialize(array('test' => true))
    )
);
