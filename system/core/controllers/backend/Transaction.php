<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Transaction as TransactionModel;
use gplcart\core\controllers\backend\Controller as BackendController;

/**
 * Handles incoming requests and outputs data related to order payment transactions
 */
class Transaction extends BackendController
{

    /**
     * Transaction model instance
     * @var \gplcart\core\models\Transaction $transaction
     */
    protected $transaction;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Constructor
     * @param TransactionModel $transaction
     * @param PaymentModel $payment
     */
    public function __construct(TransactionModel $transaction,
            PaymentModel $payment)
    {
        parent::__construct();

        $this->payment = $payment;
        $this->transaction = $transaction;
    }

    /**
     * Displays the transaction overview page
     */
    public function listTransaction()
    {
        $this->actionTransaction();

        $this->setTitleListTransaction();
        $this->setBreadcrumbListTransaction();

        $query = $this->getFilterQuery();

        $filters = array('created', 'order_id', 'payment_service',
            'service_transaction_id');
        
        $this->setFilter($filters, $query);

        $total = $this->getTotalTransaction($query);
        $limit = $this->setPager($total, $query);

        $this->setData('payment_methods', $this->payment->getList(false));
        $this->setData('transactions', $this->getListTransaction($limit, $query));

        $this->outputListTransaction();
    }

    /**
     * Applies an action to the selected transactions
     * @return null
     */
    protected function actionTransaction()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (int) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        foreach ($selected as $id) {

            if ($action === 'delete' && $this->access('transaction_delete')) {
                $deleted += (int) $this->transaction->delete($id);
            }

            if ($action === 'status' && $this->access('transaction_edit')) {
                $updated += (int) $this->transaction->update($id, array('status' => $value));
            }
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num transactions', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num transactions', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Returns total transactions found for the given conditions
     * @param array $query
     * @return integer
     */
    protected function getTotalTransaction(array $query)
    {
        $query['count'] = true;
        return (int) $this->transaction->getList($query);
    }

    /**
     * Returns an array of transactions
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListTransaction($limit, array $query)
    {
        $query['limit'] = $limit;
        return (array) $this->transaction->getList($query);
    }

    /**
     * Sets titles on the transaction overview page
     */
    protected function setTitleListTransaction()
    {
        $this->setTitle($this->text('Transactions'));
    }

    /**
     * Sets breadcrumbs on the transaction overview page
     */
    protected function setBreadcrumbListTransaction()
    {
        $breadcrumb = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders the transaction overview page
     */
    protected function outputListTransaction()
    {
        $this->output('sale/transaction/list');
    }

}
