<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'jquery' => array(
        'name' => 'Jquery',
        'description' => 'jQuery JavaScript Library',
        'type' => 'asset',
        'url' => 'https://github.com/jquery/jquery',
        'download' => 'https://code.jquery.com/jquery-2.2.4.min.js',
        'version_source' =>
        array(
            'file' => 'jquery-2.2.4.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'jquery-2.2.4.min.js',
        )
    ),
    'cookie' => array(
        'name' => 'Cookie',
        'description' => 'A simple, lightweight JavaScript API for handling browser cookies',
        'type' => 'asset',
        'url' => 'https://github.com/js-cookie/js-cookie',
        'download' => 'https://github.com/js-cookie/js-cookie/archive/v2.1.3.zip',
        'version_source' => array(
            'file' => 'src/js.cookie.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'src/js.cookie.js',
        )
    ),
    'jquery_file_upload' => array(
        'name' => 'jQuery File Upload',
        'description' => 'File Upload widget with multiple file selection, drag&drop support, progress bar, validation and preview images, audio and video for jQuery',
        'type' => 'asset',
        'url' => 'https://github.com/blueimp/jQuery-File-Upload',
        'download' => 'https://github.com/blueimp/jQuery-File-Upload/archive/v9.14.2.zip',
        'version_source' => array(
            'file' => 'bower.json',
        ),
        'files' => array(
            'js/vendor/jquery.ui.widget.js',
            'js/jquery.iframe-transport.js',
            'js/jquery.fileupload.js',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.7.0',
        )
    ),
    'jquery_match_height' => array(
        'name' => 'jQuery Match Height',
        'description' => 'A responsive equal heights plugin for jQuery',
        'type' => 'asset',
        'url' => 'https://github.com/liabru/jquery-match-height',
        'download' => 'https://github.com/liabru/jquery-match-height/archive/0.7.0.zip',
        'version_source' => array(
            'file' => 'dist/jquery.matchHeight-min.js',
            'pattern' => '/(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/jquery.matchHeight-min.js',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.7.0',
        )
    ),
    'jquery_ui' => array(
        'name' => 'jQuery UI',
        'description' => 'jQuery user interface library',
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
    ),
    'font_awesome' => array(
        'name' => 'Font Awesome',
        'description' => 'The iconic font and CSS toolkit',
        'type' => 'asset',
        'url' => 'https://github.com/FortAwesome/Font-Awesome',
        'download' => 'https://github.com/FortAwesome/Font-Awesome/archive/v4.7.0.zip',
        'version_source' => array(
            'file' => 'css/font-awesome.min.css',
            'pattern' => '/(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'css/font-awesome.min.css',
        )
    ),
    'html5shiv' => array(
        'name' => 'HTML5 Shiv',
        'description' => 'The HTML5 Shiv enables use of HTML5 sectioning elements in legacy Internet Explorer and provides basic HTML5 styling for Internet Explorer 6-9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.',
        'type' => 'asset',
        'url' => 'https://github.com/aFarkas/html5shiv',
        'download' => 'https://github.com/aFarkas/html5shiv/archive/3.7.3.zip',
        'version_source' => array(
            'file' => 'dist/html5shiv.min.js',
            'pattern' => '/(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/html5shiv.min.js',
        )
    ),
    'respond' => array(
        'name' => 'Respond',
        'description' => 'A fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more)',
        'type' => 'asset',
        'url' => 'https://github.com/scottjehl/Respond',
        'download' => 'https://github.com/scottjehl/Respond/archive/1.4.2.zip',
        'version_source' => array(
            'file' => 'dest/respond.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dest/respond.min.js',
        )
    ),
    'bootstrap' => array(
        'name' => 'Bootstrap',
        'description' => 'HTML, CSS, and JavaScript framework for developing responsive, mobile first layouts',
        'type' => 'asset',
        'url' => 'https://github.com/twbs/bootstrap',
        'download' => 'https://github.com/twbs/bootstrap/archive/v3.3.7.zip',
        'version_source' => array(
            'file' => 'dist/css/bootstrap.min.css',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/js/bootstrap.min.js',
            'dist/css/bootstrap.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.9.1',
        )
    ),
    'bootstrap_select' => array(
        'name' => 'Bootstrap Select',
        'description' => 'A jQuery plugin that utilizes Bootstrap\'s dropdown.js to style and bring additional functionality to standard select elements',
        'type' => 'asset',
        'url' => 'https://github.com/silviomoreto/bootstrap-select',
        'download' => 'https://github.com/silviomoreto/bootstrap-select/archive/v1.12.1.zip',
        'version_source' => array(
            'file' => 'dist/js/bootstrap-select.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/js/bootstrap-select.min.js',
            'dist/css/bootstrap-select.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.8',
            'bootstrap' => '>= 3.0',
        ),
    )
);
