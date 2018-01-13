<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'code' => 'US',
        'name' => 'USA',
        'native_name' => 'USA',
        'status' => 0,
        'weight' => -9,
        'format' => serialize(array('phone' => array())),
        'zone_id' => 1
    ),
    array(
        'code' => 'UK',
        'name' => 'Ukraine',
        'native_name' => 'Ukraine',
        'status' => 1,
        'weight' => 9,
        'format' => serialize(array('phone' => array())),
        'zone_id' => 2
    )
);
