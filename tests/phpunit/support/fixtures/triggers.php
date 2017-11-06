<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'trigger_id' => 1,
        'name' => 'Trigger name',
        'status' => 1,
        'store_id' => 1,
        'weight' => -9,
        'data' => serialize(array('test' => true))
    ),
    array(
        'trigger_id' => 2,
        'name' => 'Trigger name 2',
        'status' => 0,
        'store_id' => 2,
        'weight' => 9,
        'data' => serialize(array('test' => true))
    )
);
