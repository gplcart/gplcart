<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Dashboard as DashboardModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to admin dashboard
 */
class Dashboard extends BackendController
{

    /**
     * Dashboard model instance
     * @var \gplcart\core\models\Dashboard $dashboard
     */
    protected $dashboard;

    /**
     * An array of dashboard items
     * @var array
     */
    protected $data_dashboard = array();

    /**
     * @param DashboardModel $dashboard
     */
    public function __construct(DashboardModel $dashboard)
    {
        parent::__construct();

        $this->dashboard = $dashboard;
    }

    /**
     * Displays the dashboard page
     */
    public function indexDashboard()
    {
        $this->toggleIntroIndexDashboard();

        $this->setDashboard(true);
        $this->setTitleIndexDashboard();
        $this->setDataContentIndexDashboard();

        $this->outputIndexDashboard();
    }

    /**
     * Sets a dashboard template data
     */
    protected function setDataContentIndexDashboard()
    {
        $columns = $this->config('dashboard_columns', 2);

        $this->setData('columns', $columns);
        $this->setData('dashboard', gplcart_array_split($this->data_dashboard['data'], $columns));

        $stores = $this->store->getList(array('status' => 1));
        $this->setData('no_enabled_stores', empty($stores));

        if ($this->config('intro', false) && $this->isSuperadmin()) {
            $this->setData('intro', $this->render('dashboard/intro'));
        }
    }

    /**
     * Toggles intro view
     */
    protected function toggleIntroIndexDashboard()
    {
        if ($this->isQuery('skip_intro')) {
            $this->config->reset('intro');
            $this->redirect();
        }
    }

    /**
     * Sets titles on the dashboard page
     */
    protected function setTitleIndexDashboard()
    {
        $this->setTitle($this->text('Dashboard'), false);
    }

    /**
     * Render and output the dashboard page
     */
    protected function outputIndexDashboard()
    {
        $this->output('dashboard/dashboard');
    }

    /**
     * Displays the edit dashboard page
     */
    public function editDashboard()
    {
        $this->setDashboard(false);
        $this->setTitleEditDashboard();
        $this->setBreadcrumbEditDashboard();

        $this->setData('dashboard', $this->data_dashboard);

        $this->submitEditDashboard();
        $this->outputEditDashboard();
    }

    /**
     * Sets an array of dashboard items
     * @param bool $active
     */
    protected function setDashboard($active)
    {
        $dashboard = $this->dashboard->getList(array('user_id' => $this->uid, 'active' => $active));
        $this->data_dashboard = $this->prepareDashboard($dashboard);
    }

    /**
     * Prepare an array of dashboard items
     * @param array $dashboard
     * @return array
     */
    protected function prepareDashboard(array $dashboard)
    {
        foreach ($dashboard['data'] as &$item) {
            $this->setItemRendered($item, array('content' => $item), array('template_item' => $item['template']));
        }

        return $dashboard;
    }

    /**
     * Sets breadcrumbs on the edit dashboard page
     */
    protected function setBreadcrumbEditDashboard()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Handles an array of submitted data
     */
    protected function submitEditDashboard()
    {
        if ($this->isPosted('save')) {
            $this->validateEditDashboard();
            $this->saveDashboard();
        } else if ($this->isPosted('delete') && isset($this->data_dashboard['dashboard_id'])) {
            $this->deleteDashboard();
        }
    }

    /**
     * Validates an array of submitted data
     */
    protected function validateEditDashboard()
    {
        $submitted = $this->setSubmitted('dashboard');

        foreach ($submitted as &$item) {
            $item['status'] = !empty($item['status']);
            $item['weight'] = intval($item['weight']);
        }

        $this->setSubmitted(null, $submitted);
    }

    /**
     * Saves submitted data
     */
    protected function saveDashboard()
    {
        $this->controlAccess('dashboard_edit');

        if ($this->dashboard->set($this->uid, $this->getSubmitted())) {
            $this->redirect('', $this->text('Your dashboard has been updated'), 'success');
        }

        $this->redirect('', $this->text('Your dashboard has not been updated'), 'warning');
    }

    /**
     * Deletes a saved a dashboard record
     */
    protected function deleteDashboard()
    {
        $this->controlAccess('dashboard_edit');

        if ($this->dashboard->delete($this->data_dashboard['dashboard_id'])) {
            $message = $this->text('Your dashboard has been reset');
            $this->redirect('', $message, 'success');
        }

        $this->redirect('admin', $this->text('Your dashboard has not been reset'), 'warning');
    }

    /**
     * Set titles on the edit dashboard page
     */
    protected function setTitleEditDashboard()
    {
        $this->setTitle($this->text('Customize dashboard'));
    }

    /**
     * Set breadcrumbs on the edit dashboard page
     */
    protected function outputEditDashboard()
    {
        $this->output('dashboard/edit');
    }

}
