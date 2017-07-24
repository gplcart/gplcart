<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'extensions' => array(
        'gd' => array(
            'status' => extension_loaded('gd'),
            'severity' => 'danger',
            'message' => 'GD extension installed',
        ),
        'pdo' => array(
            'status' => extension_loaded('pdo'),
            'severity' => 'danger',
            'message' => 'PDO extension installed'
        ),
        'spl' => array(
            'status' => extension_loaded('spl'),
            'severity' => 'danger',
            'message' => 'SPL extension installed'
        ),
        'curl' => array(
            'status' => extension_loaded('curl'),
            'severity' => 'danger',
            'message' => 'CURL extension installed'
        ),
        'fileinfo' => array(
            'status' => extension_loaded('fileinfo'),
            'severity' => 'danger',
            'message' => 'FileInfo extension installed'
        ),
        'openssl' => array(
            'status' => extension_loaded('openssl'),
            'severity' => 'danger',
            'message' => 'OpenSSL extension installed'
        ),
        'ctype' => array(
            'status' => extension_loaded('ctype'),
            'severity' => 'danger',
            'message' => 'Ctype extension installed'
        ),
        'json' => array(
            'status' => function_exists('json_decode'),
            'severity' => 'danger',
            'message' => 'JSON extension installed'
        ),
    ),
    'php' => array(
        'allow_url_fopen' => array(
            'status' => !ini_get('allow_url_fopen'),
            'severity' => 'warning',
            'message' => '"allow_url_fopen" directive disabled'
        )
    ),
    'files' => array(
        'system_directory' => array(
            'status' => is_writable(GC_CONFIG_DIR . '/runtime'),
            'severity' => 'danger',
            'message' => '"/system/config/runtime" directory exists and writable'
        ),
        'cache_directory' => array(
            'status' => is_writable(GC_CACHE_DIR),
            'severity' => 'danger',
            'message' => '"/cache" directory exists and writable'
        )
    )
);
