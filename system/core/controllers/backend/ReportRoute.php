<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\UserRole as UserRoleModel;
use gplcart\core\traits\Listing as ListingTrait;

/**
 * Handles incoming requests and outputs data related to system routes
 */
class ReportRoute extends Controller
{

    use ListingTrait;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

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
        $this->setFilterListReportRoute();
        $this->setPagerListReportRoute();

        $this->setData('routes', (array) $this->getListReportRoute());
        $this->setData('permissions', $this->getPermissionsReportRole());

        $this->outputListReportRoute();
    }

    /**
     * Sets the filter on the route overview page
     */
    protected function setFilterListReportRoute()
    {
        $this->setFilter($this->getAllowedFiltersReportRoute());
    }

    /**
     * Returns an array of allowed fields for sorting and filtering
     * @return array
     */
    protected function getAllowedFiltersReportRoute()
    {
        return array('pattern', 'access', 'status', 'internal');
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReportRoute()
    {
        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->getListReportRoute(true)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of routes or counts them
     * @param bool $count
     * @return array|int
     */
    protected function getListReportRoute($count = false)
    {
        $list = $this->route->getList();
        $this->prepareListReportRoute($list);
        $allowed = $this->getAllowedFiltersReportRoute();

        $this->filterList($list, $allowed, $this->query_filter);
        $this->sortList($list, $allowed, $this->query_filter, array('pattern' => 'asc'));

        if ($count) {
            return count($list);
        }

        $this->limitList($list, $this->data_limit);
        return $list;
    }

    /**
     * Prepares an array of routes
     * @param array $routes
     */
    protected function prepareListReportRoute(array &$routes)
    {
        $permissions = $this->getPermissionsReportRole();

        foreach ($routes as $pattern => &$route) {

            if (strpos($pattern, 'admin') === 0 && !isset($route['access'])) {
                $route['access'] = 'admin';
            }

            if (!isset($route['access'])) {
                $route['access'] = GC_PERM_PUBLIC;
            }

            $access_names = array();

            if (isset($permissions[$route['access']])) {
                $access_names[$route['access']] = $this->text($permissions[$route['access']]);
            } else {
                $access_names[''] = $this->text($permissions[GC_PERM_PUBLIC]);
            }

            if ($route['access'] === GC_PERM_SUPERADMIN) {
                $access_names = array(GC_PERM_SUPERADMIN => $this->text($permissions[GC_PERM_SUPERADMIN]));
            }

            $route['pattern'] = $pattern;
            $route['access_name'] = implode(' + ', $access_names);
            $route['access'] = implode('', array_keys($access_names));
            $route['status'] = !isset($route['status']) || !empty($route['status']);
        }
    }

    /**
     * Returns an array of permissions
     * @return array
     */
    protected function getPermissionsReportRole()
    {
        $permissions = array(
            GC_PERM_PUBLIC => 'Public',
            GC_PERM_SUPERADMIN => 'Superadmin'
        );

        $permissions += $this->role->getPermissions();
        return $permissions;
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
