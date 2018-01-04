<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'scope_product' => array(
        'title' => /* @text */'Product scope',
        'description' => /* @text */'Make a trigger available only for products. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'product'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'product')
        ),
    ),
    'scope_cart' => array(
        'title' => /* @text */'Cart scope',
        'description' => /* @text */'Make a trigger available only for cart items. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'cart'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'cart')
        ),
    ),
    'scope_order' => array(
        'title' => /* @text */'Order scope',
        'description' => /* @text */'Make a trigger available only for orders. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'order'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'order')
        ),
    ),
    'url_route' => array(
        'title' => /* @text */'System URL route',
        'description' => /* @text */'Parameters: system route pattern, e.g "product/(\d+)". Only <code>=</code> and <code>!=</code> operators allowed',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'route'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'route'),
        ),
    ),
    'url_path' => array(
        'title' => /* @text */'URL path',
        'description' => /* @text */'Parameters: URL path with regexp pattern, e.g "account/(\d+)". Only <code>=</code> and <code>!=</code> operators allowed. No trailing slashes!',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'path'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'path'),
        ),
    ),
    'user_id' => array(
        'title' => /* @text */'User ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'id'),
        ),
    ),
    'user_role_id' => array(
        'title' => /* @text */'User role ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'roleId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'roleId'),
        ),
    ),
    'date' => array(
        'title' => /* @text */'Current date',
        'description' => /* @text */'Parameters: One value in time format. See http://php.net/manual/en/datetime.formats.php',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Date', 'date'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Date', 'current'),
        ),
    ),
    'pricerule_used' => array(
        'title' => /* @text */'Number of times a price rule code (coupon) has been used',
        'description' => /* @text */'Parameters: One numeric value',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\PriceRule', 'used'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Base', 'validateInteger'),
        ),
    ),
    'order_shipping_method' => array(
        'title' => /* @text */'Shipping method',
        'description' => /* @text */'Parameters: list of shipping method ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'shippingMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'shippingMethod'),
        ),
    ),
    'order_payment_method' => array(
        'title' => /* @text */'Order payment method',
        'description' => /* @text */'Parameters: list of payment method ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'paymentMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'paymentMethod'),
        ),
    ),
    'shipping_country_code' => array(
        'title' => /* @text */'Order shipping country code',
        'description' => /* @text */'Parameters: list of country codes, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'countryCode'),
        ),
    ),
    'shipping_state_id' => array(
        'title' => /* @text */'Order shipping state ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'stateId'),
        ),
    ),
    'shipping_zone_id' => array(
        'title' => /* @text */'Order shipping address zone ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'zoneId'),
        ),
    ),
    'payment_country_code' => array(
        'title' => /* @text */'Order payment country code',
        'description' => 'Parameters: list of country codes, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'countryCode'),
        ),
    ),
    'payment_state_id' => array(
        'title' => /* @text */'Order payment state ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'stateId'),
        ),
    ),
    'payment_zone_id' => array(
        'title' => /* @text */'Order payment address zone ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'zoneId'),
        ),
    ),
    'cart_total' => array(
        'title' => /* @text */'Cart total',
        'description' => /* @text */'Parameters: one value in format "price|currency". If only price specified, default currency will be used',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'total'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Price', 'price'),
        ),
    ),
    'cart_product_id' => array(
        'title' => /* @text */'Cart contains product ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'productId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'cart_sku' => array(
        'title' => /* @text */'Cart contains SKU',
        'description' => /* @text */'Parameters: list of SKU, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'sku'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'sku'),
        ),
    ),
    'product_id' => array(
        'title' => /* @text */'Product ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'product_category_id' => array(
        'title' => /* @text */'Product category ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'categoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    ),
    'product_brand_category_id' => array(
        'title' => /* @text */'Product brand category ID',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'brandCategoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    ),
    'product_bundle_item_id' => array(
        'title' => /* @text */'Product ID that is bundled item',
        'description' => /* @text */'Parameters: list of ID, separated by comma',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\ProductBundle', 'itemId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id')
        ),
    ),
    'product_bundle_item_count' => array(
        'title' => /* @text */'Number of bundled product items',
        'description' => /* @text */'Number of bundled product items. One numeric parameter',
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\ProductBundle', 'itemCount'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Base', 'validateInteger')
        ),
    )
);
