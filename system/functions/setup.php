<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Checks critical requirements
 */
function gplcart_setup_requirements()
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
 * @return null
 */
function gplcart_setup_server()
{
    if (GC_CLI) {
        return null;
    }

    if (!isset($_SERVER['HTTP_REFERER'])) {
        $_SERVER ['HTTP_REFERER'] = '';
    }

    if (!isset($_SERVER['SERVER_PROTOCOL']) || ($_SERVER['SERVER_PROTOCOL'] !== 'HTTP/1.0'//
            && $_SERVER['SERVER_PROTOCOL'] !== 'HTTP/1.1')) {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    }

    if (!isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = '';
        return null;
    }

    $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);

    if (!gplcart_valid_host($_SERVER['HTTP_HOST'])) {
        header("{$_SERVER['SERVER_PROTOCOL']} 400 Bad Request");
        exit;
    }

    return null;
}

/**
 * Sets up PHP ini options
 */
function gplcart_setup_ini()
{
    gplcart_setup_ini_session();
    gplcart_setup_ini_memory('1G');
}

/**
 * Sets session INI
 */
function gplcart_setup_ini_session()
{
    if (GC_CLI) {
        return null;
    }

    ini_set('session.use_cookies', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.use_trans_sid', '0');
    ini_set('session.cache_limiter', '');
    ini_set('session.cookie_httponly', '1');
    return null;
}

/**
 * Checks and tries to increase memory_limit if needed
 * @param string $value
 * @return null
 */
function gplcart_setup_ini_memory($value)
{
    if (!GC_CLI) {
        return null;
    }

    $bytes = gplcart_to_bytes($value);
    $limit = trim(ini_get('memory_limit'));

    if ($limit != -1 && $bytes < 1024 * 1024 * 1024) {
        ini_set('memory_limit', $value);
    }

    return null;
}

/**
 * Provides class autoloading functionality
 * @param string $namespace
 * @return boolean
 */
function gplcart_setup_autoload()
{
    return spl_autoload_register(function($namespace) {

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
    });
}
