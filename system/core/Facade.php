<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\classes\Url;
use core\classes\Session;

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
     * Constructor
     * @param Route $route
     * @param Url $url
     * @param Session $session
     * @param Config $config
     * @param Hook $hook
     * @param Logger $logger
     */
    public function __construct(Route $route, Url $url, Session $session,
            Config $config, Hook $hook, Logger $logger)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->logger = $logger;
        $this->session = $session;

        date_default_timezone_set($this->config->get('timezone', 'Europe/London'));

        $this->setErrorReportingLevel();
        $this->setErrorHandlers();
        $this->setDebuggingTools();

        // Register hooks
        $modules = $this->config->getEnabledModules();
        $this->hook->modules($modules);
    }
    
    /**
     * Process the route
     * @return mixed
     */
    public function route()
    {
        if (GC_CLI) {
            // Process command line mode
            return Container::instance('core\\CliRoute')->process();
        }

        // Process normal HTTP request
        $this->session->init();

        // Redirect to installation if needed
        $install = (!$this->config->exists() // No config/common.php exists
                || $this->session->get('install', 'processing')) // Installation in progress
                && !$this->url->isInstall(); // and not on /install page

        if ($install) {
            return $this->url->redirect('install');
        }

        return $this->route->process();
    }

    /**
     * Sets system error level
     */
    protected function setErrorReportingLevel()
    {
        $level = $this->config->get('error_level', 2);

        switch ($level) {
            case 0:
                error_reporting(0); // disable at all
                break;
            case 1:
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                break;
            case 2:
                error_reporting(E_ALL);
        }
    }

    /**
     * Registers error handlers
     */
    protected function setErrorHandlers()
    {
        register_shutdown_function(array($this->logger, 'shutdownHandler'));
        set_exception_handler(array($this->logger, 'exceptionHandler'));
        set_error_handler(array($this->logger, 'errorHandler'), error_reporting());
    }

    /**
     * Sets debugging tools
     */
    protected function setDebuggingTools()
    {
        //if ($this->config->get('kint', 0)) {
            require_once GC_LIBRARY_DIR . '/kint/Kint.class.php';
        //}
    }

}
