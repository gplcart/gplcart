<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
$handlers = array();

// Files
$handlers['image'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'image')
    ),
);

$handlers['csv'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'csv')
    ),
);

$handlers['zip'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\FileType', 'zip')
    ),
);

// Entity validators
$handlers['cart'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Cart', 'cart')
    ),
);

$handlers['category'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Category', 'category')
    ),
);

$handlers['category_group'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\CategoryGroup', 'categoryGroup')
    ),
);

$handlers['city'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\City', 'city')
    ),
);

$handlers['collection'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Collection', 'collection')
    ),
);

$handlers['collection_item'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\CollectionItem', 'collectionItem')
    ),
);

$handlers['country'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Country', 'country')
    ),
);

$handlers['compare'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Compare', 'compare')
    ),
);

$handlers['editor'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Editor', 'editor')
    ),
);

$handlers['export'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Export', 'export')
    ),
);

$handlers['address'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Address', 'address')
    ),
);

$handlers['backup'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Backup', 'backup')
    ),
);

$handlers['backup_restore'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Backup', 'restore')
    ),
);

$handlers['currency'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Currency', 'currency')
    ),
);

$handlers['field'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Field', 'field')
    ),
);

$handlers['field_value'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\FieldValue', 'fieldValue')
    ),
);

$handlers['file'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\File', 'file')
    ),
);

$handlers['filter'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Filter', 'filter')
    ),
);

$handlers['image_style'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\ImageStyle', 'imageStyle')
    ),
);

$handlers['import'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Import', 'import')
    ),
);

$handlers['install'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Install', 'install')
    ),
);

$handlers['language'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Language', 'language')
    ),
);

$handlers['module_upload'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Module', 'upload')
    ),
);

$handlers['page'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Page', 'page')
    ),
);

$handlers['price_rule'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\PriceRule', 'priceRule')
    ),
);

$handlers['product'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Product', 'product')
    ),
);

$handlers['product_class'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\ProductClass', 'productClass')
    ),
);

$handlers['rating'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Rating', 'rating')
    ),
);

$handlers['review'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Review', 'review')
    ),
);

$handlers['settings'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Settings', 'settings')
    ),
);

$handlers['state'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\State', 'state')
    ),
);

$handlers['store'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Store', 'store')
    ),
);

$handlers['translation_upload'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Translation', 'upload')
    ),
);

$handlers['trigger'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Trigger', 'trigger')
    ),
);

$handlers['user'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\User', 'user')
    ),
);

$handlers['user_login'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\User', 'login')
    ),
);

$handlers['user_reset_password'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\User', 'resetPassword')
    ),
);

$handlers['user_role'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\UserRole', 'userRole')
    ),
);

$handlers['zone'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Zone', 'zone')
    ),
);

$handlers['wishlist'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Wishlist', 'wishlist')
    ),
);

$handlers['order'] = array(
    'handlers' => array(
        'validate' => array('gplcart\\core\\handlers\\validator\\Order', 'order')
    ),
);

return $handlers;
