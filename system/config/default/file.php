<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'image' => array(
        'extensions' => array('jpg', 'jpeg', 'gif', 'png'),
        'validator' => 'image'
    ),
    'json' => array(
        'extensions' => array('json'),
        'validator' => 'json'
    ),
    'csv' => array(
        'extensions' => array('csv'),
        'validator' => 'csv'
    ),
    'zip' => array(
        'extensions' => array('zip'),
        'validator' => 'zip'
    )
);

