<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Report as ReportModel;

/**
 * Handles incoming requests and outputs data related to system events
 */
class ReportEvent extends Controller
{

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * @param ReportModel $report
     */
    public function __construct(ReportModel $report)
    {
        parent::__construct();

        $this->report = $report;
    }

    /**
     * Displays the event overview page
     */
    public function listReportEvent()
    {
        $this->clearReportEvent();
        $this->actionListReportEvent();

        $this->setTitleListReportEvent();
        $this->setBreadcrumbListReportEvent();
        $this->setFilterListReportEvent();
        $this->setPagerListReportEvent();

        $this->setData('types', $this->report->getTypes());
        $this->setData('severities', $this->report->getSeverities());
        $this->setData('records', $this->getListReportEvent());

        $this->outputListReportEvent();
    }

    /**
     * Applies an action to the selected aliases
     */
    protected function actionListReportEvent()
    {
        list($selected, $action) = $this->getPostedAction();

        if ($action === 'delete' && $this->access('report_events')) {
            if ($this->report->delete(array('log_id' => $selected))) {
                $message = $this->text('Deleted %num item(s)', array('%num' => count($selected)));
                $this->setMessage($message, 'success');
            }
        }
    }

    /**
     * Sets filter on the event overview page
     */
    protected function setFilterListReportEvent()
    {
        $this->setFilter(array('severity', 'type', 'created', 'text'));
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerListReportEvent()
    {
        $conditions = $this->query_filter;
        $conditions['count'] = true;

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->report->getList($conditions)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Deletes all system events from the database
     */
    protected function clearReportEvent()
    {
        $key = 'clear';
        $this->controlToken($key);

        if ($this->isQuery($key)) {
            $this->report->delete();
            $this->redirect('admin/report/events');
        }
    }

    /**
     * Returns an array of system events
     * @return array
     */
    protected function getListReportEvent()
    {
        $conditions = $this->query_filter;
        $conditions['limit'] = $this->data_limit;

        $list = (array) $this->report->getList($conditions);
        $this->prepareListReportEvent($list);
        return $list;
    }

    /**
     * Prepare an array of system events
     * @param array $list
     */
    protected function prepareListReportEvent(array &$list)
    {
        foreach ($list as &$item) {

            $variables = array();

            if (!empty($item['data']['variables'])) {
                $variables = $item['data']['variables'];
            }

            $item['created'] = $this->date($item['created']);

            $type = "event_{$item['type']}";
            $item['type'] = $this->text($type);

            if (!empty($item['translatable'])) {
                $item['text'] = $this->text($item['text'], $variables);
            }

            $item['summary'] = $this->truncate($item['text']);
            $item['severity_text'] = $this->text($item['severity']);
        }
    }

    /**
     * Sets title on the event overview page
     */
    protected function setTitleListReportEvent()
    {
        $this->setTitle($this->text('System events'));
    }

    /**
     * Sets breadcrumbs on the event overview page
     */
    protected function setBreadcrumbListReportEvent()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the event overview page
     */
    protected function outputListReportEvent()
    {
        $this->output('report/events');
    }

}
