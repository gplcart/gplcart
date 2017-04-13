<?php

/**
 * @package Twig
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\modules\twig;

use gplcart\core\Module,
    gplcart\core\Library;

/**
 * Main class for Twig module
 */
class Twig extends Module
{

    /**
     * Library class instance
     * @var \gplcart\core\Library
     */
    protected $library;

    /**
     * An array of TWIG instances keyed by file directory
     * @var array
     */
    protected $twig = array();

    /**
     * An array of module settings
     * @var array
     */
    protected $settings = array();

    /**
     * @param Library $library
     */
    public function __construct(Library $library)
    {
        parent::__construct();

        $this->library = $library;
    }

    /**
     * Module info
     * @return array
     */
    public function info()
    {
        return array(
            'name' => 'Twig',
            'version' => GC_VERSION,
            'description' => 'A GPL Cart module that allows to render .twig templates',
            'author' => 'Iurii Makukh ',
            'core' => '1.x',
            'license' => 'GPL-3.0+',
            'configure' => 'admin/module/settings/twig',
            'settings' => array(
                'cache' => true,
                'debug' => false,
                'auto_reload' => false,
                'strict_variables' => false
            ),
        );
    }

    /**
     * Implements hook "library.list"
     * @param array $libraries
     */
    public function hookLibraryList(array &$libraries)
    {
        $libraries['twig'] = array(
            'name' => 'Twig',
            'description' => 'Twig is a template engine for PHP',
            'url' => 'https://github.com/twigphp/Twig',
            'download' => 'https://github.com/twigphp/Twig/archive/v1.33.0.zip',
            'type' => 'php',
            'module' => 'twig',
            'version_source' => array(
                'lines' => 100,
                'pattern' => '/.*VERSION.*(\\d+\\.+\\d+\\.+\\d+)/',
                'file' => 'vendor/twig/twig/lib/Twig/Environment.php'
            ),
            'files' => array(
                'vendor/autoload.php'
            )
        );
    }

    /**
     * Implements hook "route.list"
     * @param array $routes
     */
    public function hookRouteList(array &$routes)
    {
        // Module settings page
        $routes['admin/module/settings/twig'] = array(
            'access' => 'module_edit',
            'handlers' => array(
                'controller' => array('gplcart\\modules\\twig\\controllers\\Settings', 'editSettings')
            )
        );
    }

    /**
     * Implements hook "template.render"
     * @param string $template
     * @param array $data
     * @param null|string $rendered
     * @param \gplcart\core\Controller $object
     */
    public function hookTemplateRender($template, $data, &$rendered, $object)
    {
        $template .= '.twig';

        if (is_file($template)) {

            if (empty($this->twig)) {
                $this->library->load('twig');
                $this->settings = $this->config->module('twig');
            }

            $rendered = $this->render($template, $data, $object);
        }
    }

    /**
     * Returns a TWIG instance for the given file directory
     * @param string $path
     * @param \gplcart\core\Controller $object
     */
    public function getTwigInstance($path, $object)
    {
        if (isset($this->twig[$path])) {
            return $this->twig[$path];
        }

        $options = $this->settings;

        if (!empty($this->settings['cache'])) {
            $options['cache'] = __DIR__ . '/cache';
        }

        $twig = new \Twig_Environment(new \Twig_Loader_Filesystem($path), $options);

        if (!empty($options['debug'])) {
            $twig->addExtension(new \Twig_Extension_Debug());
        }

        foreach ($this->getDefaultFunctions($object) as $function) {
            $twig->addFunction($function);
        }

        return $this->twig[$path] = $twig;
    }

    /**
     * Renders a .twig template
     * @param string $template
     * @param array $data
     * @param \gplcart\core\Controller $object
     * @return string
     */
    public function render($template, $data, $object)
    {
        $parts = explode('/', $template);
        $file = array_pop($parts);

        $twig = $this->getTwigInstance(implode('/', $parts), $object);

        $controller_data = $object->getData();
        $merged = gplcart_array_merge($controller_data, $data);

        return $twig->loadTemplate($file)->render($merged);
    }

    /**
     * Adds custom functions and returns an array of Twig_SimpleFunction objects
     * @param \gplcart\core\Controller $object
     * @return array
     */
    protected function getDefaultFunctions($object)
    {
        $functions = array();

        $functions[] = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') use ($object) {
            return $object->error($key, $has_error, $no_error);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('text', function ($text, $arguments = array()) use ($object) {
            return $object->text($text, $arguments);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('access', function ($permission) use ($object) {
            return $object->access($permission);
        });

        $functions[] = new \Twig_SimpleFunction('url', function ($path = '', array $query = array(), $absolute = false) use ($object) {
            return $object->url($path, $query, $absolute);
        });

        $functions[] = new \Twig_SimpleFunction('isSuperadmin', function ($user_id = null) use ($object) {
            return $object->isSuperadmin($user_id);
        });

        $functions[] = new \Twig_SimpleFunction('date', function ($timestamp = null, $full = true, $unix_format = '') use ($object) {
            return $object->date($timestamp, $full, $unix_format);
        });

        $functions[] = new \Twig_SimpleFunction('attributes', function ($attributes) use ($object) {
            return $object->attributes($attributes);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('config', function ($key = null, $default = null) use ($object) {
            return $object->config($key, $default);
        });

        $functions[] = new \Twig_SimpleFunction('settings', function ($key = null, $default = null) use ($object) {
            return $object->settings($key, $default);
        });

        $functions[] = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $filter = null) use ($object) {
            return $object->summary($text, $xss, $filter);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('user', function ($item = null) use ($object) {
            return $object->user($item);
        });

        $functions[] = new \Twig_SimpleFunction('store', function ($item = null) use ($object) {
            return $object->store($item);
        });

        $functions[] = new \Twig_SimpleFunction('d', function ($key = null) use ($object) {
            d($object->getData($key));
        });

        $functions[] = new \Twig_SimpleFunction('xss', function ($text, $filter = null) use ($object) {
            return $object->xss($text, $filter);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('truncate', function ($string, $length = 100, $trimmarker = '...') use ($object) {
            return $object->truncate($string, $length, $trimmarker);
        });

        $functions[] = new \Twig_SimpleFunction('cart', function ($key = null) use ($object) {
            return $object->cart($key);
        });

        $functions[] = new \Twig_SimpleFunction('compare', function ($key = null) use ($object) {
            return $object->compare($key);
        });

        $functions[] = new \Twig_SimpleFunction('wishlist', function ($key = null) use ($object) {
            return $object->wishlist($key);
        });

        $functions[] = new \Twig_SimpleFunction('menu', function (array $options = array()) use ($object) {
            return $object->menu($options);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('prop', function ($name) use ($object) {
            return $object->prop($name);
        });

        $functions[] = new \Twig_SimpleFunction('path', function ($path = null) use ($object) {
            return $object->path($path);
        });

        return $functions;
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
