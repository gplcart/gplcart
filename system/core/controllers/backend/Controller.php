<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\Container;
use gplcart\core\Controller as BaseController;

/**
 * Contents methods related to admin backend
 */
class Controller extends BaseController
{

    use \gplcart\core\traits\ControllerWidget,
        \gplcart\core\traits\ControllerJob;

    /**
     * Job model instance
     * @var \gplcart\core\models\Job $job
     */
    protected $job;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->job = Container::get('gplcart\\core\\models\\Job');

        $this->processCurrentJobTrait($this);

        $this->hook->fire('init.backend', $this);
        $this->controlHttpStatus();
    }

    /**
     * Returns rendered admin menu
     * @param array $options
     * @return string
     */
    public function menu(array $options = array())
    {
        $items = array();
        foreach ($this->route->getList() as $path => $route) {

            if (strpos($path, 'admin/') !== 0 || empty($route['menu']['admin'])) {
                continue;
            }

            if (isset($route['access']) && !$this->access($route['access'])) {
                continue;
            }

            $items[$path] = array(
                'url' => $this->url($path),
                'depth' => (substr_count($path, '/') - 1),
                'text' => $this->text($route['menu']['admin'])
            );
        }

        ksort($items);

        $options += array('items' => $items);
        return $this->renderMenuTrait($this, $options);
    }

    /**
     * Returns an array of existing stores
     * @return array
     */
    public function stores()
    {
        return $this->store->getList();
    }

    /**
     * Displays parent admin menu items
     * @todo Output real content
     */
    public function adminSections()
    {
        $this->redirect('admin');
    }

}
