<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    array(
        'file_id' => 1,
        'entity_id' => 1,
        'created' => 1234567890,
        'modified' => 1234567892,
        'weight' => -9,
        'entity' => 'product',
        'file_type' => 'image',
        'title' => 'File title 1',
        'mime_type' => 'image/png',
        'path' => 'some/path/image.png',
        'description' => 'File description 1',
    ),
    array(
        'file_id' => 2,
        'entity_id' => 1,
        'created' => 1234567890,
        'modified' => 1234567892,
        'weight' => 9,
        'entity' => 'page',
        'file_type' => 'text',
        'title' => 'File title 2',
        'mime_type' => 'text/csv',
        'path' => 'some/path/text.csv',
        'description' => 'File description 2',
    )
);
