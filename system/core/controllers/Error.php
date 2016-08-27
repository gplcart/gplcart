<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to HTTP errors
 */
class Error extends FrontendController
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays 404 Page Not Found
     */
    public function error404()
    {
        $this->setTitle($this->text('404 - Page not found'), false);
        $this->output('common/error/404', array('headers' => array(404)));
    }

    /**
     * Displays 403 Access Denied page
     */
    public function error403()
    {
        $this->setTitle($this->text('403 - Permission denied'), false);
        $this->output('common/error/403', array('headers' => array(403)));
    }

}
