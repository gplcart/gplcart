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
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookTheme($controller)
    {
        if (!$controller->isCurrentTheme('frontend')) {
            return null;
        }

        $controller->addAssetLibrary('bootstrap');
        $controller->addAssetLibrary('html5shiv', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('respond', array('aggregate' => false, 'condition' => 'if lt IE 9'));
        $controller->addAssetLibrary('font_awesome');

        if ($controller->isInstallUrl()) {
            $controller->setCss($this->getAsset('frontend', 'css', 'install.css'));
        } else {
            $controller->setCss($this->getAsset('frontend', 'css', 'style.css'));
            $controller->addAssetLibrary('jquery_match_height');
            $controller->setJs($this->getAsset('frontend', 'js', 'common.js'));
        }

        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
    }

}
