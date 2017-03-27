<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\helpers;

use gplcart\core\Library;

/**
 * Wrapper class for TWIG template engine
 */
class Twig
{

    /**
     * Library class instance
     * @var object
     */
    protected $library;

    /**
     * Twig loader instance
     * @var object
     */
    protected $loader;

    /**
     * Twig environment instance
     * @var object
     */
    protected $twig;

    /**
     * Controller object
     * @var \gplcart\core\Controller $controller
     */
    protected $controller;

    /**
     * Constructor
     * @param Library $library
     */
    public function __construct(Library $library)
    {
        $this->library = $library;
        $this->library->load('twig');
        \Twig_Autoloader::register();
    }

    /**
     * Sets up Twig
     * @param string $path
     * @param \gplcart\core\Controller $object
     * @param array $options
     */
    public function set($path, $object, array $options = array())
    {
        if (!$object instanceof \gplcart\core\Controller) {
            throw new \RuntimeException("Object is not instance of \gplcart\core\Controller");
        }

        $this->controller = $object;

        if (!empty($options['cache'])) {
            $theme = $this->controller->prop('current_theme');
            $options['cache'] = GC_MODULE_DIR . "/{$theme['id']}/{$options['cache']}";
        }

        $this->loader = new \Twig_Loader_Filesystem($path);
        $this->twig = new \Twig_Environment($this->loader, $options);

        if (!empty($options['debug'])) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }

        foreach ($this->getDefaultFunctions() as $function) {
            $this->twig->addFunction($function);
        }
    }

    /**
     * Returns controller object
     * @return \gplcart\core\Controller
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * Renders a .twig template
     * @param string $file
     * @param array $data
     * @return string
     */
    public function render($file, array $data)
    {
        if (empty($this->twig)) {
            return "Failed to render twig template $file";
        }

        $template = $this->twig->loadTemplate($file);
        return $template->render($data);
    }

    /**
     * Validates Twig syntax for a given template
     * @param string $content
     * @param string $file
     * @param \gplcart\core\Controller $object
     * @return boolean
     */
    public function validate($content, $file, $object)
    {
        $info = pathinfo($file);
        $this->set($info['dirname'], $object);

        try {
            $this->twig->parse($this->twig->tokenize(new \Twig_Source($content, $info['basename'])));
            return true;
        } catch (\Twig_Error_Syntax $e) {
            return $e->getMessage();
        }
    }

    /**
     * Returns an array of TWIG simple function objects
     * @return array
     */
    protected function getDefaultFunctions()
    {
        $functions = array();

        $functions[] = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') {
            return $this->controller->error($key, $has_error, $no_error);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('text', function ($text, $arguments = array()) {
            return $this->controller->text($text, $arguments);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('access', function ($permission) {
            return $this->controller->access($permission);
        });

        $functions[] = new \Twig_SimpleFunction('url', function ($path = '', array $query = array(), $absolute = false) {
            return $this->controller->url($path, $query, $absolute);
        });

        $functions[] = new \Twig_SimpleFunction('isSuperadmin', function ($user_id = null) {
            return $this->controller->isSuperadmin($user_id);
        });

        $functions[] = new \Twig_SimpleFunction('date', function ($timestamp = null, $full = true) {
            return $this->controller->date($timestamp, $full);
        });

        $functions[] = new \Twig_SimpleFunction('attributes', function ($attributes) {
            return $this->controller->attributes($attributes);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('config', function ($key = null, $default = null) {
            return $this->controller->config($key, $default);
        });

        $functions[] = new \Twig_SimpleFunction('settings', function ($key = null, $default = null) {
            return $this->controller->settings($key, $default);
        });

        $functions[] = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $filter = null) {
            return $this->controller->summary($text, $xss, $filter);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('user', function ($item = null) {
            return $this->controller->user($item);
        });

        $functions[] = new \Twig_SimpleFunction('store', function ($item = null) {
            return $this->controller->store($item);
        });

        $functions[] = new \Twig_SimpleFunction('d', function ($key = null) {
            d($this->controller->getData($key));
        });

        $functions[] = new \Twig_SimpleFunction('xss', function ($text, $filter = null) {
            return $this->controller->xss($text, $filter);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('truncate', function ($string, $length = 100, $trimmarker = '...') {
            return $this->controller->truncate($string, $length, $trimmarker);
        });

        $functions[] = new \Twig_SimpleFunction('cart', function ($key = null) {
            return $this->controller->cart($key);
        });

        $functions[] = new \Twig_SimpleFunction('compare', function ($key = null) {
            return $this->controller->compare($key);
        });

        $functions[] = new \Twig_SimpleFunction('wishlist', function ($key = null) {
            return $this->controller->wishlist($key);
        });

        $functions[] = new \Twig_SimpleFunction('menu', function (array $options = array()) {
            return $this->controller->menu($options);
        }, array('is_safe' => array('all')));

        $functions[] = new \Twig_SimpleFunction('prop', function ($name) {
            return $this->controller->prop($name);
        });

        $functions[] = new \Twig_SimpleFunction('path', function ($path = null) {
            return $this->controller->path($path);
        });

        return $functions;
    }

}
