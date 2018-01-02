<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend;

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
     * @param \gplcart\core\Controller $controller
     */
    public function hookTheme($controller)
    {
        if ($controller->isCurrentTheme('frontend') && !$controller->isInternalRoute()) {
            $this->setThemeAssets($controller);
            $this->setThemeMetaTags($controller);
        }
    }

    /**
     * Sets theme specific assets
     * @param \gplcart\core\Controller $controller
     */
    protected function setThemeAssets($controller)
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
     * @param \gplcart\core\Controller $controller
     */
    protected function setThemeMetaTags($controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
    }

}
