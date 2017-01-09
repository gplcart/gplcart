<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backend;

/**
 * Main backend theme class
 */
class Backend
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
            'core' => '1.0',
            'type' => 'theme',
            'settings' => array()
        );
    }

    /**
     * Implements hook.backend
     * @param \gplcart\core\controllers\backend\Controller $controller
     */
    public function hookInitBackend($controller)
    {
        if(!$controller->isCurrentTheme('backend')){
            return null;
        }
        
        // Add system JS
        $controller->setJs('system/modules/backend/js/common.js');
        
        // Add asset libraries
        $libraries = array('bootstrap', 'font_awesome', 'summernote', 'primeui',
            'jquery_file_upload', 'bootstrap_select', 'bootstrap_colorpicker', 'codemirror', 'chart');
        
        $controller->addAssetLibrary($libraries);
        
        // Add theme JS depending on the current URL path
        $controller->setJsContext(GC_MODULE_DIR . '/backend/js', array('position' => 'bottom'));
        
        // Add theme-specific CSS. Goes after all added CSS
        $controller->setCss('system/modules/backend/css/style.css');
        
        // Set meta tags
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));
    }

}
