<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core;

use core\Hook;
use core\Handler;
use core\helpers\Cli;

/**
 * Routes CLI commands
 */
class CliRoute
{

    /**
     * Hook class instance
     * @var \core\Hook $hook
     */
    protected $hook;

    /**
     * Cli class instance
     * @var \core\helpers\Cli $cli
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
    public function __construct(Cli $cli, Hook $hook)
    {
        $this->cli = $cli;
        $this->hook = $hook;

        $this->init();
    }

    /**
     * Sets parsed arguments
     */
    protected function init()
    {
        $source = GC_CLI_EMULATE ? $_POST['command'] : $_SERVER['argv'];
        $this->arguments = $this->cli->parse($source);
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
     * Returns an array of CLI routes
     */
    public function getList()
    {
        $routes = array();

        $routes['help'] = array(
            'handlers' => array(
                'process' => array('core\CliController', 'help')
            ),
            'help' => array(
                'description' => 'Displays all available commands'
            ),
        );

        $routes['install'] = array(
            'handlers' => array(
                'process' => array('core\controllers\cli\Install', 'storeInstall')
            ),
            'help' => array(
                'description' => 'Performs full system installation',
                'options' => array(
                    '--db-name' => 'Required. Database name',
                    '--user-email' => 'Required. Admin e-mail',
                    '--store-host' => 'Optional. Domain name e.g "example.com". Do not use "http://" and slashes. Defaults to "localhost"',
                    '--db-user' => 'Optional. Database user. Defaults to "root"',
                    '--db-host' => 'Optional. Database host. Defaults to "localhost"',
                    '--db-password' => 'Optional. Database password. Defaults to empty string',
                    '--db-type' => 'Optional. Database type, e.g "mysql" or "sqlite". Defaults to "mysql"',
                    '--db-port' => 'Optional. Database port. Defaults to 3306',
                    '--user-password' => 'Optional. Admin password. Defaults to randomly generated password',
                    '--store-title' => 'Optional. Name of the store. Defaults to "GPL Cart"',
                    '--store-basepath' => 'Optional. Subfolder name. Defaults to empty string, i.e domain root folder',
                    '--store-timezone' => 'Optional. Timezone of the store. Defaults to "Europe/London"',
                    '--installer' => 'Optional. ID of module to be used for this installation process'
                ),
            ),
        );

        $this->hook->fire('cli.route', $routes);
        return $routes;
    }

    /**
     * Processes the current command
     */
    public function process()
    {
        $this->callController();
    }

    /**
     * Finds and calls an appropriate controller for the current command
     * @return mixed
     */
    protected function callController()
    {
        $routes = $this->getList();
        $command = array_shift($this->arguments);

        if (empty($routes[$command])) {
            echo("Unknown command. Use 'help' command to see what you have");
            exit(1);
        }

        $routes[$command]['command'] = $command;
        $routes[$command]['arguments'] = $this->arguments;
        $this->route = $routes[$command];

        Handler::call($this->route, null, 'process', array($this->arguments));
        echo("The command was not completed correctly");
        exit(1);
    }

}
