<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'scope_product' => array(
        'title' => 'Product scope', // @text
        'description' => 'Make a trigger available only for products. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'product'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'product')
        ),
    ),
    'scope_cart' => array(
        'title' => 'Cart scope', // @text
        'description' => 'Make a trigger available only for cart items. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'cart'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'cart')
        ),
    ),
    'scope_order' => array(
        'title' => 'Order scope', // @text
        'description' => 'Make a trigger available only for orders. Only <code>=</code> and <code>!=</code> operators allowed. One boolean parameter', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Scope', 'order'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Scope', 'order')
        ),
    ),
    'url_route' => array(
        'title' => 'System URL route', // @text
        'description' => 'Parameters: system route pattern, e.g "product/(\d+)". Only <code>=</code> and <code>!=</code> operators allowed', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'route'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'route'),
        ),
    ),
    'url_path' => array(
        'title' => 'URL path', // @text
        'description' => 'Parameters: URL path with regexp pattern, e.g "account/(\d+)". Only <code>=</code> and <code>!=</code> operators allowed. No trailing slashes!', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Url', 'path'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Url', 'path'),
        ),
    ),
    'user_id' => array(
        'title' => 'User ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'id'),
        ),
    ),
    'user_role_id' => array(
        'title' => 'User role ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\User', 'roleId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\User', 'roleId'),
        ),
    ),
    'date' => array(
        'title' => 'Current date', // @text
        'description' => 'Parameters: One value in time format. See http://php.net/manual/en/datetime.formats.php', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Date', 'date'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Date', 'current'),
        ),
    ),
    'pricerule_used' => array(
        'title' => 'Number of times a price rule code (coupon) has been used', // @text
        'description' => 'Parameters: One numeric value', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\PriceRule', 'used'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Base', 'validateInteger'),
        ),
    ),
    'order_shipping_method' => array(
        'title' => 'Shipping method', // @text
        'description' => 'Parameters: list of shipping method ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'shippingMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'shippingMethod'),
        ),
    ),
    'order_payment_method' => array(
        'title' => 'Order payment method', // @text
        'description' => 'Parameters: list of payment method ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Order', 'paymentMethod'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Order', 'paymentMethod'),
        ),
    ),
    'shipping_country_code' => array(
        'title' => 'Order shipping country code', // @text
        'description' => 'Parameters: list of country codes, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'countryCode'),
        ),
    ),
    'shipping_state_id' => array(
        'title' => 'Order shipping country state ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'stateId'),
        ),
    ),
    'shipping_zone_id' => array(
        'title' => 'Order shipping address zone ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Shipping', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Shipping', 'zoneId'),
        ),
    ),
    'payment_country_code' => array(
        'title' => 'Order payment country code', // @text
        'description' => 'Parameters: list of country codes, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'countryCode'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'countryCode'),
        ),
    ),
    'payment_state_id' => array(
        'title' => 'Order payment country state ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'stateId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'stateId'),
        ),
    ),
    'payment_zone_id' => array(
        'title' => 'Order payment address zone ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Payment', 'zoneId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Payment', 'zoneId'),
        ),
    ),
    'cart_total' => array(
        'title' => 'Cart total', // @text
        'description' => 'Parameters: one value in format "price|currency". If only price specified, default currency will be used', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'total'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Price', 'price'),
        ),
    ),
    'cart_product_id' => array(
        'title' => 'Cart contains product ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'productId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'cart_sku' => array(
        'title' => 'Cart contains SKU', // @text
        'description' => 'Parameters: list of SKU, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Cart', 'sku'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'sku'),
        ),
    ),
    'product_id' => array(
        'title' => 'Product ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'id'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id'),
        ),
    ),
    'product_category_id' => array(
        'title' => 'Product category ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'categoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    ),
    'product_brand_category_id' => array(
        'title' => 'Product brand category ID', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\Product', 'brandCategoryId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'categoryId'),
        ),
    ),
    'product_bundle_item_id' => array(
        'title' => 'Product ID that is bundled item', // @text
        'description' => 'Parameters: list of ID, separated by comma', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\ProductBundle', 'itemId'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Product', 'id')
        ),
    ),
    'product_bundle_item_count' => array(
        'title' => 'Number of bundled product items', // @text
        'description' => 'Number of bundled product items. One numeric parameter', // @text
        'handlers' => array(
            'process' => array('gplcart\\core\\handlers\\condition\\ProductBundle', 'itemCount'),
            'validate' => array('gplcart\\core\\handlers\\validator\\condition\\Base', 'validateInteger')
        ),
    )
);
