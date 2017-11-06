<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'role_id' => 1,
        'status' => 1,
        'name' => 'Role name',
        'redirect' => 'some/path',
        'permissions' => serialize(array('access'))
    ),
    array(
        'role_id' => 2,
        'status' => 0,
        'name' => 'Role name 2',
        'redirect' => '',
        'permissions' => serialize(array('access'))
    )
);
