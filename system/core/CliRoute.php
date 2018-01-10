<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Cli as CliHelper,
    gplcart\core\helpers\Server as ServerHelper;
use gplcart\core\exceptions\Route as RouteException;

/**
 * Routes CLI commands
 */
class CliRoute
{

    /**
     * Hook class instance
     * @var \gplcart\core\Hook $hook
     */
    protected $hook;

    /**
     * CLI class instance
     * @var \gplcart\core\helpers\Cli $cli
     */
    protected $cli;

    /**
     * Server class instance
     * @var \gplcart\core\helpers\Server $server
     */
    protected $server;

    /**
     * The current CLI route data
     * @var array
     */
    protected $route = array();

    /**
     * An array of parsed CLI parameters
     * @var array
     */
    protected $params = array();

    /**
     * An array of commands keyed by their aliases
     * @var array
     */
    protected $aliases = array();

    /**
     * @param Hook $hook
     * @param CliHelper $cli
     * @param ServerHelper $server
     */
    public function __construct(Hook $hook, CliHelper $cli, ServerHelper $server)
    {
        $this->cli = $cli;
        $this->hook = $hook;
        $this->server = $server;

        $this->setParams();

        $this->hook->attach('construct.cli.route', $this);
    }

    /**
     * Returns the current CLI route
     * @return array
     */
    public function get()
    {
        return $this->route;
    }

    /**
     * Returns an array of parameters for the current command
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }

    /**
     * Returns an array of commands keyed by their aliases
     * @return array
     */
    public function getAliases()
    {
        return $this->aliases;
    }

    /**
     * Set CLI params (both options and arguments)
     * @param array|null $params
     * @return array
     */
    public function setParams($params = null)
    {
        if (isset($params)) {
            $this->params = (array) $params;
        } else {
            $this->params = $this->cli->parseParams($this->server->cliArgs());
        }

        return $this->params;
    }

    /**
     * Returns an array of CLI routes
     * @return array
     * @throws RouteException
     */
    public function getList()
    {
        $routes = &gplcart_static('cli.route.list');

        if (isset($routes)) {
            return $routes;
        }

        $routes = (array) gplcart_config_get(GC_FILE_CONFIG_ROUTE_CLI);

        $this->hook->attach('cli.route.list', $routes, $this);
        $this->setAliases($routes);
        return $routes;
    }

    /**
     * Sets an array of commands keyed by their aliases
     * @param array $routes
     * @throws RouteException
     */
    protected function setAliases(array $routes)
    {
        foreach ($routes as $command => $route) {

            if (!isset($route['alias'])) {
                continue;
            }

            if (isset($this->aliases[$route['alias']])) {
                throw new RouteException("Command alias '{$route['alias']}' is not unique");
            }

            $this->aliases[$route['alias']] = $command;
        }
    }

    /**
     * Processes the current CLI command
     */
    public function process()
    {
        $this->set();
        $this->callController();
        throw new RouteException('The command was completed incorrectly');
    }

    /**
     * Sets the current route
     * @param array|null $route
     * @return array|null
     * @throws RouteException
     */
    public function set($route = null)
    {
        if (!isset($route)) {

            $routes = $this->getList();
            $command = array_shift($this->params);

            if (isset($this->aliases[$command])) {
                $command = $this->aliases[$command];
            }

            if (empty($routes[$command])) {
                $command = 'help';
            }

            if (empty($routes[$command])) {
                throw new RouteException('Unknown command');
            }

            $route = $routes[$command];
            $route['command'] = $command;
            $route['params'] = $this->params;
        }

        return $this->route = (array) $route;
    }

    /**
     * Call a route controller
     */
    protected function callController()
    {
        try {
            $callback = Handler::get($this->route, null, 'controller');
            if (!$callback[0] instanceof \gplcart\core\CliController) {
                throw new RouteException('Controller must be instance of \gplcart\core\CliController');
            }
            call_user_func_array($callback, array());
        } catch (Exception $ex) {
            throw new RouteException($ex->getMessage());
        }
    }

}
