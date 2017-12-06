<?php

/**
 * @package Font Awesome
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2017, Iurii Makukh <gplcart.software@gmail.com>
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GPL-3.0+
 */

namespace gplcart\modules\fa;

use gplcart\core\Library;

/**
 * Main class for Font Awesome module
 */
class Fa
{

    /**
     * Library class instance
     * @var \gplcart\core\Library $library
     */
    protected $library;

    /**
     * @param Library $library
     */
    public function __construct(Library $library)
    {
        $this->library = $library;
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['font_awesome'] = array(
            'name' => /* @text */'Font Awesome',
            'description' => /* @text */'The iconic font and CSS toolkit',
            'type' => 'asset',
            'module' => 'fa',
            'url' => 'https://github.com/FortAwesome/Font-Awesome',
            'download' => 'https://github.com/FortAwesome/Font-Awesome/archive/v4.7.0.zip',
            'version_source' => array(
                'file' => 'vendor/font_awesome/css/font-awesome.min.css',
                'pattern' => '/(\\d+\\.+\\d+\\.+\\d+)/',
            ),
            'files' => array(
                'vendor/font_awesome/css/font-awesome.min.css',
            )
        );
    }

    /**
     * Implements hook "module.enable.after"
     */
    public function hookModuleEnableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.disable.after"
     */
    public function hookModuleDisableAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.install.after"
     */
    public function hookModuleInstallAfter()
    {
        $this->library->clearCache();
    }

    /**
     * Implements hook "module.uninstall.after"
     */
    public function hookModuleUninstallAfter()
    {
        $this->library->clearCache();
    }

}
