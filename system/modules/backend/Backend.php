<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace modules\backend;

use core\classes\Tool;

/**
 * Main backend theme class
 */
class Backend
{

    /**
     * Returns module info
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
     * @param \core\controllers\admin\Controller $controller
     */
    public function hookInitBackend($controller)
    {
        $this->addCss($controller);
        $this->addMeta($controller);
        $this->addJs($controller);
    }

    /**
     * Adds styles
     * @param \core\controllers\admin\Controller $controller
     */
    protected function addCss($controller)
    {
        $controller->setCss('files/assets/bootstrap/bootstrap/css/bootstrap.min.css');
        $controller->setCss('files/assets/font-awesome/css/font-awesome.min.css');
        $controller->setCss('files/assets/jquery/ui/jquery-ui.min.css');
        $controller->setCss('files/assets/jquery/summernote/summernote.css');
        $controller->setCss('files/assets/bootstrap/select/dist/css/bootstrap-select.min.css');
        $controller->setCss('files/assets/bootstrap/colorpicker/dist/css/bootstrap-colorpicker.min.css');
        
        $controller->setCss('files/assets/jquery/primeui/components/core/core.css');
        $controller->setCss('files/assets/jquery/primeui/components/growl/growl.css');
        
        $controller->setCss('system/modules/backend/css/style.css');
    }

    /**
     * Adds meta tags
     * @param \core\controllers\admin\Controller $controller
     */
    protected function addMeta($controller)
    {
        $controller->setMeta(array('charset' => 'utf-8'));
        $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
        $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
        $controller->setMeta(array('name' => 'author', 'content' => 'GPL Cart'));
    }

    /**
     * Adds scripts
     * @param \core\controllers\admin\Controller $controller
     */
    protected function addJs($controller)
    {
        $controller->setJs('system/modules/backend/js/common.js', 'top');
        $controller->setJs('files/assets/jquery/ui/jquery-ui.min.js', 'top');
        $controller->setJs('files/assets/bootstrap/bootstrap/js/bootstrap.min.js', 'top');
        
        $controller->setJs('files/assets/jquery/primeui/components/core/core.js', 'bottom');
        $controller->setJs('files/assets/jquery/primeui/components/growl/growl.js', 'bottom');

        $path = $controller->getData('path');
        $file = Tool::contexUrltFile(GC_MODULE_DIR . '/backend/js', 'js', $path);

        if (isset($file['filename'])) {
            $controller->setJs("system/modules/backend/js/{$file['filename']}.js", 'bottom');
        }

        $controller->setJs('files/assets/jquery/fileupload/jquery.fileupload.js', 'bottom');
        $controller->setJs('files/assets/bootstrap/select/dist/js/bootstrap-select.min.js', 'bottom');

        $lang_region = $controller->getData('lang_region');
        $controller->setJs('files/assets/jquery/summernote/summernote.min.js', 'bottom');

        if ($lang_region) {
            $controller->setJs("files/assets/jquery/summernote/lang/summernote-$lang_region.js", 'bottom');
        }

        $controller->setJs('files/assets/jquery/cookie/js.cookie.js', 'bottom');
        $controller->setJs('files/assets/bootstrap/colorpicker/dist/js/bootstrap-colorpicker.min.js', 'bottom');
        $controller->setJs('files/assets/jquery/countdown/countdown.js', 'bottom');
    }

}
