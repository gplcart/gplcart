<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
define('GC_VERSION', '1.0.0');
define('GC_START', microtime(true));
define('GC_CLI', PHP_SAPI === 'cli');
define('GC_WIN', strtoupper(substr(PHP_OS, 0, 3)) === 'WIN');
define('GC_ROOT_DIR', realpath(__DIR__ . '/../'));
define('GC_TEST_DIR', GC_ROOT_DIR . '/tests');
define('GC_TEST_UNIT_DIR', GC_TEST_DIR . '/unit');
define('GC_SYSTEM_DIR', GC_ROOT_DIR . '/system');
define('GC_CORE_DIR', GC_SYSTEM_DIR . '/core');
define('GC_MODULE_DIR', GC_SYSTEM_DIR . '/modules');
define('GC_CACHE_DIR', GC_ROOT_DIR . '/cache');
define('GC_FILE_DIR', GC_ROOT_DIR . '/files');
define('GC_ASSET_DIR', GC_FILE_DIR . '/assets');
define('GC_ASSET_LIBRARY_DIR', GC_ASSET_DIR . '/libraries');
define('GC_COMPRESSED_ASSET_DIR', GC_ASSET_DIR . '/compressed');
define('GC_CONFIG_DIR', GC_SYSTEM_DIR . '/config');
define('GC_CONFIG_OVERRIDE', GC_CONFIG_DIR . '/runtime/override.php');
define('GC_CONFIG_COMMON', GC_CONFIG_DIR . '/runtime/common.php');
define('GC_CONFIG_DATABASE', GC_CONFIG_DIR . '/default/database.php');
define('GC_CONFIG_LANGUAGE', GC_CONFIG_DIR . '/default/language.php');
define('GC_CONFIG_COUNTRY', GC_CONFIG_DIR . '/default/country.php');
define('GC_CONFIG_CURRENCY', GC_CONFIG_DIR . '/default/currency.php');
define('GC_CONFIG_ROUTE', GC_CONFIG_DIR . '/default/route.php');
define('GC_CONFIG_CLI_ROUTE', GC_CONFIG_DIR . '/default/cli_route.php');
define('GC_CONFIG_PERMISSION', GC_CONFIG_DIR . '/default/permission.php');
define('GC_CONFIG_COMMON_DEFAULT', GC_CONFIG_DIR . '/default/common.php');
define('GC_CONFIG_VALIDATOR', GC_CONFIG_DIR . '/default/validator.php');
define('GC_CONFIG_IMAGE_STYLE', GC_CONFIG_DIR . '/default/image_style.php');
define('GC_CONFIG_IMAGE_ACTION', GC_CONFIG_DIR . '/default/image_action.php');
define('GC_CONFIG_LIBRARY', GC_CONFIG_DIR . '/default/library.php');
define('GC_CONFIG_MAIL', GC_CONFIG_DIR . '/default/mail.php');
define('GC_CONFIG_COLLECTION', GC_CONFIG_DIR . '/default/collection.php');
define('GC_CONFIG_CONDITION', GC_CONFIG_DIR . '/default/condition.php');
define('GC_CONFIG_COUNTRY_FORMAT', GC_CONFIG_DIR . '/default/country_format.php');
define('GC_CONFIG_DASHBOARD', GC_CONFIG_DIR . '/default/dashboard.php');
define('GC_CONFIG_REQUIREMENT', GC_CONFIG_DIR . '/default/requirement.php');
define('GC_UPLOAD_DIR', GC_FILE_DIR . '/upload');
define('GC_DOWNLOAD_DIR', GC_FILE_DIR . '/download');
define('GC_PRIVATE_DIR', GC_FILE_DIR . '/private');
define('GC_PRIVATE_DOWNLOAD_DIR', GC_PRIVATE_DIR . '/download');
define('GC_PRIVATE_TEMP_DIR', GC_PRIVATE_DIR . '/temp');
define('GC_PRIVATE_MODULE_DIR', GC_PRIVATE_DIR . '/modules');
define('GC_IMAGE_DIR', GC_FILE_DIR . '/image/upload');
define('GC_IMAGE_CACHE_DIR', GC_FILE_DIR . '/image/cache');
define('GC_LOCALE_DIR', GC_SYSTEM_DIR . '/locale');
define('GC_LOCALE_JS_DIR', GC_FILE_DIR . '/assets/system/js/locale');
define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);
