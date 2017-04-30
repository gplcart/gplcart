<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core;

use gplcart\core\Hook,
    gplcart\core\Handler;
use gplcart\core\helpers\Cli as CliHelper;

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
     * Cli class instance
     * @var \gplcart\core\helpers\Cli $cli
     */
    protected $cli;

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
     * Constructor
     * @param Cli $cli
     * @param Hook $hook
     */
    public function __construct(CliHelper $cli, Hook $hook)
    {
        $this->cli = $cli;
        $this->hook = $hook;

        if (!empty($_SERVER['argv'])) {
            $this->arguments = $this->cli->parse($_SERVER['argv']);
        }

        $this->hook->fire('construct.cli.route', $this);
    }

    /**
     * Returns the current route
     * @return array
     */
    public function get()
    {
        return $this->route;
    }

    /**
     * Returns an array of arguments of the current command
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * Set an array of CLI arguments
     * @param array $arguments
     */
    public function setArguments(array $arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * Returns an array of CLI routes
     */
    public function getList()
    {
        $routes = require GC_CONFIG_CLI_ROUTE;
        $this->hook->fire('cli.route.list', $routes);
        return $routes;
    }

    /**
     * Processes the current command
     */
    public function process()
    {
        $routes = $this->getList();
        $command = array_shift($this->arguments);

        if (empty($routes[$command])) {
            exit("Unknown command. Use 'help' command to see supported commands");
        }

        $routes[$command]['command'] = $command;
        $routes[$command]['arguments'] = $this->arguments;

        $this->route = $routes[$command];

        Handler::call($this->route, null, 'process', array($this->arguments));
        exit('The command was completed incorrectly');
    }

}
