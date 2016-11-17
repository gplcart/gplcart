<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers\frontend;

use core\models\Transaction as ModelsTransaction;
use core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to payment transactions
 */
class Transaction extends FrontendController
{

    /**
     * Transaction model instance
     * @var \core\models\Transaction $transaction
     */
    protected $transaction;

    /**
     * Controller
     * @param ModelsTransaction $transaction
     */
    public function __construct(ModelsTransaction $transaction)
    {
        parent::__construct();

        $this->transaction = $transaction;
    }

    /**
     * Success transaction URL callback
     * @param integer $order_id
     */
    public function successTransaction($order_id)
    {
        if (empty($order_id)) {
            $this->outputError(403);
        }

        $request = $this->request->request();
        $result = $this->transaction->remote($order_id, $request);

        $this->redirect($result['redirect'], $result['message'], $result['severity']);
    }

}
