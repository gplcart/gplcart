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
class Frontend
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
            'core' => '1.0',
            'type' => 'theme',
            'configure' => 'admin/module/settings/frontend',
            'settings' => $this->getDefaultSettings()
        );
    }

    /**
     * Adds a new route for settings page
     * @param array $routes
     */
    public function hookRoute(&$routes)
    {
        $routes['admin/module/settings/frontend'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\frontend\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook init.theme
     * @param \gplcart\core\controllers\frontend\Controller $controller
     */
    public function hookTheme($controller)
    {
        if (!$controller->isCurrentTheme('frontend')) {
            return null;
        }

        $libraries = array('bootstrap', 'font_awesome');

        if (!$controller->isInstalling()) {
            $controller->setJs('system/modules/frontend/js/script.js');
            $libraries = array_merge($libraries, array('jquery_match_height', 'lightgallery', 'lightslider', 'jquery_ui'));
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

    /**
     * Returns an array of default module settings
     * @return array
     */
    protected function getDefaultSettings()
    {
        return array(
            'catalog_limit' => 20,
            'catalog_front_sort' => 'price',
            'catalog_front_order' => 'asc',
            'catalog_sort' => 'price',
            'catalog_order' => 'asc',
            'catalog_view' => 'grid',
            'image_style_page' => 5,
            'image_style_category' => 3,
            'image_style_category_child' => 3,
            'image_style_product' => 5,
            'image_style_product_grid' => 3,
            'image_style_product_list' => 3,
            'image_style_cart' => 3,
            'image_style_option' => 1,
            'image_style_collection_banner' => 7,
            'twig' => array(
                'status' => true,
                'cache' => 'cache',
                'auto_reload' => true
            )
        );
    }

}
