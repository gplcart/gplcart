<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\Controller;

/**
 * Handles incoming requests and outputs data related to various errors
 */
class Error extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the page not found page
     */
    public function error404()
    {
        $this->setTitleError404();
        $this->outputError404();
    }

    /**
     * Displays the access denied page
     */
    public function error403()
    {
        $this->setTitleError403();
        $this->outputError403();
    }

    /**
     * Sets titles on the 404 page not found page
     */
    protected function setTitleError404()
    {
        $this->setTitle($this->text('404 - Page not found'), false);
    }

    /**
     * Renders the error 404 page not found page
     */
    protected function outputError404()
    {
        $this->output('common/error/404', array('headers' => array(404)));
    }

    /**
     * Sets titles on the access denied page
     */
    protected function setTitleError403()
    {
        $this->setTitle($this->text('403 - Permission denied'), false);
    }

    /**
     * Renders the access denied page
     */
    protected function outputError403()
    {
        $this->output('common/error/403', array('headers' => array(403)));
    }
}
