<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'product' => array(
        'title' => 'Product',
        'id_key' => 'product_id',
        'handlers' => array(
            'list' => array('gplcart\\core\\models\\Product', 'getList'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'product'),
        ),
        'template' => array(
            'item' => 'product/item/grid',
            'list' => 'collection/list/product'
        ),
    ),
    'file' => array(
        'title' => 'File',
        'id_key' => 'file_id',
        'handlers' => array(
            'list' => array('gplcart\\core\\models\\File', 'getList'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'file'),
        ),
        'template' => array(
            'item' => 'collection/item/file',
            'list' => 'collection/list/file'
        )
    ),
    'page' => array(
        'title' => 'Page',
        'id_key' => 'page_id',
        'handlers' => array(
            'list' => array('gplcart\\core\\models\\Page', 'getList'),
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'page'),
        ),
        'template' => array(
            'item' => 'collection/item/page',
            'list' => 'collection/list/page'
        )
    )
);
