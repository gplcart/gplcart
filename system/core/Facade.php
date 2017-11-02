<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Session as SessionHelper;

/**
 * Provides methods to route incoming requests and setup the system
 */
class Facade
{

    /**
     * Config class instance
     * @var \gplcart\core\Config $config
     */
    protected $config;

    /**
     * @param Config $config
     * @param Hook $hook
     * @param SessionHelper $session
     */
    public function __construct(Config $config, Hook $hook, SessionHelper $session)
    {
        $session->start();

        $this->config = $config;
        $this->config->init();

        $hook->registerAll();
        $hook->attach('construct', $this);
    }

    /**
     * Routes command line requests
     */
    public function routeCli()
    {
        if (!$this->config->get('cli_status', 1)) {
            throw new \Exception('CLI access is disabled!');
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
        /* @var $route \gplcart\core\Route */
        $route = Container::get('gplcart\\core\\Route');

        /* @var $url \gplcart\core\helpers\Url */
        $url = Container::get('gplcart\\core\\helpers\\Url');

        if ($this->config->isInitialized() || $url->isInstall()) {
            $route->output();
        } else {
            $url->redirect('install');
        }
    }

}
