<?php

/**
 * @package Bootstrap
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\bootstrap;

/**
 * Main class for Bootstrap module
 */
class Main
{

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['html5shiv'] = array(
            'name' => 'HTML5 Shiv', // @text
            'description' => 'The HTML5 Shiv enables use of HTML5 sectioning elements in legacy Internet Explorer and provides basic HTML5 styling for Internet Explorer 6-9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.', // @text
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/aFarkas/html5shiv',
            'download' => 'https://github.com/aFarkas/html5shiv/archive/3.7.3.zip',
            'version' => '3.7.3',
            'files' => array(
                'dist/html5shiv.min.js',
            )
        );
        $libraries['respond'] = array(
            'name' => 'Respond', // @text
            'description' => 'A fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more)', // @text
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/scottjehl/Respond',
            'download' => 'https://github.com/scottjehl/Respond/archive/1.4.2.zip',
            'version' => '1.4.2',
            'files' => array(
                'dest/respond.min.js',
            )
        );

        $libraries['bootstrap'] = array(
            'name' => 'Bootstrap', // @text
            'description' => 'HTML, CSS, and JavaScript framework for developing responsive, mobile first layouts', // @text
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/twbs/bootstrap',
            'download' => 'https://github.com/twbs/bootstrap/archive/v3.3.7.zip',
            'version' => '3.3.7',
            'files' => array(
                'dist/js/bootstrap.min.js',
                'dist/css/bootstrap.min.css',
            ),
            'dependencies' => array(
                'jquery' => '>= 1.9.1',
            )
        );
    }
}
