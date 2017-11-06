<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'store_id' => 1,
        'status' => 1,
        'domain' => 'domain.com',
        'name' => 'Store',
        'basepath' => 'test',
        'created' => 1234567890,
        'modified' => 1234567891,
        'data' => serialize(array('test' => true))
    ),
    array(
        'store_id' => 2,
        'status' => 0,
        'domain' => 'domain2.com',
        'name' => 'Store 2',
        'basepath' => 'test2',
        'created' => 1234567892,
        'modified' => 1234567893,
        'data' => serialize(array('test' => true))
    )
);
