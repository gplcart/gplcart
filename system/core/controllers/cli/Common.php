<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\cli;

use core\CliController;

/**
 * Handles common CLI commands
 */
class Common extends CliController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays core version
     */
    public function version()
    {
        $this->setMessage(GC_VERSION)->output();
    }

}
