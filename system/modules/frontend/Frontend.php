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
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Frontend theme',
            'description' => 'Frontend theme',
            'author' => 'IURII MAKUKH',
            'core' => '1.x',
            'type' => 'theme',
            'configure' => 'admin/module/settings/frontend',
            'settings' => array(
                'catalog_limit' => 20,
                'catalog_front_sort' => 'price',
                'catalog_front_order' => 'asc',
                'catalog_sort' => 'price',
                'catalog_order' => 'asc',
                'catalog_view' => 'grid',
                'image_style_page' => 5,
                'image_style_category' => 3,
                'image_style_category_child' => 3,
                'image_style_product' => 6,
                'image_style_product_grid' => 3,
                'image_style_product_list' => 3,
                'image_style_cart' => 3,
                'image_style_option' => 1,
                'image_style_collection_banner' => 7
            )
        );
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(&$routes)
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

        $libraries = array('bootstrap', 'font_awesome');

        if (!$controller->isInstalling()) {
            $controller->setJs('system/modules/frontend/js/common.js');
            $libraries = array_merge($libraries, array('jquery_match_height', 'lightgallery', 'lightgallery_thumbnail', 'lightslider', 'jquery_ui'));
        }

        $controller->addAssetLibrary($libraries);

        $condition_libraries = array('html5shiv', 'respond');
        $controller->addAssetLibrary($condition_libraries, array('aggregate' => false, 'condition' => 'if lt IE 9'));

        if ($controller->isInstalling()) {
            $controller->setCss('system/modules/frontend/css/install.css');
        } else {
            $controller->setCss('system/modules/frontend/css/style.css');
        }

        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));
    }

}
