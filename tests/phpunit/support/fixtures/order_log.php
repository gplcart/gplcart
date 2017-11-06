<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'order_log_id' => 1,
        'user_id' => 1,
        'order_id' => 1,
        'created' => 1234567890,
        'text' => 'Test 1',
        'data' => serialize(array('test' => true))
    ),
    array(
        'order_log_id' => 2,
        'user_id' => 2,
        'order_id' => 2,
        'created' => 1234567890,
        'text' => 'Test 2',
        'data' => serialize(array('test' => true))
    )
);
