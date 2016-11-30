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
        require_once GC_LIBRARY_DIR . '/twig/Autoloader.php';
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

        // Add global controller object
        $this->twig->addGlobal('gc', $object);

        // Add custom functions
        $this->addFunctionUrl($object);
        $this->addFunctionDate($object);
        $this->addFunctionText($object);
        $this->addFunctionConfig($object);
        $this->addFunctionAccess($object);
        $this->addFunctionSummary($object);
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
     * Adds function \core\Controller::text()
     * @param object $object
     */
    protected function addFunctionText($object)
    {
        $function = new Twig_SimpleFunction('text', function ($object, $text, $arguments = array()) {
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
        $function = new Twig_SimpleFunction('access', function ($object, $permission) {
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
        $function = new Twig_SimpleFunction('url', function ($object, $path = '', array $query = array(), $absolute = false) {
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
        $function = new Twig_SimpleFunction('isSuperadmin', function ($object, $user_id = null) {
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
        $function = new Twig_SimpleFunction('date', function ($object, $timestamp = null, $full = true) {
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
        $function = new Twig_SimpleFunction('attributes', function ($object, $attributes) {
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
        $function = new Twig_SimpleFunction('config', function ($object, $key = null, $default = null) {
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
        $function = new Twig_SimpleFunction('summary', function ($object, $text, $xss = false, $tags = null, $protocols = null) {
            return $object->summary($text, $xss, $tags, $protocols);
        });

        $this->twig->addFunction($function);
    }

}
