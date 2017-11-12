<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
return array(
    'jquery' => array(
        'name' => /* @text */'Jquery',
        'description' => /* @text */'jQuery JavaScript Library',
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
        'name' => /* @text */'Cookie',
        'description' => /* @text */'A simple, lightweight JavaScript API for handling browser cookies',
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
    'jquery_match_height' => array(
        'name' => /* @text */'jQuery Match Height',
        'description' => /* @text */'A responsive equal heights plugin for jQuery',
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
        'name' => /* @text */'jQuery UI',
        'description' => /* @text */'jQuery user interface library',
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
        'name' => /* @text */'Font Awesome',
        'description' => /* @text */'The iconic font and CSS toolkit',
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
        'name' => /* @text */'HTML5 Shiv',
        'description' => /* @text */'The HTML5 Shiv enables use of HTML5 sectioning elements in legacy Internet Explorer and provides basic HTML5 styling for Internet Explorer 6-9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.',
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
        'name' => /* @text */'Respond',
        'description' => /* @text */'A fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more)',
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
        'name' => /* @text */'Bootstrap',
        'description' => /* @text */'HTML, CSS, and JavaScript framework for developing responsive, mobile first layouts',
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
    )
);
