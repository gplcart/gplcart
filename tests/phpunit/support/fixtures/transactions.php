<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'transaction_id' => 1,
        'order_id' => 1,
        'total' => 9999,
        'currency' => 'USD',
        'created' => 1234567890,
        'payment_method' => 'test_payment',
        'gateway_transaction_id' => 1,
        'data' => serialize(array('test' => true))
    ),
    array(
        'transaction_id' => 2,
        'order_id' => 2,
        'total' => 1111,
        'currency' => 'USD',
        'created' => 1234567892,
        'payment_method' => 'test_payment',
        'gateway_transaction_id' => 2,
        'data' => serialize(array('test' => true))
    )
);
