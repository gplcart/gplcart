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
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Help command callback. Lists all available commands
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

        $this->line($this->text('List of available commands'));

        foreach ($routes as $command => $info) {

            if (!empty($info['alias'])) {
                $command .= ", {$info['alias']}";
            }

            if (empty($info['description'])) {
                $this->line("  $command");
                continue;
            }

            $vars = array(
                '@command' => $command,
                '@description' => $this->text($info['description'])
            );

            $this->line($this->text('  @command - @description', $vars));
        }
        
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
