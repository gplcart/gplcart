<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'jquery' => array(
        'name' => 'Jquery', // @text
        'description' => 'jQuery JavaScript Library', // @text
        'type' => 'asset',
        'url' => 'https://github.com/jquery/jquery',
        'download' => 'https://code.jquery.com/jquery-2.2.4.min.js',
        'version_source' => array(
            'file' => 'jquery-2.2.4.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'jquery-2.2.4.min.js',
        )
    ),
    'jquery_ui' => array(
        'name' => 'jQuery UI', // @text
        'description' => 'jQuery user interface library', // @text
        'type' => 'asset',
        'url' => 'https://jqueryui.com',
        'download' => 'https://jqueryui.com/resources/download/jquery-ui-1.12.1.zip',
        'version_source' => array(
            'file' => 'jquery-ui.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'jquery-ui.min.js',
            'jquery-ui.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.7.0',
        )
    )
);
