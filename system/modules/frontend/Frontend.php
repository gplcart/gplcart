<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\frontend;

use gplcart\core\Module;

/**
 * Main class for Frontend theme
 */
class Frontend extends Module
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
        if ($controller->isCurrentTheme('frontend')) {
            $this->setThemeAssets($controller);
            $this->setThemeMetaTags($controller);
        }
    }

    /**
     * Implements hook "construct.controller.frontend"
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookConstructControllerFrontend($controller)
    {
        $this->setThemeRegions($controller);
    }

    /**
     * Sets all required assets
     * @param \gplcart\core\Controller $controller
     */
    protected function setThemeAssets($controller)
    {
        $controller->addAssetLibrary('bootstrap');
        $controller->addAssetLibrary('html5shiv', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('respond', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('font_awesome');

        if ($controller->isInstall()) {
            $controller->setCss($this->getAsset('frontend', 'install.css'));
        } else {
            $controller->setCss($this->getAsset('frontend', 'style.css'));
            $controller->addAssetLibrary('jquery_match_height');
            $controller->setJs($this->getAsset('frontend', 'common.js'));
        }
    }

    /**
     * Sets meta-tags
     * @param \gplcart\core\Controller $controller
     */
    protected function setThemeMetaTags($controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
    }

    /**
     * Set theme regions
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    protected function setThemeRegions($controller)
    {
        $pattern = $controller->getRoute('simple_pattern');

        if (in_array($pattern, array('wishlist', 'compare', 'category/*'))) {
            $categories = $controller->getCategories();
            if (!empty($categories)) {
                $options = array('template' => 'category/menu', 'items' => $categories);
                $controller->setRegion('left', $controller->renderMenu($options));
            }
        }
    }

}
