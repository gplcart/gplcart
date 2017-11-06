<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\backend;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main backend theme class
 */
class Backend extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "theme"
     * @param \gplcart\core\controllers\backend\Controller $controller
     */
    public function hookTheme($controller)
    {
        if ($controller->isCurrentTheme('backend')) {

            $controller->addAssetLibrary('jquery_ui');
            $controller->addAssetLibrary('bootstrap');
            $controller->addAssetLibrary('bootstrap_select');
            $controller->addAssetLibrary('html5shiv', array('aggregate' => false, 'condition' => 'if lt IE 9'));
            $controller->addAssetLibrary('respond', array('aggregate' => false, 'condition' => 'if lt IE 9'));

            $controller->setJs($this->getAsset('backend', 'common.js'));
            $controller->addAssetLibrary('font_awesome');
            $controller->setCss($this->getAsset('backend', 'style.css'));

            $controller->setMeta(array('charset' => 'utf-8'));
            $controller->setMeta(array('http-equiv' => 'X-UA-Compatible', 'content' => 'IE=edge'));
            $controller->setMeta(array('name' => 'viewport', 'content' => 'width=device-width, initial-scale=1'));
            $controller->setMeta(array('name' => 'author', 'content' => 'GPLCart'));
        }
    }

}
