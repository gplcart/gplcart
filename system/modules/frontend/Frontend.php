<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\frontend;

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
                'controller' => array('modules\\frontend\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook init.frontend
     * @param \core\controllers\Controller $controller
     */
    public function hookInitFrontend($controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));

        $controller->setJs('system/modules/frontend/js/script.js', 'top');
        $controller->setJs('files/assets/jquery/ui/jquery-ui.min.js', 'top');
        $controller->setJs('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');
        $controller->setJs('files/assets/jquery/match-height/dist/jquery.matchHeight-min.js', 'top');
        $controller->setJs('files/assets/jquery/lightslider/dist/js/lightslider.min.js', 'top');
        $controller->setJs('files/assets/jquery/lightgallery/dist/js/lightgallery-all.min.js', 'top');

        $controller->setCss('files/assets/bootstrap/bootstrap/css/bootstrap.min.css', 0);
        $controller->setCss('files/assets/font-awesome/css/font-awesome.min.css', 10);
        $controller->setCss('files/assets/jquery/ui/jquery-ui.min.css', 20);
        $controller->setCss('files/assets/jquery/lightslider/dist/css/lightslider.min.css', 30);
        $controller->setCss('files/assets/jquery/lightgallery/dist/css/lightgallery.min.css', 40);
        $controller->setCss('files/assets/jquery/ui/jquery-ui.min.css', 50);
        $controller->setCss('system/modules/frontend/css/style.css', 60);
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
            'image_style_category' => 3,
            'image_style_category_child' => 3,
            'image_style_product' => 5,
            'image_style_product_extra' => 3,
            'image_style_product_grid' => 3,
            'image_style_product_list' => 3,
            'image_style_cart' => 3,
            'image_style_option' => 1,
            'image_style_collection_banner' => 7,
            'image_style_page' => 5
        );
    }

}
