<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'image' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'image')
        )
    ),
    'csv' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'csv')
        )
    ),
    'zip' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'zip')
        )
    ),
    'cart' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Cart', 'cart')
        )
    ),
    'category' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Category', 'category')
        )
    ),
    'category_group' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\CategoryGroup', 'categoryGroup')
        )
    ),
    'city' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\City', 'city')
        )
    ),
    'collection' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Collection', 'collection')
        )
    ),
    'collection_item' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\CollectionItem', 'collectionItem')
        )
    ),
    'country' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Country', 'country')
        )
    ),
    'compare' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Compare', 'compare')
        )
    ),
    'editor' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Editor', 'editor')
        )
    ),
    'export' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Export', 'export')
        )
    ),
    'address' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Address', 'address')
        )
    ),
    'backup' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Backup', 'backup')
        )
    ),
    'backup_restore' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Backup', 'restore')
        )
    ),
    'currency' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Currency', 'currency')
        )
    ),
    'field' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Field', 'field')
        )
    ),
    'field_value' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\FieldValue', 'fieldValue')
        )
    ),
    'file' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\File', 'file')
        )
    ),
    'filter' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Filter', 'filter')
        )
    ),
    'image_style' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\ImageStyle', 'imageStyle')
        )
    ),
    'import' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Import', 'import')
        )
    ),
    'install' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Install', 'install')
        )
    ),
    'language' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Language', 'language')
        )
    ),
    'module_upload' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Module', 'upload')
        )
    ),
    'page' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Page', 'page')
        )
    ),
    'price_rule' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\PriceRule', 'priceRule')
        )
    ),
    'product' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Product', 'product')
        )
    ),
    'product_class' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\ProductClass', 'productClass')
        )
    ),
    'rating' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Rating', 'rating')
        )
    ),
    'review' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Review', 'review')
        )
    ),
    'settings' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Settings', 'settings')
        )
    ),
    'state' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\State', 'state')
        )
    ),
    'store' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Store', 'store')
        )
    ),
    'translation_upload' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Translation', 'upload')
        )
    ),
    'trigger' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Trigger', 'trigger')
        )
    ),
    'user' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\User', 'user')
        )
    ),
    'user_login' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\User', 'login')
        )
    ),
    'user_reset_password' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\User', 'resetPassword')
        )
    ),
    'user_role' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\UserRole', 'userRole')
        )
    ),
    'zone' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Zone', 'zone')
        )
    ),
    'wishlist' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Wishlist', 'wishlist')
        )
    ),
    'order' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\Order', 'order')
        )
    )
);

