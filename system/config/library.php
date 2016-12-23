<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */
$assets = GC_ASSET_DIR;
$libs = GC_LIBRARY_DIR;

$libraries = array();

$libraries[] = array(
    'id' => 'jquery',
    'name' => 'Jquery',
    'description' => 'jQuery JavaScript Library',
    'type' => 'js',
    'url' => 'https://github.com/jquery/jquery',
    'download' => 'https://github.com/jquery/jquery/archive/1.11.3.zip',
    'version' => '1.11.3',
    'basepath' => "$assets/jquery",
    'files' => array(
        "dist/jquery.min.js"
    )
);

$libraries[] = array(
    'id' => 'cookie',
    'name' => 'Cookie',
    'description' => 'A simple, lightweight JavaScript API for handling browser cookies',
    'type' => 'js',
    'url' => 'https://github.com/js-cookie/js-cookie',
    'download' => 'https://github.com/js-cookie/js-cookie/archive/v2.1.3.zip',
    'version' => '2.1.3',
    'basepath' => "$assets/js-cookie",
    'files' => array(
        "src/js.cookie.js"
    )
);

$libraries[] = array(
    'id' => 'jquery-file-upload',
    'name' => 'jQuery File Upload',
    'description' => 'File Upload widget with multiple file selection, drag&drop support, progress bar, validation and preview images, audio and video for jQuery',
    'type' => 'js',
    'url' => 'https://github.com/blueimp/jQuery-File-Upload',
    'download' => 'https://github.com/blueimp/jQuery-File-Upload/archive/v9.14.2.zip',
    'version' => '9.14.2',
    'basepath' => "$assets/jquery-file-upload",
    'files' => array(
        "js/vendor/jquery.ui.widget.js",
        "js/jquery.iframe-transport.js",
        "js/jquery.fileupload.js",
    ),
    'dependencies' => array(
        'jquery' => '>= 1.7.0'
    )
);

