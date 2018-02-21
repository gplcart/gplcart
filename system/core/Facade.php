<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Session;
use gplcart\core\helpers\Url;

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
     * @param Session $session
     * @param Url $url
     */
    public function __construct(Config $config, Hook $hook, CliRoute $cli, Route $route, Session $session, Url $url)
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
            echo 'No access' . PHP_EOL;
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
