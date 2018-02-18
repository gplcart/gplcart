<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'product' => array(
        'title' => 'Product', // @text
        'entity' => 'product',
        'handlers' => array(
            /** @uses \gplcart\core\models\Product::getList() */
            'list' => array('gplcart\\core\\models\\Product', 'getList'),
            /** @uses \gplcart\core\handlers\validator\components\CollectionItem::validateProductCollectionItem() */
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'validateProductCollectionItem'),
        ),
        'template' => array(
            'item' => 'product/item/grid',
            'list' => 'collection/list/product'
        ),
    ),
    'file' => array(
        'title' => 'File', // @text
        'entity' => 'file',
        'handlers' => array(
            /** @uses \gplcart\core\models\File::getList() */
            'list' => array('gplcart\\core\\models\\File', 'getList'),
            /** @uses \gplcart\core\handlers\validator\components\CollectionItem::validateFileCollectionItem() */
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'validateFileCollectionItem'),
        ),
        'template' => array(
            'item' => 'collection/item/file',
            'list' => 'collection/list/file'
        )
    ),
    'page' => array(
        'title' => 'Page', // @text
        'entity' => 'page',
        'handlers' => array(
            /** @uses \gplcart\core\models\Page::getList() */
            'list' => array('gplcart\\core\\models\\Page', 'getList'),
            /** @uses \gplcart\core\handlers\validator\components\CollectionItem::validatePageCollectionItem() */
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'validatePageCollectionItem'),
        ),
        'template' => array(
            'item' => 'collection/item/page',
            'list' => 'collection/list/page'
        )
    )
);
