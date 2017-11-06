<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'user_id' => 1,
        'created' => 1234567890,
        'modified' => 1234567891,
        'status' => 1,
        'role_id' => 1,
        'store_id' => 1,
        'email' => 'test@test.com',
        'name' => 'User name',
        'timezone' => 'Europe/London',
        'hash' => 'hash1234567890',
        'data' => serialize(array())
    ),
    array(
        'user_id' => 2,
        'created' => 1234567892,
        'modified' => 1234567893,
        'status' => 0,
        'role_id' => 2,
        'store_id' => 2,
        'email' => 'test2@test.com',
        'name' => 'User name 2',
        'timezone' => 'Europe/London',
        'hash' => 'hash1234567891',
        'data' => serialize(array())
    )
);
