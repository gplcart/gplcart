<?php

/**
 * @package Bootstrap
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\bootstrap;

use gplcart\core\Module,
    gplcart\core\Config;

/**
 * Main class for Bootstrap module
 */
class Bootstrap extends Module
{

    /**
     * @param Config $config
     */
    public function __construct(Config $config)
    {
        parent::__construct($config);
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['html5shiv'] = array(
            'name' => /* @text */'HTML5 Shiv',
            'description' => /* @text */'The HTML5 Shiv enables use of HTML5 sectioning elements in legacy Internet Explorer and provides basic HTML5 styling for Internet Explorer 6-9, Safari 4.x (and iPhone 3.x), and Firefox 3.x.',
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/aFarkas/html5shiv',
            'download' => 'https://github.com/aFarkas/html5shiv/archive/3.7.3.zip',
            'version_source' => array(
                'file' => 'vendor/html5shiv/dist/html5shiv.min.js',
                'pattern' => '/(\\d+\\.+\\d+\\.+\\d+)/',
            ),
            'files' => array(
                'vendor/html5shiv/dist/html5shiv.min.js',
            )
        );
        $libraries['respond'] = array(
            'name' => /* @text */'Respond',
            'description' => /* @text */'A fast & lightweight polyfill for min/max-width CSS3 Media Queries (for IE 6-8, and more)',
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/scottjehl/Respond',
            'download' => 'https://github.com/scottjehl/Respond/archive/1.4.2.zip',
            'version_source' => array(
                'file' => 'vendor/respond/dest/respond.min.js',
                'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
            ),
            'files' => array(
                'vendor/respond/dest/respond.min.js',
            )
        );

        $libraries['bootstrap'] = array(
            'name' => /* @text */'Bootstrap',
            'description' => /* @text */'HTML, CSS, and JavaScript framework for developing responsive, mobile first layouts',
            'type' => 'asset',
            'module' => 'bootstrap',
            'url' => 'https://github.com/twbs/bootstrap',
            'download' => 'https://github.com/twbs/bootstrap/archive/v3.3.7.zip',
            'version_source' => array(
                'file' => 'vendor/bootstrap/dist/css/bootstrap.min.css',
                'pattern' => '/v(\\d+\\.+\\d+\\.+\\d+)/',
            ),
            'files' => array(
                'vendor/bootstrap/dist/js/bootstrap.min.js',
                'vendor/bootstrap/dist/css/bootstrap.min.css',
            ),
            'dependencies' => array(
                'jquery' => '>= 1.9.1',
            )
        );
    }

    /**
     * Implements hook "module.enable.after"
     */
    public function hookModuleEnableAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->getLibrary()->clearCache();
    }

}
