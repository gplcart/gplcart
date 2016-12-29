<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

define('GC_VERSION', '1.0.0');
define('GC_CLI', (PHP_SAPI === 'cli'));
define('GC_CLI_EMULATE', (isset($_POST['cli_token']) && isset($_POST['command'])));
define('GC_ROOT_DIR', realpath(__DIR__ . '/../'));
define('GC_TEST_DIR', GC_ROOT_DIR . '/tests');
define('GC_TEST_UNIT_DIR', GC_TEST_DIR . '/unit');
define('GC_SYSTEM_DIR', GC_ROOT_DIR . '/system');
define('GC_CORE_DIR', GC_SYSTEM_DIR . '/core');
define('GC_MODULE_DIR', GC_SYSTEM_DIR . '/modules');
define('GC_LIBRARY_DIR', GC_SYSTEM_DIR . '/libraries');
define('GC_CACHE_DIR', GC_ROOT_DIR . '/cache');
define('GC_FILE_DIR', GC_ROOT_DIR . '/files');
define('GC_CONFIG_DIR', GC_SYSTEM_DIR . '/config');
define('GC_CONFIG_OVERRIDE', GC_CONFIG_DIR . '/runtime/override.php');
define('GC_CONFIG_COMMON', GC_CONFIG_DIR . '/runtime/common.php');
define('GC_CONFIG_DATABASE', GC_CONFIG_DIR . '/default/database.php');
define('GC_CONFIG_LANGUAGE', GC_CONFIG_DIR . '/default/language.php');
define('GC_CONFIG_COUNTRY', GC_CONFIG_DIR . '/default/country.php');
define('GC_CONFIG_ROUTE', GC_CONFIG_DIR . '/default/route.php');
define('GC_CONFIG_LIBRARY', GC_CONFIG_DIR . '/default/library.php');
define('GC_CONFIG_COMMON_DEFAULT', GC_CONFIG_DIR . '/default/common.php');
define('GC_UPLOAD_DIR', GC_FILE_DIR . '/upload');
define('GC_DOWNLOAD_DIR', GC_FILE_DIR . '/download');
define('GC_PRIVATE_DIR', GC_FILE_DIR . '/private');
define('GC_PRIVATE_BACKUP_DIR', GC_PRIVATE_DIR . '/backup');
define('GC_PRIVATE_DOWNLOAD_DIR', GC_PRIVATE_DIR . '/download');
define('GC_PRIVATE_EXPORT_DIR', GC_PRIVATE_DIR . '/export');
define('GC_PRIVATE_IMPORT_DIR', GC_PRIVATE_DIR . '/import');
define('GC_PRIVATE_EXAMPLES_DIR', GC_PRIVATE_DIR . '/examples');
define('GC_PRIVATE_LOGS_DIR', GC_PRIVATE_DIR . '/logs');
define('GC_IMAGE_DIR', GC_FILE_DIR . '/image/upload');
define('GC_IMAGE_CACHE_DIR', GC_FILE_DIR . '/image/cache');
define('GC_LOCALE_DIR', GC_SYSTEM_DIR . '/locale');
define('GC_LOCALE_JS_DIR', GC_FILE_DIR . '/assets/system/js/locale');
define('GC_SESSION_PREFIX', 'gplcart_');
define('GC_COOKIE_PREFIX', 'gplcart_');
define('GC_WIKI_URL', 'https://github.com/gplcart/gplcart/wiki');
define('GC_DEMO_URL', 'https://gplcart.github.io/demo');
define('GC_MARKETPLACE_API_URL', 'http://gplcart.com/feed/marketplace');
define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);
