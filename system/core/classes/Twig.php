<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\classes;

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
    public function set($path, $object, $options = array())
    {
        $this->loader = new \Twig_Loader_Filesystem($path);
        $this->twig = new \Twig_Environment($this->loader, $options);
        $this->twig->addGlobal('gc', $object);
    }

    /**
     * Renders a .twig template
     * @param type $file
     * @param type $data
     * @return type
     */
    public function render($file, $data)
    {
        if (empty($this->twig)) {
            return "Failed to render twig template $file";
        }

        $template = $this->twig->loadTemplate($file);
        return $template->render($data);
    }

}
