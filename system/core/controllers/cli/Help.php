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
        $this->line($this->text('List of available commands. To see help for a certain command use --help option'));

        foreach ($this->route->getList() as $command => $info) {

            if (empty($info['help']['description'])) {
                $description = $this->text('No description available');
            } else {
                $description = $this->text($info['help']['description']);
            }

            if (!empty($info['alias'])) {
                $command .= ", {$info['alias']}";
            }

            $vars = array('@command' => $command, '@description' => $description);
            $this->line($this->text('  @command - @description', $vars));
        }

        $this->output();
    }

}
