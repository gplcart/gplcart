<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'url_route' => array(
        'title' => 'System URL route (global)',
        'description' => 'Parameters: system route pattern. Only = and != operators allowed',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'route'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'route'),
        ),
    ),
    'url_path' => array(
        'title' => 'URL path (global)',
        'description' => 'Parameters: path with regexp pattern. Only = and != operators allowed. Do not use trailing slashes. Example: account/(\d+)',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'path'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'path'),
        ),
    ),
    'user_id' => array(
        'title' => 'User ID (global)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'id'),
        ),
    ),
    'user_role_id' => array(
        'title' => 'User role ID (global)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'roleId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'roleId'),
        ),
    ),
    'date' => array(
        'title' => 'Current date (global)',
        'description' => 'Parameters: One value in time format. See http://php.net/manual/en/datetime.formats.php',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Date', 'date'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Date', 'date'),
        ),
    ),
    'pricerule_used' => array(
        'title' => 'Number of times a price rule code (coupon) has been used (checkout)',
        'description' => 'Parameters: One numeric value',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\PriceRule', 'used'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\PriceRule', 'used'),
        ),
    ),
    'order_shipping_method' => array(
        'title' => 'Shipping method (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'shippingMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'shippingMethod'),
        ),
    ),
    'order_payment_method' => array(
        'title' => 'Payment method (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'paymentMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'paymentMethod'),
        ),
    ),
    'shipping_country_code' => array(
        'title' => 'Shipping country code (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'countryCode'),
        ),
    ),
    'shipping_state_id' => array(
        'title' => 'Shipping state ID (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'stateId'),
        ),
    ),
    'shipping_zone_id' => array(
        'title' => 'Shipping address zone ID (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'zoneId'),
        ),
    ),
    'payment_country_code' => array(
        'title' => 'Payment country code (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'countryCode'),
        ),
    ),
    'payment_state_id' => array(
        'title' => 'Payment state ID (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'stateId'),
        ),
    ),
    'payment_zone_id' => array(
        'title' => 'Payment address zone ID (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'zoneId'),
        ),
    ),
    'cart_total' => array(
        'title' => 'Cart total (checkout)',
        'description' => 'Parameters: one value in format "price|currency". If only price specified, default currency will be used',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'total'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Price', 'price'),
        ),
    ),
    'cart_product_id' => array(
        'title' => 'Cart contains product ID (checkout)',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'productId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'cart_sku' => array(
        'title' => 'Cart contains SKU (checkout)',
        'description' => 'Parameters: list of SKU, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'sku'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'sku'),
        ),
    ),
    'product_id' => array(
        'title' => 'Product ID',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'product_category_id' => array(
        'title' => 'Product category ID',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'categoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    ),
    'product_brand_category_id' => array(
        'title' => 'Product brand category ID',
        'description' => 'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'brandCategoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    )
);
