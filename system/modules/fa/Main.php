<?php

/**
 * @package Font Awesome
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\fa;

/**
 * Main class for Font Awesome module
 */
class Main
{

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['font_awesome'] = array(
            'name' => 'Font Awesome', // @text
            'description' => 'The iconic font and CSS toolkit', // @text
            'type' => 'asset',
            'module' => 'fa',
            'url' => 'https://github.com/FortAwesome/Font-Awesome',
            'download' => 'https://github.com/FortAwesome/Font-Awesome/archive/v4.7.0.zip',
            'version' => '4.7.0',
            'files' => array(
                'css/font-awesome.min.css',
            )
        );
    }

}
