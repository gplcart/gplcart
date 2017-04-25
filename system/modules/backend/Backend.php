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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Implements hook "theme"
     * @param \gplcart\core\controllers\backend\Controller $controller
     */
    public function hookTheme($controller)
    {
        if ($controller->isCurrentTheme('backend')) {

            $controller->setJs('system/modules/backend/js/common.js');

            $libraries = array('font_awesome', 'jquery_ui', 'bootstrap_select');
            $controller->addAssetLibrary($libraries);

            $controller->setCss('system/modules/backend/css/style.css');

            $controller->setMeta(array('charset' => 'utf-8'));
            $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
            $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
            $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));
        }
    }

}
