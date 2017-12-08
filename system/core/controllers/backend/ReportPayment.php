<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to payment methods
 */
class ReportPayment extends BackendController
{

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * @param PaymentModel $payment
     */
    public function __construct(PaymentModel $payment)
    {
        parent::__construct();

        $this->payment = $payment;
    }

    /**
     * Displays the payment methods overview page
     */
    public function listReportPayment()
    {
        $this->setTitleListReportPayment();
        $this->setBreadcrumbListReportPayment();

        $this->setData('methods', $this->getListReportPayment());
        $this->outputListReportPayment();
    }

    /**
     * Returns an array of payment methods
     * @return array
     */
    protected function getListReportPayment()
    {
        return $this->payment->getList();
    }

    /**
     * Sets title on the payment method overview page
     */
    protected function setTitleListReportPayment()
    {
        $this->setTitle($this->text('Payment methods'));
    }

    /**
     * Sets breadcrumbs on the payment method overview page
     */
    protected function setBreadcrumbListReportPayment()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders templates on the payment method overview page
     */
    protected function outputListReportPayment()
    {
        $this->output('report/payment_methods');
    }

}
