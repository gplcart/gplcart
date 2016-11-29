<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

require 'constants.php';

error_reporting(E_ALL);
gplcart_bootstrap_requirements();

mb_language('uni');
mb_internal_encoding('UTF-8');

gplcart_bootstrap_ini();
gplcart_bootstrap_server();

spl_autoload_register('gplcart_bootstrap_autoload');

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
 * Check and fix if needed some importan server vars
 */
function gplcart_bootstrap_server()
{
    if (GC_CLI) {
        return null;
    }

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

    return null;
}

/**
 * Validates server host variable
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
    if (!GC_CLI) {
        ini_set('session.use_cookies', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.cookie_httponly', '1');
        return null;
    }

    $bytes = function ($value) {
        $unit = strtolower(substr($value, -1, 1));
        $value = (int) $value;
        switch ($unit) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }

        return $value;
    };

    $limit = trim(ini_get('memory_limit'));

    if ($limit != -1 && $bytes($limit) < 1024 * 1024 * 1024) {
        ini_set('memory_limit', '1G');
    }

    return null;
}

/**
 * Autoload function being registered with spl_autoload_register()
 * @param type $namespace
 * @return boolean
 */
function gplcart_bootstrap_autoload($namespace)
{
    $path = str_replace('\\', '/', $namespace);

    $file = (strpos($path, 'tests') === 0) ? GC_ROOT_DIR : GC_SYSTEM_DIR;
    $file .= "/$path.php";

    if (file_exists($file)) {
        include $file;
        return true;
    }

    // Check lowercase class name
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
