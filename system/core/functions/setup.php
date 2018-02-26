<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

/**
 * Checks critical system requirements
 */
function gplcart_setup_requirements()
{
    if (version_compare(PHP_VERSION, '5.4.0') < 0) {
        exit('Your PHP installation is too old. GPLCart requires at least PHP 5.4.0');
    }

    if (!function_exists('mb_internal_encoding')) {
        exit('"mbstring" must be enabled in your PHP settings');
    }
}

/**
 * Check and fix if needed some importan server vars
 */
function gplcart_setup_server()
{
    if (GC_CLI) {
        return null;
    }

    if (!isset($_SERVER['SERVER_PROTOCOL']) || !in_array($_SERVER['SERVER_PROTOCOL'], array('HTTP/1.0', 'HTTP/1.1'), true)) {
        $_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.0';
    }

    if (!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'], 1);
        if (isset($_SERVER['QUERY_STRING'])) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
        }
    }

    if (!isset($_SERVER['HTTP_HOST'])) {
        $_SERVER['HTTP_HOST'] = getenv('HTTP_HOST');
    }

    $_SERVER['HTTP_HOST'] = strtolower($_SERVER['HTTP_HOST']);
}

/**
 * Sets up PHP INI options
 */
function gplcart_setup_php()
{
    if (GC_CLI) {
        ini_set('html_errors', '0');
        ini_set('memory_limit', '-1');
    } else {
        ini_set('session.use_cookies', '1');
        ini_set('session.use_trans_sid', '0');
        ini_set('session.cache_limiter', '');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.use_only_cookies', '1');
    }
}

/**
 * Set up class autoloading
 */
function gplcart_setup_autoload()
{
    spl_autoload_register(function ($namespace) {

        $path = substr(str_replace('\\', '/', $namespace), 8);

        $file = strpos($path, 'tests') === 0 ? GC_DIR : GC_DIR_SYSTEM;
        $file .= "/$path.php";

        if (file_exists($file)) {
            require_once $file;
        } else {

            $lowerfile = strtolower($file);

            foreach (glob(dirname($file) . '/*') as $file) {
                if (strtolower($file) == $lowerfile) {
                    require_once $file;
                    break;
                }
            }
        }
    });
}

/**
 * Include optional vendor files
 */
function gplcart_setup_vendor()
{
    $lock = GC_DIR . '/composer.lock';
    $autoload = GC_DIR . "/vendor/autoload.php";

    if (is_file($lock) && is_file($autoload)) {
        include_once $autoload;
    }
}

/**
 * Setup internal encoding
 */
function gplcart_setup_encoding()
{
    mb_language('uni');
    mb_internal_encoding('UTF-8');
}
