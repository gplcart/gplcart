<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core;

use core\Hook;
use core\Route;
use core\Config;
use core\Logger;
use core\classes\Url;
use core\classes\Session;
use core\exceptions\SystemFailure as Exception;

class Facade
{

    /**
     * Route class instance
     * @var \core\Route $route
     */
    protected $route;

    /**
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Session class instance
     * @var \core\classes\Session $session
     */
    protected $session;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Logger class instance
     * @var \core\Logger $logger
     */
    protected $logger;

    /**
     * Exception class instance
     * @var \core\exceptions\SystemFailure $exception
     */
    protected $exception;

    public function __construct(Route $route, Url $url, Session $session, Config $config, Hook $hook, Logger $logger, Exception $exception)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->logger = $logger;
        $this->session = $session;
        $this->exception = $exception;

        if ($this->config->exists()) {

            date_default_timezone_set($this->config->get('timezone', 'Europe/London'));

            // Set error level
            switch ($this->config->get('error_level', 2)) {
                case 0:
                    error_reporting(0); // disable at all
                    break;
                case 1:
                    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                    break;
                case 2:
                    error_reporting(E_ALL);
            }

            // Register error handlers
            register_shutdown_function(array($this->logger, 'shutdownHandler'));
            set_exception_handler(array($this->exception, 'exceptionHandler'));
            set_error_handler(array($this->logger, 'errorHandler'), error_reporting());

            // Debugging
            if ($this->config->get('kint', 0)) {
                require_once GC_LIBRARY_DIR . '/kint/Kint.class.php';
            }

            // Register hooks
            $this->hook->registerModules($this->config->getEnabledModules());
        }
    }

    /**
     * Process the route
     */
    public function route()
    {
        // Redirect to installation if needed
        if ((!$this->config->exists() || $this->session->get('install', 'processing')) && !$this->url->isInstall()) {
            $this->url->redirect('install');
        }

        $this->route->process();
    }

}
