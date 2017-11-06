<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'id' => 'test_1',
        'value' => 'test_value_1',
        'created' => 1234567890,
        'serialized' => 0
    ),
    array(
        'id' => 'test_2',
        'value' => serialize(array('test' => true)),
        'created' => 1234567891,
        'serialized' => 1
    )
);
