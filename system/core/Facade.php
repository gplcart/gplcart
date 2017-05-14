<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Hook,
    gplcart\core\Route,
    gplcart\core\Config,
    gplcart\core\Logger;
use gplcart\core\helpers\Url as UrlHelper,
    gplcart\core\helpers\Session as SessionHelper;

/**
 * Provides methods to route incoming requests and setup the system
 */
class Facade
{

    /**
     * Route class instance
     * @var \gplcart\core\Route $route
     */
    protected $route;

    /**
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Session class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * Logger class instance
     * @var \gplcart\core\Logger $logger
     */
    protected $logger;

    /**
     * Constructor
     * @param Route $route
     * @param Config $config
     * @param Hook $hook
     * @param Logger $logger
     * @param UrlHelper $url
     * @param SessionHelper $session
     */
    public function __construct(Route $route, Config $config, Hook $hook,
            Logger $logger, UrlHelper $url, SessionHelper $session)
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

        $this->hook->registerAll();
        $this->hook->fire('construct', $this);
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->hook->fire('destruct', $this);
    }

    /**
     * Routes incoming requests
     */
    public function route()
    {
        if (GC_CLI) {
            $this->routeCli();
        } else {
            $this->routeHttp();
        }
    }

    /**
     * Routes command line requests
     */
    protected function routeCli()
    {
        if ($this->config->get('cli_disabled', 0)) {
            exit('CLI access has been disabled');
        }

        Container::get('gplcart\\core\\CliRoute')->process();
    }

    /**
     * Routes normal HTTP requests
     */
    protected function routeHttp()
    {
        if ($this->isInstalling()) {
            $this->url->redirect('install');
        } else {
            $this->route->process();
        }
    }

    /**
     * Whether the store is installing
     * @return boolean
     */
    protected function isInstalling()
    {
        return (!$this->config->exists() // No config/common.php exists
                || $this->session->get('install.processing')) // Installation in progress
                && !$this->url->isInstall(); // and not on /install page
    }

    /**
     * Sets system error level
     */
    protected function setErrorReportingLevel()
    {
        $level = $this->config->get('error_level', 2);

        switch ($level) {
            case 0:
                // Disable errors at all
                error_reporting(0);
                break;
            case 1:
                // Show only important errors
                error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
                break;
            case 2:
                // Show all errors
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

}
