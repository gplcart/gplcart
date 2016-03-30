<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * WARNING! Invalid code in this file can break down your site!
 * To adjust a setting uncomment it by removing the leading #
 */

# $config['admin_autocomplete_limit'] = 10; // Number of autocomplete suggestions for an admin
# $config['admin_image_style'] = 2; // Image style for admin UI
# $config['admin_list_limit'] = 20; // Max number of items to be shown for an admin
# $config['autocomplete_limit'] = 10; // Max number of items to be shown for customers
# $config['dashboard_limit'] = 10; // Max number of items in to be shown on dashboard
# $config['dashboard_severity'] = 'info'; // System event severity to be shown on dashboard
# $config['theme_backend'] = 'backend'; // Backend theme module name
# $config['bookmark_limit'] = 20; // Max number of bookmarks for anonymous
# $config['bookmark_limit_*'] = 20; // Max number of bookmarks per role. Replace asterisk with the role ID
# $config['cart_cookie_lifespan'] = 31536000; // Lifetime of cookie that keeps an anonymous cart, in seconds
# $config['cart_login_merge'] = 0; // Whether to merge old and current cart items on login when a customer is checking out
# $config['cart_preview_limit'] = 5; // Max number of cart items to be shown in the cart preview
# $config['cart_sku_limit'] = 10; // Max number of cart items per SKU that customer may have
# $config['cart_total_limit'] = 20; // Max number of all cart items that customer may have
# $config['category_alias_pattern'] = '%t.html'; // Pattern used to generate a category alias
# $config['category_alias_placeholder'] = array('%t' => 'title'); // Replacement rule used to generate a category alias
# $config['category_image_dirname'] = 'category'; // Category image upload directory
# $config['country'] = ''; // Default store country (code)
# $config['cron_interval'] = 86400; // Interval between cron executions, in seconds
# $config['cron_key'] = ''; // Cron secret key
# $config['csv_delimiter'] = ","; // Field delimiter used in CSV files
# $config['csv_delimiter_multiple'] = "|";
# $config['csv_delimiter_key_value'] = ":";
# $config['csv_auto'] = '*';
# $config['currency'] = 'USD'; // Default store currency
# $config['currency_cookie_lifespan'] = 31536000; // Lifetime of cookie that keeps the current currency, in seconds
# $config['date_format'] = 'd.m.Y H:i'; // Default site-wide time format
# $config['error_level'] = 2; // Error reporting level
# $config['export_lifespan'] = 86400; // Delete CSV export files after this amount of time (seconds)
# $config['export_limit'] = 50; // Rows per one export iteration
# $config['field_value_image_dirname'] = 'field_value'; // Field value image upload directory
# $config['file_upload_translit'] = 1; // Whether to transliterate names of uploaded files
# $config['ga_cache_lifespan'] = 86400; // Lifetime of Google Analytics cache, in seconds
# $config['history_lifespan'] = 2628000;
# $config['image_cache_lifetime'] = 31536000;
# $config['import_lifespan'] = 86400;
# $config['import_limit'] = 10;
# $config['kint'] = 0;
# $config['language'] = ''; // Default language
# $config['log_lifespan'] = 86400;
# $config['theme_mobile'] = 'mobile';
# $config['notification_summary_limit'] = 10; // Max number of notifications to be shown in the summary
# $config['no_image'] = 'image/misc/no-image.png';
# $config['order_status'] = 'pending';
# $config['page_alias_check'] = 1;
# $config['page_alias_pattern'] = '%t.html';
# $config['page_alias_placeholder'] = array('%t' => 'title');
# $config['page_image_dirname'] = 'page';
# $config['payment_cod_price'] = 0;
# $config['product_alias_pattern'] = '%t.html';
# $config['product_alias_placeholder'] = array('%t' => 'title');
# $config['product_comparison_cookie_lifespan'] = 604800; // Lifetime of cookie that keeps products to compare, in seconds
# $config['product_comparison_limit'] = 10; // Max products to compare
# $config['product_height'] = 0;
# $config['product_image_dirname'] = 'product';
# $config['product_image_preset'] = 6;
# $config['product_length'] = 0;
# $config['product_recent_cookie_lifespan'] = 31536000;
# $config['product_recent_limit'] = 12;
# $config['product_related_limit'] = 12;
# $config['product_sku_pattern'] = 'PRODUCT-%i';
# $config['product_sku_placeholder'] = array('%i' => 'product_id');
# $config['product_subtract'] = 1; // Default state of "Subtract" option when editing a product
# $config['product_thumb_preset'] = 2;
# $config['product_volume_unit'] = 'mm';
# $config['product_weight'] = 0;
# $config['product_weight_unit'] = 'g';
# $config['product_width'] = 0;
# $config['rating_editable'] = 1; //Whether to allow to add/edit ratings
# $config['rating_enabled'] = 1; // Whether to show product ratings
# $config['rating_unvote'] = 1; // Whether to allow to delete ratings
# $config['review_deletable'] = 1; // Whether to allow to delete reviews
# $config['review_editable'] = 1; //Whether to allow to add/edit reviews
# $config['review_enabled'] = 1; // Whether to show product reviews
# $config['report_errors'] = 1;
# $config['report_log_lifespan'] = 86400;
# $config['review_length'] = 100;
# $config['review_limit'] = 10; // Reviews per page
# $config['review_status'] = 1; // Default review status if added/edited by a customer
# $config['shipping_pickup_price'] = 0;
# $config['store'] = 1; // Default store database ID
# $config['summary_delimiter'] = '<!--summary-->';
# $config['timezone'] = 'Europe/London';
# $config['user_address_limit'] = 6; // Max number of addresses the user may have
# $config['user_cookie_name'] = 'user_id'; // Name of cookie that keeps user UID
# $config['user_login_redirect_*'] = ''; // Redirect URL per user role ID
# $config['user_login_redirect_superadmin'] = 'admin'; // Redirect URL for superadmin
# $config['user_password_max_length'] = 255;
# $config['user_password_min_length'] = 8;
# $config['user_registration_email_admin'] = 1; // Send email to admin when account is registered
# $config['user_registration_email_customer'] = 1; // Send email to a customer when its account is registered
# $config['user_registration_login'] = 1; // Login registered user immediately
# $config['user_registration_status'] = 1; //Default account status upon registration
# $config['user_reset_password_lifespan'] = 86400;
# $config['user_superadmin'] = 1; // Superadmin database UID

/**
 * End of configurable settings. The settings below are appended automatically during installation.
 * Do not touch them unless you know what you do
 */
