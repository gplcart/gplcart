<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\Transaction as TransactionModel;
use gplcart\core\handlers\validator\Component as ComponentValidator;

/**
 * Provides methods to validate orders to be stored in the database
 */
class Order extends ComponentValidator
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
        $this->validateShippingAddressOrder();
        $this->validatePaymentAddressOrder();
        $this->validateUserCartId();
        $this->validateCreatorOrder();
        $this->validateTotalOrder();
        $this->validateComponentPricesOrder();
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

        if ($this->isUpdating() && !isset($module_id)) {
            return null;
        }

        if (empty($module_id)) {
            $vars = array('@field' => $this->language->text('Payment'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('payment', $error);
            return false;
        }

        $method = $this->payment->get($module_id);

        if (empty($method['status'])) {
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

        if ($this->isUpdating() && !isset($module_id)) {
            return null;
        }

        if (empty($module_id)) {
            $vars = array('@field' => $this->language->text('Shipping'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('shipping', $error);
            return false;
        }

        $method = $this->shipping->get($module_id);

        if (empty($method['status'])) {
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

        if (!isset($status)) {
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
     * Validates a shipping address
     * @return boolean
     */
    protected function validateShippingAddressOrder()
    {
        $address_id = $this->getSubmitted('shipping_address');

        if ($this->isUpdating() && !isset($address_id)) {
            return null;
        }

        if (empty($address_id)) {
            $vars = array('@field' => $this->language->text('Shipping address'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('shipping_address', $error);
            return false;
        }

        if (!is_numeric($address_id)) {
            $vars = array('@field' => $this->language->text('Shipping address'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('shipping_address', $error);
            return false;
        }

        $address = $this->address->get($address_id);

        if (empty($address)) {
            $vars = array('@name' => $this->language->text('Shipping address'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('shipping_address', $error);
            return false;
        }

        return true;
    }

    /**
     * Validates a payment address
     * @return boolean
     */
    protected function validatePaymentAddressOrder()
    {
        $address_id = $this->getSubmitted('payment_address');

        if (empty($address_id) && !$this->isError('shipping_address')) {
            $address_id = $this->getSubmitted('shipping_address');
        }

        if (empty($address_id)) {
            $vars = array('@field' => $this->language->text('Payment address'));
            $error = $this->language->text('@field is required', $vars);
            $this->setError('payment_address', $error);
            return false;
        }

        if (!is_numeric($address_id)) {
            $vars = array('@field' => $this->language->text('Payment address'));
            $error = $this->language->text('@field must be numeric', $vars);
            $this->setError('payment_address', $error);
            return false;
        }

        $address = $this->address->get($address_id);

        if (empty($address)) {
            $vars = array('@name' => $this->language->text('Payment address'));
            $error = $this->language->text('@name is unavailable', $vars);
            $this->setError('payment_address', $error);
            return false;
        }

        $this->setSubmitted('payment_address', $address_id);
        return true;
    }

    /**
     * Validates a creator user ID
     * @return boolean|null
     */
    protected function validateCreatorOrder()
    {
        $creator = $this->getSubmitted('creator');

        if (empty($creator)) {
            return null;
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
     * Validates order component prices
     * @return bool
     */
    protected function validateComponentPricesOrder()
    {
        $components = $this->getSubmitted('data.components');

        if (empty($components)) {
            return null;
        }

        foreach ($components as $id => $price) {
            if (is_numeric($id) && !is_numeric($price)) {
                $vars = array('@field' => $this->language->text('Price'));
                $error = $this->language->text('@field must be numeric', $vars);
                $this->setError("data.components.$id", $error);
                continue;
            }

            if (strlen($price) > 10) {
                $vars = array('@max' => 10, '@field' => $this->language->text('Price'));
                $error = $this->language->text('@field must not be longer than @max characters', $vars);
                $this->setError("data.components.$id", $error);
            }
        }

        return !$this->isError('components');
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
        $log = (string) $this->getSubmitted('log');

        if (mb_strlen($log) > 255) {
            $vars = array('@max' => 255, '@field' => $this->language->text('Log'));
            $error = $this->language->text('@field must not be longer than @max characters', $vars);
            $this->setError('log', $error);
            return false;
        }

        return true;
    }

}
