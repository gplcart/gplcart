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
     * The current CLI route data
     * @var array
     */
    protected $route = array();

    /**
     * An array of parsed CLI arguments
     * @var array
     */
    protected static $arguments = array();

    /**
     * Constructor
     * @param Hook $hook
     */
    public function __construct(Hook $hook)
    {
        $this->hook = $hook;

        $this->parseArguments();
    }

    /**
     * Returns an array of parsed CLI arguments
     * Based on work by Patrick Fisher <patrick@pwfisher.com>
     * 
     * @param null|array $argv
     * @return array
     */
    public function parseArguments($argv = null)
    {
        if (!isset($argv)) {
            $argv = $_SERVER['argv'];
        }

        array_shift($argv);

        $out = array();
        for ($i = 0, $j = count($argv); $i < $j; $i++) {

            $arg = $argv[$i];
            if (substr($arg, 0, 2) === '--') {

                $pos = strpos($arg, '=');

                if ($pos === false) {
                    $key = substr($arg, 2);
                    if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                        $value = $argv[$i + 1];
                        $i++;
                    } else {
                        $value = isset($out[$key]) ? $out[$key] : true;
                    }

                    $out[$key] = $value;
                    continue;
                }

                $key = substr($arg, 2, $pos - 2);
                $value = substr($arg, $pos + 1);
                $out[$key] = $value;
                continue;
            }

            if (substr($arg, 0, 1) === '-') {

                if (substr($arg, 2, 1) === '=') {
                    $key = substr($arg, 1, 1);
                    $value = substr($arg, 3);
                    $out[$key] = $value;
                    continue;
                }

                $chars = str_split(substr($arg, 1));

                foreach ($chars as $char) {
                    $key = $char;
                    $value = isset($out[$key]) ? $out[$key] : true;
                    $out[$key] = $value;
                }

                if ($i + 1 < $j && $argv[$i + 1][0] !== '-') {
                    $out[$key] = $argv[$i + 1];
                    $i++;
                }

                continue;
            }

            $value = $arg;
            $out[] = $value;
        }

        self::$arguments = $out;
        return $out;
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
        return self::$arguments;
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
                    '--store-host' => 'Required. Domain name e.g "example.com" or "localhost". Do not use "http://" and slashes',
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
        $arguments = $this->getArguments();
        $this->callController($arguments);
    }

    /**
     * Finds and calls an appropriate controller for the current command
     * @param array $arguments
     * @return mixed
     */
    protected function callController(array $arguments)
    {
        $routes = $this->getList();
        $command = array_shift($arguments);

        if (empty($routes[$command])) {
            echo("\033[31mWrong or unsupported command. Use 'help' command to see what you have\033[0m\n");
            exit(1);
        }

        $routes[$command]['command'] = $command;
        $routes[$command]['arguments'] = $arguments;

        $this->route = $routes[$command];

        Handler::call($this->route, null, 'process', array($arguments));
        echo("\033[31mThe command was not completed correctly\033[0m\n");
        exit(1);
    }

}
