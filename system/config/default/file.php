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
        'validator' => 'image',
        'path' => GC_DIR_IMAGE
    ),
    'json' => array(
        'extensions' => array('json'),
        'validator' => 'json',
        'path' => GC_DIR_UPLOAD
    ),
    'csv' => array(
        'extensions' => array('csv'),
        'validator' => 'csv',
        'path' => GC_DIR_UPLOAD
    ),
    'zip' => array(
        'extensions' => array('zip'),
        'validator' => 'zip',
        'path' => GC_DIR_UPLOAD
    )
);

