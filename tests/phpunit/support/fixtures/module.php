<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'module_id' => 'test',
        'status' => 1,
        'weight' => -9,
        'settings' => serialize(array('test' => true))
    ),
    array(
        'module_id' => 'test2',
        'status' => 0,
        'weight' => 9,
        'settings' => serialize(array('test' => true))
    )
);
