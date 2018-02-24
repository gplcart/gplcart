<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend;

use gplcart\core\Controller;

/**
 * Main class for Frontend theme
 */
class Main
{

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        $routes['admin/module/settings/frontend'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\frontend\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook "theme"
     * @param Controller $controller
     */
    public function hookTheme(Controller $controller)
    {
        if ($controller->isCurrentTheme('frontend') && !$controller->isInternalRoute()) {
            $this->setAssets($controller);
            $this->setMetaTags($controller);
        }
    }

    /**
     * Sets theme specific assets
     * @param Controller $controller
     */
    protected function setAssets(Controller $controller)
    {
        $controller->addAssetLibrary('html5shiv', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('respond', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('font_awesome');
        $controller->addAssetLibrary('bootstrap');

        if ($controller->isInstall()) {
            $controller->setCss(__DIR__ . '/css/install.css');
        } else {
            $controller->setCss(__DIR__ . '/css/common.css');
            $controller->setJs(__DIR__ . '/js/common.js');
        }
    }

    /**
     * Sets meta tags
     * @param Controller $controller
     */
    protected function setMetaTags(Controller $controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
    }

}
