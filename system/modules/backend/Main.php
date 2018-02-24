<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backend;

use gplcart\core\Controller;

/**
 * Main backend theme class
 */
class Main
{

    /**
     * Implements hook "theme"
     * @param \gplcart\core\Controller $controller
     */
    public function hookTheme(Controller $controller)
    {
        if ($controller->isCurrentTheme('backend') && !$controller->isInternalRoute()) {
            $this->setAssets($controller);
            $this->setMetaTags($controller);
        }
    }

    /**
     * Adds theme specific assets
     * @param Controller $controller
     */
    protected function setAssets(Controller $controller)
    {
        $controller->addAssetLibrary('jquery_ui');
        $controller->addAssetLibrary('bootstrap');
        $controller->addAssetLibrary('html5shiv', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('respond', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('font_awesome');

        $controller->setJs(__DIR__ . '/js/common.js');
        $controller->setCss(__DIR__ . '/css/common.css');
    }

    /**
     * Adds meta tags
     * @param Controller $controller
     */
    protected function setMetaTags(Controller $controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $controller->setMeta(array('name' => 'author', 'content' => 'GPLCart'));
    }

}
