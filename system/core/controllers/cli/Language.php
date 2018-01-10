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
 * Handles CLI commands related to languages
 */
class Language extends CliController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Callback for "lang" command
     */
    public function language()
    {
        $this->selectLanguage($this->getParam(0));
        $this->output();
    }

}
