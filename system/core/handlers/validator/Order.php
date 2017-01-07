<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\Transaction as TransactionModel;
use gplcart\core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate orders to be stored in the database
 */
class Order extends BaseValidator
{

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Currency model instance
     * @var \gplcart\core\models\Currency $currency
     */
    protected $currency;

    /**
     * Transaction model instance
     * @var \gplcart\core\models\Transaction $transaction
     */
    protected $transaction;

    /**
     * Constructor
     * @param OrderModel $order
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     * @param AddressModel $address
     * @param CurrencyModel $currency
     * @param TransactionModel $transaction
     */
    public function __construct(OrderModel $order, PaymentModel $payment,
            ShippingModel $shipping, AddressModel $address,
            CurrencyModel $currency, TransactionModel $transaction)
    {
        parent::__construct();

        $this->order = $order;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->currency = $currency;
        $this->transaction = $transaction;
    }

    /**
     * Performs full order data validation
     * @param array $submitted
     * @param array $options
     * @return array|boolean
     */
    public function order(array &$submitted, array $options = array())
    {
        $this->options = $options;
        $this->submitted = &$submitted;

        $this->validateOrder();
        $this->validateStoreId();
        $this->validatePaymentOrder();
        $this->validateShippingOrder();
        $this->validateStatusOrder();
        $this->validateAddressOrder();
        $this->validateUserCartId();
        $this->validateCreatorOrder();
        $this->validateTotalOrder();
        $this->validateCurrencyOrder();
        $this->validateCommentOrder();
        $this->validateTransactionOrder();
        $this->validateLogOrder();

        return $this->getResult();
    }

    /**
     * Validates an order to be updated
     * @return boolean
     */
    protected function validateOrder()
    {
        $id = $this->getUpdatingId();

        if ($id === false) {
            return null;
        }

        $data = $this->order->get($id);

        if (empty($data)) {
            $vars = array('@name' => $this->language->text('Order'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('update', $error);
            return false;
        }

        $this->setUpdating($data);
        return true;
    }

    /**
     * Validates a payment method
     * @return boolean|null
     */
    protected function validatePaymentOrder()
    {
        $module_id = $this->getSubmitted('payment');

        if (empty($module_id)) {
            return null;
        }

        $method = $this->payment->get($module_id);

        if (empty($method)) {
            $vars = array('@name' => $this->language->text('Payment'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('payment', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a shipping method
     * @return boolean|null
     */
    protected function validateShippingOrder()
    {
        $module_id = $this->getSubmitted('shipping');

        if (empty($module_id)) {
            return null;
        }

        $method = $this->shipping->get($module_id);

        if (empty($method)) {
            $vars = array('@name' => $this->language->text('Shipping'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('shipping', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a status
     * @return boolean|null
     */
    protected function validateStatusOrder()
    {
        $status = $this->getSubmitted('status');

        if (empty($status)) {
            return null;
        }

        $statuses = $this->order->getStatuses();

        if (empty($statuses[$status])) {
            $vars = array('@name' => $this->language->text('Status'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('status', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates order addresses
     * @return boolean
     */
    protected function validateAddressOrder()
    {
        foreach ($this->address->getTypes() as $type) {

            $field = $type . '_address';
            $address_id = $this->getSubmitted($field);

            if (!isset($address_id)) {
                continue;
            }

            $name = ucfirst(str_replace('_', ' ', $field));

            if (!is_numeric($address_id)) {
                $vars = array('@field' => $this->language->text($name));
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error);
                continue;
            }

            if (empty($address_id)) {
                continue;
            }

            $address = $this->address->get($address_id);

            if (empty($address)) {
                $vars = array('@name' => $this->language->text($name));
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error);
            }
        }

        return !isset($error);
    }

    /**
     * Validates a creator user ID
     * @return boolean|null
     */
    protected function validateCreatorOrder()
    {
        $creator = $this->getSubmitted('creator');

        if ($this->isUpdating() && !isset($creator)) {
            return null;
        }

        if (empty($creator)) {
            $vars = array('@field' => $this->language->text('Creator'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('creator', $error);
            return false;
        }

        if (!is_numeric($creator)) {
            $vars = array('@field' => $this->language->text('Creator'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('creator', $error);
            return false;
        }

        $user = $this->user->get($creator);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('Creator'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('creator', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates order total
     * @return boolean|null
     */
    protected function validateTotalOrder()
    {
        $total = $this->getSubmitted('total');

        if (!isset($total)) {
            return null;
        }

        if (!is_numeric($total)) {
            $vars = array('@field' => $this->language->text('Total'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('total', $error);
            return false;
        }

        if (strlen($total) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Total'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('total', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a currency code
     * @return boolean|null
     */
    protected function validateCurrencyOrder()
    {
        $code = $this->getSubmitted('currency');

        if (!isset($code)) {
            return null;
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('currency', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates an order comment
     * @return boolean
     */
    protected function validateCommentOrder()
    {
        $comment = $this->getSubmitted('comment');

        if (isset($comment) && mb_strlen($comment) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Comment'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('comment', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a transaction ID
     * @return boolean|null
     */
    protected function validateTransactionOrder()
    {
        $transaction_id = $this->getSubmitted('transaction_id');

        if (!isset($transaction_id)) {
            return null;
        }

        if (!is_numeric($transaction_id)) {
            $vars = array('@field' => $this->language->text('Transaction'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('transaction_id', $error);
            return false;
        }

        $transaction = $this->transaction->get($transaction_id);

        if (empty($transaction)) {
            $vars = array('@name' => $this->language->text('Transaction'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('transaction_id', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a log message
     * @return boolean|null
     */
    protected function validateLogOrder()
    {
        if (!$this->isUpdating()) {
            return null;
        }

        $log = $this->getSubmitted('log');

        if (empty($log) || mb_strlen($log) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Log'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('log', $error);
            return false;
        }

        return true;
    }

}
