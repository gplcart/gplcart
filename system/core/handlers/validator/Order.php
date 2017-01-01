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
        $this->submitted = &$submitted;

        $this->validateOrder($options);
        $this->validateStoreId($options);
        $this->validatePaymentOrder($options);
        $this->validateShippingOrder($options);
        $this->validateStatusOrder($options);
        $this->validateAddressOrder($options);
        $this->validateUserCartId($options);
        $this->validateCreatorOrder($options);
        $this->validateTotalOrder($options);
        $this->validateCurrencyOrder($options);
        $this->validateCommentOrder($options);
        $this->validateTransactionOrder($options);
        $this->validateLogOrder($options);

        return $this->getResult();
    }

    /**
     * Validates an order to be updated
     * @param array $options
     * @return boolean
     */
    protected function validateOrder(array $options)
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
     * @param array $options
     * @return boolean|null
     */
    protected function validatePaymentOrder(array $options)
    {
        $module_id = $this->getSubmitted('payment', $options);

        if (empty($module_id)) {
            return null;
        }

        $method = $this->payment->get($module_id);

        if (empty($method)) {
            $vars = array('@name' => $this->language->text('Payment'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('payment', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a shipping method
     * @param array $options
     * @return boolean|null
     */
    protected function validateShippingOrder(array $options)
    {
        $module_id = $this->getSubmitted('shipping', $options);

        if (empty($module_id)) {
            return null;
        }

        $method = $this->shipping->get($module_id);

        if (empty($method)) {
            $vars = array('@name' => $this->language->text('Shipping'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('shipping', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a status
     * @param array $options
     * @return boolean|null
     */
    protected function validateStatusOrder(array $options)
    {
        $status = $this->getSubmitted('status', $options);

        if (empty($status)) {
            return null;
        }

        $statuses = $this->order->getStatuses();

        if (empty($statuses[$status])) {
            $vars = array('@name' => $this->language->text('Status'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('status', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates order addresses
     * @param array $options
     * @return boolean
     */
    protected function validateAddressOrder(array $options)
    {
        foreach ($this->address->getTypes() as $type) {

            $field = $type . '_address';
            $address_id = $this->getSubmitted($field, $options);

            if (!isset($address_id)) {
                continue;
            }

            $name = ucfirst(str_replace('_', ' ', $field));

            if (!is_numeric($address_id)) {
                $vars = array('@field' => $this->language->text($name));
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError($field, $error, $options);
                continue;
            }

            if (empty($address_id)) {
                continue;
            }

            $address = $this->address->get($address_id);

            if (empty($address)) {
                $vars = array('@name' => $this->language->text($name));
                $error = $this->language->text('@name is unavailable', $vars);
                $this->setError($field, $error, $options);
            }
        }

        return !isset($error);
    }

    /**
     * Validates a creator user ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateCreatorOrder(array $options)
    {
        $creator = $this->getSubmitted('creator', $options);

        if ($this->isUpdating() && !isset($creator)) {
            return null;
        }

        if (empty($creator)) {
            $vars = array('@field' => $this->language->text('Creator'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('creator', $error, $options);
            return false;
        }

        if (!is_numeric($creator)) {
            $vars = array('@field' => $this->language->text('Creator'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('creator', $error, $options);
            return false;
        }

        $user = $this->user->get($creator);

        if (empty($user)) {
            $vars = array('@name' => $this->language->text('Creator'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('creator', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates order total
     * @param array $options
     * @return boolean|null
     */
    protected function validateTotalOrder(array $options)
    {
        $total = $this->getSubmitted('total', $options);

        if (!isset($total)) {
            return null;
        }

        if (!is_numeric($total)) {
            $vars = array('@field' => $this->language->text('Total'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('total', $error, $options);
            return false;
        }

        if (strlen($total) > 10) {
            $vars = array('@max' => 10, '@field' => $this->language->text('Total'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('total', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a currency code
     * @param array $options
     * @return boolean|null
     */
    protected function validateCurrencyOrder(array $options)
    {
        $code = $this->getSubmitted('currency', $options);

        if (!isset($code)) {
            return null;
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $vars = array('@name' => $this->language->text('Currency'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('currency', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates an order comment
     * @param array $options
     * @return boolean
     */
    protected function validateCommentOrder(array $options)
    {
        $comment = $this->getSubmitted('comment', $options);

        if (isset($comment) && mb_strlen($comment) > 65535) {
            $vars = array('@max' => 65535, '@field' => $this->language->text('Comment'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('comment', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a transaction ID
     * @param array $options
     * @return boolean|null
     */
    protected function validateTransactionOrder(array $options)
    {
        $transaction_id = $this->getSubmitted('transaction_id', $options);

        if (!isset($transaction_id)) {
            return null;
        }

        if (!is_numeric($transaction_id)) {
            $vars = array('@field' => $this->language->text('Transaction'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('transaction_id', $error, $options);
            return false;
        }

        $transaction = $this->transaction->get($transaction_id);

        if (empty($transaction)) {
            $vars = array('@name' => $this->language->text('Transaction'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('transaction_id', $error, $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a log message
     * @param array $options
     * @return boolean|null
     */
    protected function validateLogOrder(array $options)
    {
        if (!$this->isUpdating()) {
            return null;
        }

        $log = $this->getSubmitted('log', $options);

        if (empty($log) || mb_strlen($log) > 255) {
            $vars = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Log'));
            $error = $this->language->text('@field must be @min - @max characters long', $vars);
            $this->setError('log', $error, $options);
            return false;
        }

        return true;
    }

}
