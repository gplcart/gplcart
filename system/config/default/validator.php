<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    // File type validators
    'image' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\FileType', 'image')
        )
    ),
    'csv' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\FileType', 'csv')
        )
    ),
    'json' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\FileType', 'json')
        )
    ),
    'zip' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\FileType', 'zip')
        )
    ),
    // Component validators
    'cart' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Cart', 'cart')
        )
    ),
    'category' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Category', 'category')
        )
    ),
    'category_group' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CategoryGroup', 'categoryGroup')
        )
    ),
    'city' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\City', 'city')
        )
    ),
    'collection' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Collection', 'collection')
        )
    ),
    'collection_item' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\CollectionItem', 'collectionItem')
        )
    ),
    'country' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Country', 'country')
        )
    ),
    'compare' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Compare', 'compare')
        )
    ),
    'address' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Address', 'address')
        )
    ),
    'currency' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Currency', 'currency')
        )
    ),
    'field' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Field', 'field')
        )
    ),
    'field_value' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\FieldValue', 'fieldValue')
        )
    ),
    'file' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\File', 'file')
        )
    ),
    'image_style' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ImageStyle', 'imageStyle')
        )
    ),
    'install' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Install', 'install')
        )
    ),
    'language' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Language', 'language')
        )
    ),
    'page' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Page', 'page')
        )
    ),
    'price_rule' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\PriceRule', 'priceRule')
        )
    ),
    'product' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Product', 'product')
        )
    ),
    'product_class' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ProductClass', 'productClass')
        )
    ),
    'product_bundle' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\ProductBundle', 'productBundle')
        )
    ),
    'rating' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Rating', 'rating')
        )
    ),
    'review' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Review', 'review')
        )
    ),
    'state' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\State', 'state')
        )
    ),
    'store' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Store', 'store')
        )
    ),
    'translation_upload' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Translation', 'upload')
        )
    ),
    'trigger' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Trigger', 'trigger')
        )
    ),
    'user' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\User', 'user')
        )
    ),
    'user_login' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\User', 'login')
        )
    ),
    'user_reset_password' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\User', 'resetPassword')
        )
    ),
    'user_role' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\UserRole', 'userRole')
        )
    ),
    'zone' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Zone', 'zone')
        )
    ),
    'wishlist' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Wishlist', 'wishlist')
        )
    ),
    'order' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\components\\Order', 'order')
        )
    ),
    // Element validators
    'required' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'required')
        )
    ),
    'numeric' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'numeric')
        )
    ),
    'integer' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'integer')
        )
    ),
    'length' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'length')
        )
    ),
    'regexp' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'regexp')
        )
    ),
    'dateformat' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'dateformat')
        )
    ),
    'json_encoded' => array(
        'handlers' => array(
            'validate' => array('gplcart\\core\\handlers\\validator\\elements\\Common', 'json')
        )
    )
);

