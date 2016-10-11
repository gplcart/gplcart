<?php

use core\Container;

error_reporting(E_ALL);

if (version_compare(PHP_VERSION, '5.4.0') < 0) {
    exit('Your PHP installation is too old. GPL Cart requires at least PHP 5.4.0');
}

if (ini_get('session.auto_start')) {
    exit('"session.auto_start" must be set to 0 in your PHP settings');
}

if (!function_exists('mb_internal_encoding')) {
    exit('"mbstring" must be enabled in your PHP settings');
}

define('GC_VERSION', '1.0.0');
define('GC_ROOT_DIR', getcwd());
define('GC_SYSTEM_DIR', GC_ROOT_DIR . '/system');
define('GC_CORE_DIR', GC_SYSTEM_DIR . '/core');
define('GC_MODULE_DIR', GC_SYSTEM_DIR . '/modules');
define('GC_LIBRARY_DIR', GC_SYSTEM_DIR . '/libraries');
define('GC_CACHE_DIR', GC_ROOT_DIR . '/cache');
define('GC_FILE_DIR', GC_ROOT_DIR . '/files');
define('GC_HELP_DIR', GC_SYSTEM_DIR . '/help');
define('GC_CONFIG_OVERRIDE', GC_SYSTEM_DIR . '/config/override.php');
define('GC_CONFIG_COMMON', GC_SYSTEM_DIR . '/config/common.php');
define('GC_CONFIG_DATABASE', GC_SYSTEM_DIR . '/config/database.php');
define('GC_CONFIG_LANGUAGE', GC_SYSTEM_DIR . '/config/language.php');
define('GC_CONFIG_COUNTRY', GC_SYSTEM_DIR . '/config/country.php');
define('GC_CONFIG_COMMON_DEFAULT', GC_SYSTEM_DIR . '/config/common.default.php');
define('GC_UPLOAD_DIR', GC_FILE_DIR . '/upload');
define('GC_DOWNLOAD_DIR', GC_FILE_DIR . '/download');
define('GC_PRIVATE_DIR', GC_FILE_DIR . '/private');
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
define('GC_MARKETPLACE_API_URL', 'http://gplcart.com/api/marketplace');
define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);

if (!isset($_SERVER['HTTP_REFERER'])) {
    $_SERVER ['HTTP_REFERER'] = '';
}

if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0' && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
    $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
}

if (isset($_SERVER['HTTP_HOST'])) {
    $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);

    $valid_host = (strlen($_SERVER['HTTP_HOST']) <= 1000 && substr_count($_SERVER['HTTP_HOST'], '.') <= 100 && substr_count($_SERVER['HTTP_HOST'], ':') <= 100 && preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_SERVER['HTTP_HOST']));

    if (!$valid_host) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        exit;
    }
} else {
    $_SERVER['HTTP_HOST'] = '';
}

ini_set('session.use_cookies', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
ini_set('session.cache_limiter', '');
ini_set('session.cookie_httponly', '1');

mb_language('uni');
mb_internal_encoding('UTF-8');

spl_autoload_register(function ($namespace) {

    $path = str_replace('\\', '/', $namespace);
    $file = GC_SYSTEM_DIR . "/$path.php";

    if (file_exists($file)) {
        include $file;
    }
});

Container::instance('core\\Facade')->route();
