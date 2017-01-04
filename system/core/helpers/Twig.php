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
            $theme = $object->getTheme();
            $options['cache'] = GC_MODULE_DIR . "/$theme/{$options['cache']}";
        }

        $this->loader = new \Twig_Loader_Filesystem($path);
        $this->twig = new \Twig_Environment($this->loader, $options);

        if (!empty($options['debug'])) {
            $this->twig->addExtension(new \Twig_Extension_Debug());
        }

        $this->addFunctionUrl($object);
        $this->addFunctionDate($object);
        $this->addFunctionText($object);
        $this->addFunctionError($object);
        $this->addFunctionToken($object);
        $this->addFunctionConfig($object);
        $this->addFunctionAccess($object);
        $this->addFunctionSummary($object);
        $this->addFunctionAttributes($object);
        $this->addFunctionIsSuperadmin($object);
        $this->addFunctionUid($object);
        $this->addFunctionStore($object);
        $this->addFunctionVarDump($object);
        $this->addFunctionXss($object);
        $this->addFunctionTruncate($object);
        $this->addFunctionDate($object);
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
     */
    protected function addFunctionError($object)
    {
        $function = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') use ($object) {
            return $object->error($key, $has_error, $no_error);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::token()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionToken($object)
    {
        $function = new \Twig_SimpleFunction('token', function () use ($object) {
            return $object->token();
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::text()
     * @param \gplcart\core\Controller $object
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
     */
    protected function addFunctionAttributes($object)
    {
        $function = new \Twig_SimpleFunction('attributes', function ($attributes) use ($object) {
            return $object->attributes($attributes);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::config()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionConfig($object)
    {
        $function = new \Twig_SimpleFunction('config', function ($key = null, $default = null) use ($object) {
            return $object->config($key, $default);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::summary()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionSummary($object)
    {
        $function = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $tags = null, $protocols = null) use ($object) {
            return $object->summary($text, $xss, $tags, $protocols);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::uid()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionUid($object)
    {
        $function = new \Twig_SimpleFunction('uid', function () use ($object) {
            return $object->uid();
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::store()
     * @param \gplcart\core\Controller $object
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
    protected function addFunctionVarDump($object)
    {
        $function = new \Twig_SimpleFunction('var_dump', function ($key = null) use ($object) {
            ddd($object->getData($key));
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::xss()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionXss($object)
    {
        $function = new \Twig_SimpleFunction('xss', function ($text, $tags = null, $protocols = null) use ($object) {
            return $object->xss($text, $tags, $protocols);
        }, array('is_safe' => array('all')));

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \gplcart\core\Controller::truncate()
     * @param \gplcart\core\Controller $object
     */
    protected function addFunctionTruncate($object)
    {
        $function = new \Twig_SimpleFunction('truncate', function ($string, $length = 100, $trimmarker = '...') use ($object) {
            return $object->truncate($string, $length, $trimmarker);
        });

        $this->twig->addFunction($function);
    }

}
