<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Session as SessionHelper;
use gplcart\core\helpers\Url as UrlHelper;

/**
 * An intermediary class that provides methods to route incoming requests and setup the system
 */
class Facade
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * Hook instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * CLI router class instance
     * @var \gplcart\core\CliRoute $cli
     */
    protected $cli;

    /**
     * HTTP router class instance
     * @var \gplcart\core\Route
     */
    protected $route;

    /**
     * URL helper class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Session helper class instance
     * @var \gplcart\core\helpers\Session $session
     */
    protected $session;

    /**
     * @param Config $config
     * @param Hook $hook
     * @param CliRoute $cli
     * @param Route $route
     * @param SessionHelper $session
     * @param UrlHelper $url
     */
    public function __construct(Config $config, Hook $hook, CliRoute $cli, Route $route,
                                SessionHelper $session, UrlHelper $url)
    {
        $this->url = $url;
        $this->cli = $cli;
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->session = $session;

        $this->session->init();
        $this->config->init();
        $this->route->init();
        $this->hook->init();

        $this->hook->attach('construct', $this);
    }

    /**
     * Routes CLI commands
     */
    public function routeCli()
    {
        if (!$this->config->get('cli_status', 1)) {
            echo 'CLI access is disabled!' . PHP_EOL;
            exit(1);
        }

        $this->cli->process();
    }

    /**
     * Routes HTTP requests
     */
    public function routeHttp()
    {
        if ($this->config->isInitialized() || $this->url->isInstall()) {
            $this->route->process();
        } else {
            $this->url->redirect('install');
        }
    }

}
