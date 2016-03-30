<?php
namespace core\controllers;

use core\Controller;

class Error extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function error404()
    {
        $this->setTitle($this->text('404 - Page not found'), false);
        $this->output('common/error/404', array('headers' => array(404)));
    }
    
    public function error403()
    {
        $this->setTitle($this->text('403 - Permission denied'), false);
        $this->output('common/error/403', array('headers' => array(403)));
    }
}
