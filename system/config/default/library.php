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
    'jquery_mobile' => array(
        'name' => 'Jquery Mobile',
        'description' => 'A Touch-Optimized Web Framework',
        'type' => 'asset',
        'url' => 'https://jquerymobile.com',
        'download' => 'http://jquerymobile.com/resources/download/jquery.mobile-1.4.5.zip',
        'version_source' => array(
            'file' => 'jquery.mobile-1.4.5.min.js',
            'pattern' => '/jQuery Mobile (\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'jquery.mobile-1.4.5.min.js',
            'jquery.mobile-1.4.5.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.8.0',
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
    'lightgallery' => array(
        'name' => 'JQuery lightGallery',
        'description' => 'A customizable, modular, responsive, lightbox gallery plugin',
        'type' => 'asset',
        'url' => 'https://github.com/sachinchoolur/lightGallery',
        'download' => 'https://github.com/sachinchoolur/lightGallery/archive/1.3.8.zip',
        'version_source' => array(
            'file' => 'dist/js/lightgallery.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/js/lightgallery.min.js',
            'dist/css/lightgallery.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.8',
        )
    ),
    'lightgallery_thumbnail' => array(
        'name' => 'LightGallery thumbnail',
        'description' => 'Thumbnail module for lightGallery',
        'type' => 'asset',
        'url' => 'https://github.com/sachinchoolur/lg-thumbnail',
        'download' => 'https://github.com/sachinchoolur/lg-thumbnail/archive/1.0.2.zip',
        'version_source' => array(
            'file' => 'dist/lg-thumbnail.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/lg-thumbnail.min.js',
        ),
        'dependencies' => array(
            'lightgallery' => '>= 1.3.0',
        )
    ),
    'lightslider' => array(
        'name' => 'JQuery lightSlider',
        'description' => 'A lightweight responsive content slider with carousel thumbnails navigation',
        'type' => 'asset',
        'url' => 'https://github.com/sachinchoolur/lightslider',
        'download' => 'https://github.com/sachinchoolur/lightslider/archive/1.1.6.zip',
        'version_source' => array(
            'file' => 'dist/js/lightslider.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/js/lightslider.min.js',
            'dist/css/lightslider.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.8',
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
    'primeui' => array(
        'name' => 'Prime UI',
        'description' => 'Rich set of javascript-css only widgets',
        'type' => 'asset',
        'url' => 'https://github.com/primefaces/primeui',
        'download' => 'https://github.com/primefaces/primeui/archive/v4.1.15.zip',
        'version_source' => array(
            'file' => 'package.json',
        ),
        'files' => array(
            'components/core/core.js',
            'components/growl/growl.js',
            'components/terminal/terminal.js',
            'components/core/core.css',
            'components/growl/growl.css',
            'components/terminal/terminal.css',
        ),
        'dependencies' => array(
            'jquery_ui' => '>= 1.12',
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
    'bootstrap_colorpicker' => array(
        'name' => 'Bootstrap Colorpicker 2',
        'description' => 'Simple and customizable colorpicker component for Twitter Bootstrap',
        'type' => 'asset',
        'url' => 'https://github.com/itsjavi/bootstrap-colorpicker',
        'download' => 'https://github.com/itsjavi/bootstrap-colorpicker/archive/2.3.5.zip',
        'version_source' => array(
            'file' => 'dist/js/bootstrap-colorpicker.min.js',
            'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
        ),
        'files' => array(
            'dist/js/bootstrap-colorpicker.min.js',
            'dist/css/bootstrap-colorpicker.min.css',
        ),
        'dependencies' => array(
            'jquery' => '>= 1.1',
            'bootstrap' => '>= 3.0',
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