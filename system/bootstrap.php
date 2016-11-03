<?php

error_reporting(E_ALL);
gplcart_bootstrap_requirements();
gplcart_bootstrap_constants();

mb_language('uni');
mb_internal_encoding('UTF-8');

if (!GC_CLI) {
    gplcart_bootstrap_server();
    gplcart_bootstrap_ini();
}

spl_autoload_register('gplcart_bootstrap_autoload');

// Call facade class
\core\Container::instance('core\\Facade')->route();

/**
 * Checks critical requirements
 * and stops further request processing if they are not met
 */
function gplcart_bootstrap_requirements()
{
    if (version_compare(PHP_VERSION, '5.4.0') < 0) {
        exit('Your PHP installation is too old. GPL Cart requires at least PHP 5.4.0');
    }

    if (ini_get('session.auto_start')) {
        exit('"session.auto_start" must be set to 0 in your PHP settings');
    }

    if (!function_exists('mb_internal_encoding')) {
        exit('"mbstring" must be enabled in your PHP settings');
    }
}

/**
 * Defines system constant
 */
function gplcart_bootstrap_constants()
{
    define('GC_VERSION', '1.0.0');
    define('GC_CLI', (PHP_SAPI === 'cli'));
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
    define('GC_MARKETPLACE_API_URL', 'http://gplcart.com/feed/marketplace');
    define('GC_TIME', (int) $_SERVER['REQUEST_TIME']);
}

/**
 * Check and fix if needed some importan server vars
 */
function gplcart_bootstrap_server()
{
    if (!isset($_SERVER['HTTP_REFERER'])) {
        $_SERVER ['HTTP_REFERER'] = '';
    }

    if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.0'//
            && $_SERVER['SERVER_PROTOCOL'] != 'HTTP/1.1')) {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    }

    if (isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
        gplcart_bootstrap_host();
    } else {
        $_SERVER['HTTP_HOST'] = '';
    }
}

/**
 * Checks server host variable.
 * If not - exit immediately.
 */
function gplcart_bootstrap_host()
{
    $is_valid = (strlen($_SERVER['HTTP_HOST']) <= 1000 //
            && substr_count($_SERVER['HTTP_HOST'], '.') <= 100 //
            && substr_count($_SERVER['HTTP_HOST'], ':') <= 100 //
            && preg_match('/^\[?(?:[a-zA-Z0-9-:\]_]+\.?)+$/', $_SERVER['HTTP_HOST']));

    if (!$is_valid) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        exit;
    }
}

/**
 * Sets up PHP ini options
 */
function gplcart_bootstrap_ini()
{
    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cache_limiter', '');
    ini_set('session.cookie_httponly', '1');
}

/**
 * Autoload function being registered with spl_autoload_register()
 * @param type $namespace
 * @return boolean
 */
function gplcart_bootstrap_autoload($namespace)
{
    $path = str_replace('\\', '/', $namespace);
    $file = GC_SYSTEM_DIR . "/$path.php";

    if (file_exists($file)) {
        include $file;
        return true;
    }

    // Now check lowercase class name
    // to prevent "file not found" for
    // classes like core\\modules\\test_module\\TestModule
    $lowerfile = strtolower($file);

    foreach (glob(dirname($file) . '/*') as $file) {
        if (strtolower($file) == $lowerfile) {
            include $file;
            return true;
        }
    }

    return false;
}
