<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use Exception;
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
     * URL class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

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
        $session->start();

        $this->url = $url;
        $this->route = $route;
        $this->config = $config;

        $hook->registerAll();
        $hook->attach('construct', $this);
    }

    /**
     * Routes command line requests
     */
    public function routeCli()
    {
        if (!$this->config->get('cli_status', 1)) {
            throw new Exception('CLI access is disabled!');
        }

        /* @var $route \gplcart\core\CliRoute */
        $route = Container::get('gplcart\\core\\CliRoute');
        $route->process();
    }

    /**
     * Routes HTTP requests
     */
    public function routeHttp()
    {
        if ($this->config->exists() || $this->url->isInstall()) {
            $this->route->process();
        } else {
            $this->url->redirect('install');
        }
    }

}
