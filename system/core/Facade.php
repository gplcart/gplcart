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
    gplcart\core\Config;
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
     * @param Config $config
     * @param Route $route
     * @param Hook $hook
     * @param UrlHelper $url
     * @param SessionHelper $session
     */
    public function __construct(Config $config, Route $route, Hook $hook,
            UrlHelper $url, SessionHelper $session)
    {
        $this->url = $url;
        $this->hook = $hook;
        $this->route = $route;
        $this->config = $config;
        $this->session = $session;

        $this->hook->attachAll();
        $this->hook->attach('construct', $this);
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
        if ($this->config->get('cli_status', 1)) {
            Container::get('gplcart\\core\\CliRoute')->process();
        }
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

}
