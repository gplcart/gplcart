<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backend;

use gplcart\core\Module;

/**
 * Main backend theme class
 */
class Backend extends Module
{

    /**
     * Returns the module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Backend theme',
            'description' => 'Backend theme',
            'author' => 'Iurii Makukh',
            'core' => '1.x',
            'type' => 'theme',
            'status' => true,
            'version' => GC_VERSION
        );
    }

    /**
     * Implements hook "theme"
     * @param \gplcart\core\controllers\backend\Controller $controller
     */
    public function hookTheme($controller)
    {
        if ($controller->isCurrentTheme('backend')) {

            $controller->setJs('system/modules/backend/js/common.js');

            $libraries = array('bootstrap', 'font_awesome', 'summernote', 'primeui',
                'jquery_file_upload', 'bootstrap_select', 'bootstrap_colorpicker',
                'codemirror');

            $controller->addAssetLibrary($libraries);
            $controller->setCss('system/modules/backend/css/style.css');

            $controller->setMeta(array('charset' => 'utf-8'));
            $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
            $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
            $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));
        }
    }

}
