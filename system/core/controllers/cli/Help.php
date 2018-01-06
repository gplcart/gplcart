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
        $routes = $this->route->getList();
        ksort($routes);

        $this->outputCommandHelp($routes);
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
    protected function outputCommandHelp(array $routes)
    {
        $command = $this->getArgument(0);

        if (!isset($command)) {
            return null;
        }

        if (empty($routes[$command])) {
            $this->outputErrors($this->text('Unknown command'), true);
        }

        $this->line();
        if (empty($routes[$command]['description'])) {
            $this->output($this->text('No description provided for the command'));
        }

        $this->line($this->text($routes[$command]['description']));

        if (!empty($routes[$command]['usage'])) {
            $this->line();
            $this->line($this->text('Usage:'));
            foreach ($routes[$command]['usage'] as $usage) {
                $this->line($usage);
            }
        }

        if (!empty($routes[$command]['options'])) {
            $this->line();
            $this->line($this->text('Options:'));
            foreach ($routes[$command]['options'] as $option => $description) {
                $vars = array('@option' => $option, '@description' => $this->text($description));
                $this->line($this->text('  @option  @description', $vars));
            }
        }

        $this->abort();
    }

}
