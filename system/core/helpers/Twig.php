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
        if (!empty($options['cache'])) {
            $theme = $object->prop('current_theme');
            $options['cache'] = GC_MODULE_DIR . "/{$theme['id']}/{$options['cache']}";
        }

        $this->loader = new \Twig_Loader_Filesystem($path);
        $this->twig = new \Twig_Environment($this->loader, $options);

        if (!empty($options['debug'])) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }

        $this->addFunctionD($object);
        $this->addFunctionXss($object);
        $this->addFunctionUrl($object);
        $this->addFunctionProp($object);
        $this->addFunctionMenu($object);
        $this->addFunctionDate($object);
        $this->addFunctionCart($object);
        $this->addFunctionDate($object);
        $this->addFunctionText($object);
        $this->addFunctionUser($object);
        $this->addFunctionStore($object);
        $this->addFunctionError($object);
        $this->addFunctionConfig($object);
        $this->addFunctionAccess($object);
        $this->addFunctionSummary($object);
        $this->addFunctionCompare($object);
        $this->addFunctionTruncate($object);
        $this->addFunctionSettings($object);
        $this->addFunctionWishlist($object);
        $this->addFunctionAttributes($object);
        $this->addFunctionIsSuperadmin($object);
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
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::error()
     */
    protected function addFunctionError($object)
    {
        $function = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') use ($object) {
            return $object->error($key, $has_error, $no_error);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::text()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::text()
     */
    protected function addFunctionText($object)
    {
        $function = new \Twig_SimpleFunction('text', function ($text, $arguments = array()) use ($object) {
            return $object->text($text, $arguments);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::access()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::access()
     */
    protected function addFunctionAccess($object)
    {
        $function = new \Twig_SimpleFunction('access', function ($permission) use ($object) {
            return $object->access($permission);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::url()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::url()
     */
    protected function addFunctionUrl($object)
    {
        $function = new \Twig_SimpleFunction('url', function ($path = '', array $query = array(), $absolute = false) use ($object) {
            return $object->url($path, $query, $absolute);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::isSuperadmin()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::isSuperadmin()
     */
    protected function addFunctionIsSuperadmin($object)
    {
        $function = new \Twig_SimpleFunction('isSuperadmin', function ($user_id = null) use ($object) {
            return $object->isSuperadmin($user_id);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::date()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::date()
     */
    protected function addFunctionDate($object)
    {
        $function = new \Twig_SimpleFunction('date', function ($timestamp = null, $full = true) use ($object) {
            return $object->date($timestamp, $full);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::attributes()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::attributes()
     */
    protected function addFunctionAttributes($object)
    {
        $function = new \Twig_SimpleFunction('attributes', function ($attributes) use ($object) {
            return $object->attributes($attributes);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::config()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::config()
     */
    protected function addFunctionConfig($object)
    {
        $function = new \Twig_SimpleFunction('config', function ($key = null, $default = null) use ($object) {
            return $object->config($key, $default);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::settings()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::settings()
     */
    protected function addFunctionSettings($object)
    {
        $function = new \Twig_SimpleFunction('settings', function ($key = null, $default = null) use ($object) {
            return $object->settings($key, $default);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::summary()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::summary()
     */
    protected function addFunctionSummary($object)
    {
        $function = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $filter = null) use ($object) {
            return $object->summary($text, $xss, $filter);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::user()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::user()
     */
    protected function addFunctionUser($object)
    {
        $function = new \Twig_SimpleFunction('user', function ($item = null) use ($object) {
            return $object->user($item);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::store()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::store()
     */
    protected function addFunctionStore($object)
    {
        $function = new \Twig_SimpleFunction('store', function ($item = null) use ($object) {
            return $object->store($item);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds debug function to see template variables \gplcart\core\Controller::$data
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionD($object)
    {
        $function = new \Twig_SimpleFunction('d', function ($key = null) use ($object) {
            d($object->getData($key));
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::xss()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::xss()
     */
    protected function addFunctionXss($object)
    {
        $function = new \Twig_SimpleFunction('xss', function ($text, $filter = null) use ($object) {
            return $object->xss($text, $filter);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::truncate()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::truncate()
     */
    protected function addFunctionTruncate($object)
    {
        $function = new \Twig_SimpleFunction('truncate', function ($string, $length = 100, $trimmarker = '...') use ($object) {
            return $object->truncate($string, $length, $trimmarker);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::cart()
     * @param \gplcart\core\controllers\frontend\Controller $object
     * @see \gplcart\core\controllers\frontend\Controller::cart()
     */
    protected function addFunctionCart($object)
    {
        $function = new \Twig_SimpleFunction('cart', function ($key = null) use ($object) {
            return $object->cart($key);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::compare()
     * @param \gplcart\core\controllers\frontend\Controller $object
     * @see \gplcart\core\controllers\frontend\Controller::compare()
     */
    protected function addFunctionCompare($object)
    {
        if (!$object instanceof \gplcart\core\Controller) {
            throw new \Exception;
        }

        $function = new \Twig_SimpleFunction('compare', function ($key = null) use ($object) {
            return $object->compare($key);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\controllers\frontend\Controller::wishlist()
     * @param \gplcart\core\controllers\frontend\Controller $object
     * @see \gplcart\core\controllers\frontend\Controller::wishlist()
     */
    protected function addFunctionWishlist($object)
    {
        if (!$object instanceof \gplcart\core\Controller) {
            throw new \Exception;
        }

        $function = new \Twig_SimpleFunction('wishlist', function ($key = null) use ($object) {
            return $object->wishlist($key);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function Controller::menu()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\controllers\frontend\Controller::menu()
     * @see \gplcart\core\controllers\backend\Controller::menu()
     */
    protected function addFunctionMenu($object)
    {
        if (!$object instanceof \gplcart\core\Controller) {
            throw new \Exception;
        }

        $function = new \Twig_SimpleFunction('menu', function (array $options = array()) use ($object) {
            return $object->menu($options);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::prop()
     * @param \gplcart\core\Controller $object
     * @see \gplcart\core\Controller::prop()
     */
    protected function addFunctionProp($object)
    {
        $function = new \Twig_SimpleFunction('prop', function ($name) use ($object) {
            return $object->prop($name);
        });

        $this->twig->addFunction($function);
    }

}
