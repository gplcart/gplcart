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
        $libraries['popper'] = array(
            'name' => 'Popper', // @text
            'description' => 'A kickass library to manage your poppers', // @text
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/FezVrasta/popper.js',
            'download' => 'https://github.com/FezVrasta/popper.js/archive/v1.14.3.zip',
            'version' => '1.14.3',
            'files' => array(
                'dist/umd/popper.min.js',
            )
        );

        $libraries['bootstrap'] = array(
            'name' => 'Bootstrap', // @text
            'description' => 'HTML, CSS, and JavaScript framework for developing responsive, mobile first layouts', // @text
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/twbs/bootstrap',
            'download' => 'https://github.com/twbs/bootstrap/archive/v4.1.3.zip',
            'version' => '4.1.3',
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
