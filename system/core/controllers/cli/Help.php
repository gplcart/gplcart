<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\cli;

use gplcart\core\CliController;

/**
 * Handles CLI commands related to user manuals
 */
class Help extends CliController
{

    /**
     * Help constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Callback for "help" command
     * Shows help for a certain command or a list of all available commands
     */
    public function help()
    {
        $this->outputCommandHelp();
        $this->outputCommandListHelp();
    }

    /**
     * Output a list of all available CLI commands
     */
    protected function outputCommandListHelp()
    {
        $routes = $this->route->getList();

        ksort($routes);

        $rows = array(
            array(
                $this->text('Command'),
                $this->text('Alias'),
                $this->text('Description')
            )
        );

        foreach ($routes as $command => $info) {
            $rows[] = array(
                $command,
                empty($info['alias']) ? '' : $info['alias'],
                empty($info['description']) ? '' : $info['description']
            );
        }

        $this->table($rows);
        $this->output();
    }

    /**
     * Displays help message for a command
     */
    protected function outputCommandHelp()
    {
        $command = $this->getParam(0);

        if (!empty($command)) {
            $this->outputHelp($command);
            $this->output();
        }
    }

}
