<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

/**
 * Handles incoming requests and outputs data related to admin sections
 */
class Section extends Controller
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Displays the admin section page
     * @param string $parent
     */
    public function listSection($parent)
    {
        $this->controlAccess('admin');

        $this->setTitleListSection($parent);
        $this->setBreadcrumbListSection();

        $this->setDataListSection($parent);
        $this->outputListSection();
    }

    /**
     * Sets template data on the admin section page
     * @param string $parent
     */
    protected function setDataListSection($parent)
    {
        $options = array(
            'parent_url' => $parent,
            'template' => 'section/menu'
        );

        $this->setData('menu', $this->getWidgetAdminMenu($this->route, $options));
    }

    /**
     * Sets titles on the admin section page
     * @param string $parent
     */
    protected function setTitleListSection($parent)
    {
        foreach ($this->route->getList() as $route) {
            if (isset($route['menu']['admin']) && isset($route['arguments']) && in_array($parent, $route['arguments'])) {
                $this->setTitle($route['menu']['admin']);
                break;
            }
        }
    }

    /**
     * Sets breadcrumbs on the admin section page
     */
    protected function setBreadcrumbListSection()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the admin section page
     */
    protected function outputListSection()
    {
        $this->output('section/section');
    }

}
