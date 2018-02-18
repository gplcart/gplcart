<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\helpers\Cli;
use gplcart\core\helpers\Server;
use LogicException;
use OutOfBoundsException;
use OverflowException;
use UnexpectedValueException;

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
     * @param Cli $cli
     * @param Server $server
     */
    public function __construct(Hook $hook, Cli $cli, Server $server)
    {
        $this->cli = $cli;
        $this->hook = $hook;
        $this->server = $server;

        $this->setParams();

        $this->hook->attach('construct.cli.route', $this);
    }

    /**
     * Returns the current CLI route
     * @param null|string $command
     * @return array
     */
    public function get($command = null)
    {
        if (isset($command)) {
            $routes = $this->getList();
            return isset($routes[$command]) ? $routes[$command] : array();
        }

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
     * @return $this
     */
    public function setParams($params = null)
    {
        if (isset($params)) {
            $this->params = (array) $params;
        } else {
            $this->params = $this->cli->parseParams($this->server->cliArgs());
        }

        return $this;
    }

    /**
     * Returns an array of CLI routes
     * @return array
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
     * @throws OverflowException
     */
    protected function setAliases(array $routes)
    {
        foreach ($routes as $command => $route) {

            if (!isset($route['alias'])) {
                continue;
            }

            if (isset($this->aliases[$route['alias']])) {
                throw new OverflowException("Command alias '{$route['alias']}' is not unique");
            }

            $this->aliases[$route['alias']] = $command;
        }
    }

    /**
     * Processes the current CLI command
     * @param null|array
     * @throws LogicException
     */
    public function process($route = null)
    {
        $this->set($route);
        $this->callController();
    }

    /**
     * Sets the current route
     * @param array|null $route
     * @return $this
     * @throws OutOfBoundsException
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
                $this->params = array();
            }

            if (empty($routes[$command])) {
                throw new OutOfBoundsException('Unknown command');
            }

            if (isset($routes[$command]['status']) && empty($routes[$command]['status'])) {
                throw new OutOfBoundsException('Disabled command');
            }

            $route = $routes[$command];
            $route['command'] = $command;
            $route['params'] = $this->params;
        }

        $this->route = (array) $route;
        return $this;
    }

    /**
     * Call a route controller
     * @param null|array
     * @throws LogicException
     * @throws UnexpectedValueException
     */
    public function callController($route = null)
    {
        if (!isset($route)) {
            $route = $this->route;
        }

        $callback = Handler::get($route, null, 'controller');

        if (!$callback[0] instanceof CliController) {
            throw new UnexpectedValueException('Controller must be instance of \gplcart\core\CliController');
        }

        if (!$callback[0]->isInitialized()) {
            throw new LogicException('Controller is not initialized');
        }

        call_user_func($callback);

        throw new LogicException('The command was completed incorrectly');
    }

}
