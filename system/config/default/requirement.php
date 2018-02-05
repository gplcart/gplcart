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
            'message' => 'GD extension installed', // @text
        ),
        'pdo' => array(
            'status' => extension_loaded('pdo'),
            'severity' => 'danger',
            'message' => 'PDO extension installed' // @text
        ),
        'spl' => array(
            'status' => extension_loaded('spl'),
            'severity' => 'danger',
            'message' => 'SPL extension installed' // @text
        ),
        'fileinfo' => array(
            'status' => extension_loaded('fileinfo'),
            'severity' => 'danger',
            'message' => 'FileInfo extension installed' // @text
        ),
        'ctype' => array(
            'status' => extension_loaded('ctype'),
            'severity' => 'danger',
            'message' => 'Ctype extension installed' // @text
        ),
        'json' => array(
            'status' => function_exists('json_decode'),
            'severity' => 'danger',
            'message' => 'JSON extension installed' // @text
        ),
    ),
    'php' => array(
        'allow_url_fopen' => array(
            'status' => !ini_get('allow_url_fopen'),
            'severity' => 'warning',
            'message' => 'Directive "allow_url_fopen" is disabled' // @text
        )
    ),
    'files' => array(
        'system_directory' => array(
            'status' => is_writable(GC_DIR_CONFIG_COMPILED),
            'severity' => 'danger',
            'message' => 'Directory "/system/config/compiled" exists and writable' // @text
        ),
        'cache_directory' => array(
            'status' => is_writable(GC_DIR_CACHE),
            'severity' => 'danger',
            'message' => 'Directory "/cache" exists and writable' // @text
        )
    )
);
