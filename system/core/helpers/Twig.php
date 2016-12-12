<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\helpers;

/**
 * Wrapper class for TWIG template engine
 */
class Twig
{

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
     */
    public function __construct()
    {
        gplcart_require_library('twig/Autoloader.php');
        \Twig_Autoloader::register();
    }

    /**
     * Sets up Twig
     * @param string $path
     * @param object $object
     * @param array $options
     */
    public function set($path, $object, array $options = array())
    {
        $this->loader = new \Twig_Loader_Filesystem($path);
        $this->twig = new \Twig_Environment($this->loader, $options);

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
     * Adds function \core\Controller::error()
     * @param object $object
     */
    protected function addFunctionError($object)
    {
        $function = new \Twig_SimpleFunction('error', function ($key = null, $has_error = null, $no_error = '') use ($object) {
            return $object->error($key, $has_error, $no_error);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::token()
     * @param object $object
     */
    protected function addFunctionToken($object)
    {
        $function = new \Twig_SimpleFunction('token', function () use ($object) {
            return $object->token();
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::text()
     * @param object $object
     */
    protected function addFunctionText($object)
    {
        $function = new \Twig_SimpleFunction('text', function ($text, $arguments = array()) use ($object) {
            return $object->text($text, $arguments);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::access()
     * @param object $object
     */
    protected function addFunctionAccess($object)
    {
        $function = new \Twig_SimpleFunction('access', function ($permission) use ($object) {
            return $object->access($permission);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::url()
     * @param object $object
     */
    protected function addFunctionUrl($object)
    {
        $function = new \Twig_SimpleFunction('url', function ($path = '', array $query = array(), $absolute = false) use ($object) {
            return $object->url($path, $query, $absolute);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::isSuperadmin()
     * @param object $object
     */
    protected function addFunctionIsSuperadmin($object)
    {
        $function = new \Twig_SimpleFunction('isSuperadmin', function ($user_id = null) use ($object) {
            return $object->isSuperadmin($user_id);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::date()
     * @param object $object
     */
    protected function addFunctionDate($object)
    {
        $function = new \Twig_SimpleFunction('date', function ($timestamp = null, $full = true) use ($object) {
            return $object->date($timestamp, $full);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::attributes()
     * @param object $object
     */
    protected function addFunctionAttributes($object)
    {
        $function = new \Twig_SimpleFunction('attributes', function ($attributes) use ($object) {
            return $object->attributes($attributes);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::config()
     * @param object $object
     */
    protected function addFunctionConfig($object)
    {
        $function = new \Twig_SimpleFunction('config', function ($key = null, $default = null) use ($object) {
            return $object->config($key, $default);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::summary()
     * @param object $object
     */
    protected function addFunctionSummary($object)
    {
        $function = new \Twig_SimpleFunction('summary', function ($text, $xss = false, $tags = null, $protocols = null) use ($object) {
            return $object->summary($text, $xss, $tags, $protocols);
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::uid()
     * @param object $object
     */
    protected function addFunctionUid($object)
    {
        $function = new \Twig_SimpleFunction('uid', function () use ($object) {
            return $object->uid();
        });

        $this->twig->addFunction($function);
    }

    /**
     * Adds function \core\Controller::store()
     * @param object $object
     */
    protected function addFunctionStore($object)
    {
        $function = new \Twig_SimpleFunction('store', function ($item = null) use ($object) {
            return $object->store($item);
        });

        $this->twig->addFunction($function);
    }

}
