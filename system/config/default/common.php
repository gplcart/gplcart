<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
/**
 * To adjust a setting uncomment it by removing the leading #
 */
$config = array();

# $config['account_order_limit']                  = 10; // Max number of orders per page in accout
# $config['alias']                                = 1; // Whether URL aliasing is enabled
# $config['autocomplete_limit']                   = 10; // Max number of autocomplete suggestions
# $config['bookmark_limit']                       = 5; // Max number of bookmarks to quick access
# $config['blog_limit']                           = 20;// Max posts per blog page
# $config['image_style']                          = 3; // Default image style
# $config['list_limit']                           = 20; // Max number of items for UI
# $config['dashboard_limit']                      = 10; // Max number of items in dashboard blocks
# $config['dashboard_columns']                    = 2; // Number of dashboard columns. Must be multiple of 12
# $config['theme_mobile']                         = 'mobile'; // Default module ID of mobile theme
# $config['theme_backend']                        = 'backend'; // Default module ID of backend theme
# $config['wishlist_limit']                       = 20; // Max number of wishlist items for anonymous
# $config['wishlist_limit_<role ID>']             = 20; // Max number of allowed wishlist items per <role ID>
# $config['cart_cookie_lifespan']                 = 365*24*60*60; // Lifetime of cookie that keeps anonymous cart, in seconds
# $config['cart_login_merge']                     = 0; // Whether to merge old and current cart items on checkout login
# $config['cart_preview_limit']                   = 5; // Max number of cart items to display in cart preview
# $config['cart_sku_limit']                       = 10; // Max number of cart items per SKU that customer may have
# $config['cart_item_limit']                      = 20; // Max total number of cart items that customer may have
# $config['category_alias_pattern']               = '%t.html'; // Pattern to generate category alias
# $config['category_alias_placeholder']           = array('%t' => 'title'); // Replacement rule to generate category alias
# $config['category_image_dirname']               = 'category'; // Default folder for uploaded category images
# $config['cron_interval']                        = 24*60*60; // Interval between cron executions, in seconds
# $config['cron_key']                             = ''; // Cron secret key
# $config['csv_delimiter']                        = ","; // CSV field delimiter
# $config['csv_delimiter_multiple']               = "|"; // Character to separate multiple values in CSV
# $config['csv_delimiter_key_value']              = ":"; // Character to separate key => value items in CSV
# $config['currency']                             = 'USD'; // Default store currency
# $config['currency_cookie_lifespan']             = 365*24*60*60; // Lifetime of cookie that keeps the current currency, in seconds
# $config['date_prefix']                          = 'd.m.Y'; // Default time format - hours
# $config['date_suffix']                          = ' H:i'; // Default time format - minutes
# $config['error_level']                          = 2; // Default error reporting level
# $config['file_upload_translit']                 = 1; // Whether to transliterate names of uploaded files
# $config['history_lifespan']                     = 30*24*60*60; // Max number of seconds to keep records in "history" table
# $config['language']                             = ''; // Default store language
# $config['mailer']                               = 'php'; // Mailer
# $config['no_image']                             = 'image/misc/no-image.png'; // Path to placeholder image
# $config['order_status']                         = 'pending'; // Default order status
# $config['order_status_initial']                 = 'pending'; // Default status for new orders
# $config['order_status_canceled']                = 'canceled'; // Default status for canceled orders
# $config['order_status_awaiting_payment']        = 'pending_payment'; // Default status for awaiting payment orders
# $config['order_status_processing']              = 'processing';
# $config['order_update_notify_customer']         = 1; // Whether to send notification to customer on order status change
# $config['order_log_limit']                      = 5; // Max order log records to display for admin
# $config['order_size_unit']                      = 'mm'; // Default order size unit
# $config['order_weight_unit']                    = 'g'; // Default order weight unit
# $config['page_alias_pattern']                   = '%t.html'; // Pattern to generate page alias
# $config['page_alias_placeholder']               = array('%t' => 'title'); // Replacement rule to generate page alias
# $config['page_image_dirname']                   = 'page'; // Default folder for uploaded page images
# $config['product_alias_pattern']                = '%t.html'; // Pattern to generate product alias
# $config['product_alias_placeholder']            = array('%t' => 'title'); // Replacement rule to generate product alias
# $config['product_compare_cookie_lifespan']      = 30*24*60*60; // Max number of seconds to keeps products to compare in cookie
# $config['product_compare_limit']                = 10; // Max number of products to compare
# $config['product_height']                       = 0; // Default product height (dimension)
# $config['product_length']                       = 0; // Default product length (dimension)
# $config['product_weight']                       = 0; // Default product weight (dimension)
# $config['product_width']                        = 0; // Default product width (dimension)
# $config['product_image_dirname']                = 'product'; // Default folder for uploaded product images
# $config['product_view_limit']                   = 100; // Max number of viewed products
# $config['product_view_pager_limit']             = 4; // Max number of viewed products for pager
# $config['related_limit']                        = 12; // Max number of related products
# $config['related_pager_limit']                  = 4; // Max number of related products for pager
# $config['product_sku_pattern']                  = 'PRODUCT-%i'; // Pattern to generate product SKU
# $config['product_sku_placeholder']              = array('%i' => 'product_id'); // Replacement rule to generate product SKU
# $config['product_subtract']                     = 0; // Default state of "Subtract" option
# $config['product_size_unit']                    = 'mm'; // Default size unit for products
# $config['product_weight_unit']                  = 'g'; // Default weight unit for products
# $config['rating_editable']                      = 1; //Whether to allow to edit product ratings
# $config['rating_enabled']                       = 1; // Whether to allow product ratings
# $config['rating_unvote']                        = 1; // Whether to allow to delete product ratings
# $config['review_deletable']                     = 1; // Whether to allow to delete product reviews
# $config['review_editable']                      = 1; //Whether to allow to edit product reviews
# $config['review_enabled']                       = 1; // Whether to allow product reviews
# $config['review_max_length']                    = 1000; // Max number of characters in product review
# $config['review_min_length']                    = 10; // Min number of characters in product review
# $config['review_limit']                         = 10; // Max number of reviews to show on product pages
# $config['review_status']                        = 1; // Default status for review added by a customer
# $config['report_log_lifespan']                  = 24*60*60; // Max number of seconds to keep records in "log" table
# $config['redirect_default_langcode']            = 1; // Whether to redirect to URL without default language code
# $config['store']                                = 1; // Database ID of default store
# $config['teaser_delimiter']                    = '<!--teaser-->'; // Character(s) to separate summary and full text
# $config['timezone']                             = 'Europe/London'; // Default store timezone
# $config['user_address_limit']                   = 4; // Max number of addresses for logged in user
# $config['user_address_limit_anonymous']         = 1; // Max number of addresses for anonymous user
# $config['user_cookie_name']                     = 'user_id'; // Name of cookie that keeps cart user ID
# $config['user_password_max_length']             = 255; // Max number of password characters
# $config['user_password_min_length']             = 8; // Min number of password characters
# $config['user_registration_email_admin']        = 1; // Whether to send email to admin when an account is registered
# $config['user_registration_email_customer']     = 1; // Whether to send email to customer when his account is registered
# $config['user_registration_login']              = 1; // Whether to log in registered user immediately
# $config['user_registration_status']             = 1; // Default account status upon registration
# $config['user_reset_password_lifespan']         = 24*60*60; // Max number of seconds before password reset link will expire
# $config['user_superadmin']                      = 1; // Default database ID for superadmin
# $config['cli_status']                           = 1; // Enable/disable CLI
# $config['field_value_image_dirname']            = 'field_value'; // Default folder for uploaded field value images
# $config['compress_js']                          = 0; // Whether to aggregate JS files
# $config['compress_css']                         = 0; // Whether to aggregate and compress CSS files
# $config['filter_allowed_tags']                  = array('a', 'i', 'b', 'em', 'span', 'strong', 'ul', 'ol', 'li'); // Array of allowed tags for default filter
# $config['filter_allowed_protocols']             = array('http', 'ftp', 'mailto'); // Array of allowed protocols for default filter

/**
 * End of configurable settings
 * The settings below are appended automatically during installation
 */
