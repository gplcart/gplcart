<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Hook;
use core\Route;
use core\Config;
use core\Logger;
use core\classes\Url;
use core\classes\Session;
use core\exceptions\SystemFailure;

/**
 * Provides methods to route incoming requests and setup the system
 */
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

    /**
     * Constructor
     * @param Route $route
     * @param Url $url
     * @param Session $session
     * @param Config $config
     * @param Hook $hook
     * @param Logger $logger
     * @param SystemFailure $exception
     */
    public function __construct(Route $route, Url $url, Session $session,
            Config $config, Hook $hook, Logger $logger, SystemFailure $exception)
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
            //if ($this->config->get('kint', 0)) {
                require_once GC_LIBRARY_DIR . '/kint/Kint.class.php';
            //}

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
