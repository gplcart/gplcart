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

        $this->addFunctionD();
        $this->addFunctionXss();
        $this->addFunctionUrl();
        $this->addFunctionPath();
        $this->addFunctionProp();
        $this->addFunctionMenu();
        $this->addFunctionDate();
        $this->addFunctionCart();
        $this->addFunctionDate();
        $this->addFunctionText();
        $this->addFunctionUser();
        $this->addFunctionStore();
        $this->addFunctionError();
        $this->addFunctionConfig();
        $this->addFunctionAccess();
        $this->addFunctionSummary();
        $this->addFunctionCompare();
        $this->addFunctionTruncate();
        $this->addFunctionSettings();
        $this->addFunctionWishlist();
        $this->addFunctionAttributes();
        $this->addFunctionIsSuperadmin();
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
     * Adds function \gplcart\core\Controller::error()
     * @see \gplcart\core\Controller::error()
     */
    protected function addFunctionError()
    {
        $function = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') {
            return $this->controller->error($key, $has_error, $no_error);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::text()
     * @see \gplcart\core\Controller::text()
     */
    protected function addFunctionText()
    {
        $function = new \Twig_SimpleFunction('text', function ($text, $arguments = array()) {
            return $this->controller->text($text, $arguments);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::access()
     * @see \gplcart\core\Controller::access()
     */
    protected function addFunctionAccess()
    {
        $function = new \Twig_SimpleFunction('access', function ($permission) {
            return $this->controller->access($permission);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::url()
     * @see \gplcart\core\Controller::url()
     */
    protected function addFunctionUrl()
    {
        $function = new \Twig_SimpleFunction('url', function ($path = '', array $query = array(), $absolute = false) {
            return $this->controller->url($path, $query, $absolute);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::isSuperadmin()
     * @see \gplcart\core\Controller::isSuperadmin()
     */
    protected function addFunctionIsSuperadmin()
    {
        $function = new \Twig_SimpleFunction('isSuperadmin', function ($user_id = null) {
            return $this->controller->isSuperadmin($user_id);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::date()
     * @see \gplcart\core\Controller::date()
     */
    protected function addFunctionDate()
    {
        $function = new \Twig_SimpleFunction('date', function ($timestamp = null, $full = true) {
            return $this->controller->date($timestamp, $full);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::attributes()
     * @see \gplcart\core\Controller::attributes()
     */
    protected function addFunctionAttributes()
    {
        $function = new \Twig_SimpleFunction('attributes', function ($attributes) {
            return $this->controller->attributes($attributes);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::config()
     * @see \gplcart\core\Controller::config()
     */
    protected function addFunctionConfig()
    {
        $function = new \Twig_SimpleFunction('config', function ($key = null, $default = null) {
            return $this->controller->config($key, $default);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::settings()
     * @see \gplcart\core\Controller::settings()
     */
    protected function addFunctionSettings()
    {
        $function = new \Twig_SimpleFunction('settings', function ($key = null, $default = null) {
            return $this->controller->settings($key, $default);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::summary()
     * @see \gplcart\core\Controller::summary()
     */
    protected function addFunctionSummary()
    {
        $function = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $filter = null) {
            return $this->controller->summary($text, $xss, $filter);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::user()
     * @see \gplcart\core\Controller::user()
     */
    protected function addFunctionUser()
    {
        $function = new \Twig_SimpleFunction('user', function ($item = null) {
            return $this->controller->user($item);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::store()
     * @see \gplcart\core\Controller::store()
     */
    protected function addFunctionStore()
    {
        $function = new \Twig_SimpleFunction('store', function ($item = null) {
            return $this->controller->store($item);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds debug function to see template variables \gplcart\core\Controller::$data
     */
    protected function addFunctionD()
    {
        $function = new \Twig_SimpleFunction('d', function ($key = null) {
            d($this->controller->getData($key));
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::xss()
     * @see \gplcart\core\Controller::xss()
     */
    protected function addFunctionXss()
    {
        $function = new \Twig_SimpleFunction('xss', function ($text, $filter = null) {
            return $this->controller->xss($text, $filter);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::truncate()
     * @see \gplcart\core\Controller::truncate()
     */
    protected function addFunctionTruncate()
    {
        $function = new \Twig_SimpleFunction('truncate', function ($string, $length = 100, $trimmarker = '...') {
            return $this->controller->truncate($string, $length, $trimmarker);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::cart()
     * @see \gplcart\core\controllers\frontend\Controller::cart()
     */
    protected function addFunctionCart()
    {
        if ($this->controller instanceof \gplcart\core\controllers\frontend\Controller) {
            $function = new \Twig_SimpleFunction('cart', function ($key = null) {
                return $this->controller->cart($key);
            });
            $this->twig->addFunction($function);
        }
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::compare()
     * @see \gplcart\core\controllers\frontend\Controller::compare()
     */
    protected function addFunctionCompare()
    {
        if ($this->controller instanceof \gplcart\core\controllers\frontend\Controller) {
            $function = new \Twig_SimpleFunction('compare', function ($key = null) {
                return $this->controller->compare($key);
            });
            $this->twig->addFunction($function);
        }
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::wishlist()
     * @see \gplcart\core\controllers\frontend\Controller::wishlist()
     */
    protected function addFunctionWishlist()
    {
        if ($this->controller instanceof \gplcart\core\controllers\frontend\Controller) {
            $function = new \Twig_SimpleFunction('wishlist', function ($key = null) {
                return $this->controller->wishlist($key);
            });
            $this->twig->addFunction($function);
        }
    }

    /**
     * Adds function Controller::menu()
     * @see \gplcart\core\controllers\frontend\Controller::menu()
     * @see \gplcart\core\controllers\backend\Controller::menu()
     */
    protected function addFunctionMenu()
    {
        if ($this->controller instanceof \gplcart\core\controllers\frontend\Controller) {
            $function = new \Twig_SimpleFunction('menu', function (array $options = array()) {
                return $this->controller->menu($options);
            }, array('is_safe' => array('all')));
            $this->twig->addFunction($function);
        }
    }

    /**
     * Adds function \gplcart\core\Controller::prop()
     * @see \gplcart\core\Controller::prop()
     */
    protected function addFunctionProp()
    {
        $function = new \Twig_SimpleFunction('prop', function ($name) {
            return $this->controller->prop($name);
        });
        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::path()
     * @see \gplcart\core\Controller::path()
     */
    protected function addFunctionPath()
    {
        $function = new \Twig_SimpleFunction('path', function ($path = null) {
            return $this->controller->path($path);
        });
        $this->twig->addFunction($function);
    }

}
