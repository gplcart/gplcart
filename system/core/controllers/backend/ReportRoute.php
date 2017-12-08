<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to system routes
 */
class ReportRoute extends BackendController
{

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * @param UserRoleModel $role
     */
    public function __construct(UserRoleModel $role)
    {
        parent::__construct();

        $this->role = $role;
    }

    /**
     * Displays the route overview page
     */
    public function listReportRoute()
    {
        $this->setTitleListReportRoute();
        $this->setBreadcrumbListReportRoute();

        $this->setData('routes', $this->getListReportRoute());
        $this->outputListReportRoute();
    }

    /**
     * Returns an array of routes
     */
    protected function getListReportRoute()
    {
        $routes = $this->route->getList();
        return $this->prepareListReportRoute($routes);
    }

    /**
     * Prepares an array of routes
     * @param array $routes
     * @return array
     */
    protected function prepareListReportRoute(array $routes)
    {
        $permissions = $this->role->getPermissions();

        foreach ($routes as $pattern => &$route) {

            if (strpos($pattern, 'admin') === 0) {
                $route['permission_name'] = array($this->text($permissions['admin']));
            } else {
                $route['permission_name'] = array($this->text('Public'));
            }

            if (!isset($route['access'])) {
                continue;
            }

            if ($route['access'] === '__superadmin') {
                $route['permission_name'] = array($this->text('Superadmin'));
                continue;
            }

            if (!isset($permissions[$route['access']])) {
                $route['permission_name'] = array($this->text('Unknown'));
                continue;
            }

            $route['permission_name'][] = $this->text($permissions[$route['access']]);
        }

        ksort($routes);
        return $routes;
    }

    /**
     * Sets title on the route overview page
     */
    protected function setTitleListReportRoute()
    {
        $this->setTitle($this->text('Routes'));
    }

    /**
     * Sets breadcrumbs on the route overview page
     */
    protected function setBreadcrumbListReportRoute()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the route overview page
     */
    protected function outputListReportRoute()
    {
        $this->output('report/routes');
    }

}