$libraries[] = array(
    'id' => 'lightgallery',
    'name' => 'JQuery lightGallery',
    'description' => 'A customizable, modular, responsive, lightbox gallery plugin',
    'type' => 'js',
    'url' => 'https://github.com/sachinchoolur/lightGallery',
    'download' => 'https://github.com/sachinchoolur/lightGallery/archive/1.3.7.zip',
    'version' => '1.3.7',
    'basepath' => "$assets/lightgallery",
    'files' => array(
        'js' => array(
            "dist/js/lightgallery.min.js"
        ),
        'css' => array(
            "dist/css/lightgallery.min.css"
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.8'
    )
);

$libraries[] = array(
    'id' => 'lightslider',
    'name' => 'JQuery lightSlider',
    'description' => 'A lightweight responsive content slider with carousel thumbnails navigation',
    'type' => 'js',
    'url' => 'https://github.com/sachinchoolur/lightslider',
    'download' => 'https://github.com/sachinchoolur/lightslider/archive/1.1.6.zip',
    'version' => '1.1.6',
    'basepath' => "$assets/lightslider",
    'files' => array(
        'js' => array(
            "dist/js/lightslider.min.js"
        ),
        'css' => array(
            "dist/css/lightslider.min.css"
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.8'
    )
);

$libraries[] = array(
    'id' => 'jquery-match-height',
    'name' => 'jQuery Match Height',
    'description' => 'A responsive equal heights plugin for jQuery',
    'type' => 'js',
    'url' => 'https://github.com/liabru/jquery-match-height',
    'download' => 'https://github.com/liabru/jquery-match-height/archive/0.7.0.zip',
    'version' => '0.7.0',
    'basepath' => "$assets/jquery-match-height",
    'files' => array(
        "dist/jquery.matchHeight-min.js"
    ),
    'dependencies' => array(
        'jquery' => '>= 1.7.0'
    )
);

$libraries[] = array(
    'id' => 'primeui',
    'name' => 'Prime UI',
    'description' => 'Rich set of javascript-css only widgets',
    'type' => 'js',
    'url' => 'https://github.com/primefaces/primeui',
    'download' => 'https://github.com/primefaces/primeui/archive/v4.1.15.zip',
    'version' => '4.1.15',
    'basepath' => "$assets/primeui",
    'files' => array(
        'css' => array(
            "components/core/core.css",
            "components/growl/growl.css",
            "components/terminal/terminal.css",
        ),
        'js' => array(
            "components/core/core.js",
            "components/growl/growl.js",
            "components/terminal/terminal.js",
        )
    ),
    'dependencies' => array(
        'jquery-ui' => '>= 1.12'
    )
);

$libraries[] = array(
    'id' => 'summernote',
    'name' => 'Summernote',
    'description' => 'Super simple WYSIWYG editor',
    'type' => 'js',
    'url' => 'https://github.com/summernote/summernote',
    'download' => 'https://github.com/summernote/summernote/archive/v0.8.2.zip',
    'version' => '0.8.2',
    'basepath' => "$assets/summernote",
    'files' => array(
        'css' => array(
            'dist/summernote.css',
        ),
        'js' => array(
            'dist/summernote.min.js',
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.9.0',
        'bootstrap' => '>= 3.0.1',
    )
);

$libraries[] = array(
    'id' => 'jquery-ui',
    'name' => 'jQuery UI',
    'description' => 'jQuery user interface library',
    'type' => 'js',
    'url' => 'https://jqueryui.com',
    'download' => 'https://jqueryui.com/resources/download/jquery-ui-1.12.1.zip',
    'version' => '1.12.1',
    'basepath' => "$assets/jquery-ui",
    'files' => array(
        'css' => array(
            'jquery-ui.min.css',
        ),
        'js' => array(
            'jquery-ui.min.js',
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.7.0',
    )
);

$libraries[] = array(
    'id' => 'chart',
    'name' => 'Chart.js',
    'description' => 'Simple HTML5 Charts using the <canvas> tag',
    'type' => 'js',
    'url' => 'https://github.com/chartjs/Chart.js',
    'download' => 'https://github.com/chartjs/Chart.js/releases/download/v2.2.1/Chart.min.js',
    'version' => '2.2.1',
    'basepath' => "$assets/chart",
    'files' => array(
        'Chart.min.js'
    )
);

$libraries[] = array(
    'id' => 'codemirror',
    'name' => 'CodeMirror',
    'description' => 'In-browser code editor',
    'type' => 'js',
    'url' => 'https://codemirror.net',
    'download' => 'http://codemirror.net/codemirror.zip',
    'version' => '5.22.1',
    'basepath' => "$assets/codemirror",
    'files' => array(
        'css' => array(
            'lib/codemirror.css',
        ),
        'js' => array(
            'lib/codemirror.js',
            'mode/css/css.js',
            'mode/javascript/javascript.js',
            'mode/twig/twig.js',
            'mode/xml/xml.js',
            'mode/htmlmixed/htmlmixed.js'
        )
    )
);

$libraries[] = array(
    'id' => 'font-awesome',
    'name' => 'Font Awesome',
    'description' => 'The iconic font and CSS toolkit',
    'type' => 'css',
    'url' => 'https://github.com/FortAwesome/Font-Awesome',
    'download' => 'https://github.com/FortAwesome/Font-Awesome/archive/v4.7.0.zip',
    'version' => '4.7.0',
    'basepath' => "$assets/font-awesome",
    'files' => array(
        'css/font-awesome.min.css'
    )
);

$libraries[] = array(
    'id' => 'html5shiv',
    'name' => 'HTML5 Shiv',
    'description' => 'The HTML5 Shiv enables use of HTML5 sectioning elements in legacy Internet Explorer and provides basic HTML5 styling for Internet Explorer 6-9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.',
    'type' => 'js',
    'url' => 'https://github.com/aFarkas/html5shiv',
    'download' => 'https://github.com/aFarkas/html5shiv/archive/3.7.3.zip',
    'version' => '3.7.3',
    'basepath' => "$assets/html5shiv",
    'files' => array(
        'dist/html5shiv.min.js'
    )
);

$libraries[] = array(
    'id' => 'respond',
    'name' => 'Respond',
    'description' => 'A fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more)',
    'type' => 'js',
    'url' => 'https://github.com/scottjehl/Respond',
    'download' => 'https://github.com/scottjehl/Respond/archive/1.4.2.zip',
    'version' => '1.4.2',
    'basepath' => "$assets/respond",
    'files' => array(
        'dest/respond.min.js'
    )
);

$libraries[] = array(
    'id' => 'bootstrap',
    'name' => 'Bootstrap',
    'description' => 'The most popular HTML, CSS, and JavaScript framework for developing responsive, mobile first projects on the web',
    'type' => 'css',
    'url' => 'https://github.com/twbs/bootstrap',
    'download' => 'https://github.com/twbs/bootstrap/archive/v3.3.7.zip',
    'version' => '3.3.7',
    'basepath' => "$assets/bootstrap",
    'files' => array(
        'css' => array(
            'dist/css/bootstrap.min.css',
        ),
        'js' => array(
            'dist/js/bootstrap.min.js',
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.9.1',
    )
);

$libraries[] = array(
    'id' => 'bootstrap-colorpicker',
    'name' => 'Bootstrap Colorpicker 2',
    'description' => 'Simple and customizable colorpicker component for Twitter Bootstrap',
    'type' => 'js',
    'url' => 'https://github.com/itsjavi/bootstrap-colorpicker',
    'download' => 'https://github.com/itsjavi/bootstrap-colorpicker/archive/2.3.5.zip',
    'version' => '2.3.5',
    'basepath' => "$assets/bootstrap-colorpicker",
    'files' => array(
        'css' => array(
            'dist/css/bootstrap-colorpicker.min.css',
        ),
        'js' => array(
            'dist/js/bootstrap-colorpicker.min.js',
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.1',
        'bootstrap' => '>= 3.0'
    )
);

$libraries[] = array(
    'id' => 'bootstrap-select',
    'name' => 'Bootstrap Select',
    'description' => 'A jQuery plugin that utilizes Bootstrap\'s dropdown.js to style and bring additional functionality to standard select elements',
    'type' => 'js',
    'url' => 'https://github.com/silviomoreto/bootstrap-select',
    'download' => 'https://github.com/silviomoreto/bootstrap-select/archive/v1.12.1.zip',
    'version' => '1.12.1',
    'basepath' => "$assets/bootstrap-select",
    'files' => array(
        'css' => array(
            'dist/css/bootstrap-select.min.css',
        ),
        'js' => array(
            'dist/js/bootstrap-select.min.js',
        )
    ),
    'dependencies' => array(
        'jquery' => '>= 1.8',
        'bootstrap' => '>= 3.0'
    )
);

// PHP libraries

$libraries[] = array(
    'id' => 'google-api',
    'name' => 'Google API Client Library',
    'description' => 'A PHP client library for accessing Google APIs',
    'type' => 'php',
    'url' => 'https://github.com/google/google-api-php-client',
    'download' => 'https://github.com/google/google-api-php-client/archive/v2.1.0.zip',
    'version' => '2.1.0',
    'basepath' => "$libs/google-api-php-client",
    'files' => array(
        'src/Google/autoload.php'
    )
);

$libraries[] = array(
    'id' => 'htmlpurifier',
    'name' => 'HTML Purifier',
    'description' => 'Standards compliant HTML filter written in PHP',
    'type' => 'php',
    'url' => 'https://github.com/ezyang/htmlpurifier',
    'download' => 'https://github.com/ezyang/htmlpurifier/archive/v4.8.0.zip',
    'version' => '4.8.0',
    'basepath' => "$libs/htmlpurifier",
    'files' => array(
        'library/HTMLPurifier.auto.php'
    )
);

$libraries[] = array(
    'id' => 'kint',
    'name' => 'Kint',
    'description' => 'A powerful and modern PHP debugging tool',
    'type' => 'php',
    'url' => 'https://github.com/raveren/kint',
    'download' => 'https://github.com/raveren/kint/archive/1.0.10.zip',
    'version' => '1.0.1',
    'basepath' => "$libs/kint",
    'files' => array(
        'Kint.class.php'
    )
);

$libraries[] = array(
    'id' => 'mobile-detect',
    'name' => 'Mobile Detect',
    'description' => 'A lightweight PHP class for detecting mobile devices (including tablets)',
    'type' => 'php',
    'url' => 'https://github.com/serbanghita/Mobile-Detect',
    'download' => 'https://github.com/serbanghita/Mobile-Detect/archive/2.8.24.zip',
    'version' => '2.8.24',
    'basepath' => "$libs/mobile-detect",
    'files' => array(
        'Mobile_Detect.php'
    )
);

$libraries[] = array(
    'id' => 'phpmailer',
    'name' => 'PHPMailer',
    'description' => 'The classic email sending library for PHP',
    'type' => 'php',
    'url' => 'https://github.com/PHPMailer/PHPMailer',
    'download' => 'https://github.com/PHPMailer/PHPMailer/archive/v5.2.17.zip',
    'version' => '5.2.17',
    'basepath' => "$libs/phpmailer",
    'files' => array(
        'PHPMailerAutoload.php'
    )
);

$libraries[] = array(
    'id' => 'simpleimage',
    'name' => 'SimpleImage',
    'description' => 'A PHP class that simplifies working with images',
    'type' => 'php',
    'url' => 'https://github.com/claviska/SimpleImage',
    'download' => 'https://github.com/claviska/SimpleImage/archive/2.6.0.zip',
    'version' => '2.6.0',
    'basepath' => "$libs/simpleimage",
    'files' => array(
        'src/abeautifulsite/SimpleImage.php'
    )
);

$libraries[] = array(
    'id' => 'transliterator',
    'name' => 'Transliterator',
    'description' => 'Provides one-way string transliteration (romanization)',
    'type' => 'php',
    'url' => 'https://github.com/gplcart/transliterator',
    'download' => 'https://github.com/gplcart/transliterator/archive/1.0.0.zip',
    'version' => '1.0.0',
    'basepath' => "$libs/transliterator",
    'files' => array(
        'Transliterator.php'
    )
);

$libraries[] = array(
    'id' => 'twig',
    'name' => 'Twig',
    'description' => 'Twig is a template engine for PHP',
    'type' => 'php',
    'url' => 'https://github.com/twigphp/Twig',
    'download' => 'https://github.com/twigphp/Twig/archive/v1.30.0.zip',
    'version' => '1.30.0',
    'basepath' => "$libs/twig",
    'files' => array(
        'lib/Twig/Autoloader.php'
    )
);

return $libraries;
