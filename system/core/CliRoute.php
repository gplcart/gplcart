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
     * An array of parsed CLI arguments
     * @var array
     */
    protected $arguments = array();

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

        $this->setArguments();

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
     * Returns an array of arguments for the current command
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
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
     * Set CLI arguments
     * @param array|null $arguments
     * @return array
     */
    public function setArguments($arguments = null)
    {
        if (isset($arguments)) {
            $this->arguments = (array) $arguments;
        } else {
            $this->arguments = $this->cli->parseArguments($this->server->cliArgs());
        }

        return $this->arguments;
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
        $this->callHandler();
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
            $command = array_shift($this->arguments);

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
            $route['arguments'] = $this->arguments;
        }

        return $this->route = (array) $route;
    }

    /**
     * Call a route handler
     * @param string $method
     * @param array $arguments
     */
    public function callHandler($method = 'controller', array $arguments = array())
    {
        try {
            $arguments = array_merge(array($this->route['arguments']), $arguments);
            Handler::call($this->route, null, $method, $arguments);
        } catch (Exception $ex) {
            throw new RouteException($ex->getMessage());
        }
    }

}
