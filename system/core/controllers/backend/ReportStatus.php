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
 * Handles incoming requests and outputs data related to system status reports
 */
class ReportStatus extends Controller
{

    /**
     * Report model instance
     * @var \gplcart\core\models\Report $report
     */
    protected $report;

    /**
     * @param ReportModel $report
     */
    public function __construct(ReportModel $report)
    {
        parent::__construct();

        $this->report = $report;
    }

    /**
     * Displays the status page
     */
    public function listReportStatus()
    {
        $this->setTitleListReportStatus();
        $this->setBreadcrumbListReportStatus();
        $this->setData('statuses', $this->getReportStatus());

        $this->outputListReportStatus();
    }

    /**
     * Returns system status report
     * @return array
     */
    protected function getReportStatus()
    {
        return $this->report->getStatus();
    }

    /**
     * Sets title on the status page
     */
    protected function setTitleListReportStatus()
    {
        $this->setTitle('System status');
    }

    /**
     * Sets breadcrumbs on the status page
     */
    protected function setBreadcrumbListReportStatus()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Render and output the status page
     */
    protected function outputListReportStatus()
    {
        $this->output('report/status');
    }

}
