<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\handlers\validator\Component as ComponentValidator;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Currency as CurrencyModel;
use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\Transaction as TransactionModel;

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
     * @param OrderModel $order
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     * @param AddressModel $address
     * @param CurrencyModel $currency
     * @param TransactionModel $transaction
     */
    public function __construct(OrderModel $order, PaymentModel $payment, ShippingModel $shipping,
                                AddressModel $address, CurrencyModel $currency, TransactionModel $transaction)
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
        $this->validateData();
        $this->validateComponentPricesOrder();
        $this->validateCurrencyOrder();
        $this->validateCommentOrder();
        $this->validateTransactionOrder();
        $this->validateLogOrder();

        $this->unsetSubmitted('update');

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
            $this->setErrorUnavailable('update', $this->translation->text('Order'));
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
        $field = 'payment';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Payment');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $method = $this->payment->get($value);

        if (empty($method['status'])) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'shipping';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Shipping');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $method = $this->shipping->get($value);

        if (empty($method['status'])) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'status';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $statuses = $this->order->getStatuses();

        if (empty($statuses[$value])) {
            $this->setErrorUnavailable($field, $this->translation->text('Status'));
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
        $field = 'shipping_address';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if ($this->isUpdating() && !isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Shipping address');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $address = $this->address->get($value);

        if (empty($address)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'payment_address';

        if ($this->isExcluded($field)) {
            return null;
        }

        $value = $this->getSubmitted($field);

        if (!isset($value) && $this->isUpdating()) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value) && !$this->isError('shipping_address')) {
            $value = $this->getSubmitted('shipping_address');
        }

        $label = $this->translation->text('Payment address');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $address = $this->address->get($value);

        if (empty($address)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted($field, $value);
        return true;
    }

    /**
     * Validates a creator user ID
     * @return boolean|null
     */
    protected function validateCreatorOrder()
    {
        $field = 'creator';
        $value = $this->getSubmitted($field);

        if (!isset($value) && $this->isUpdating()) {
            $this->unsetSubmitted($field);
            return null;
        }

        if (empty($value)) {
            return null;
        }

        $label = $this->translation->text('Creator');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $user = $this->user->get($value);

        if (empty($user)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'total';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Total');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($value) > 10) {
            $this->setErrorLengthRange($field, $label, 0, 10);
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
        $field = 'data.components';
        $components = $this->getSubmitted($field);

        if (empty($components)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Price');

        if (!is_array($components)) {
            $this->setErrorInvalid($field, $label);
            return false;
        }

        foreach ($components as $id => $component) {

            if (!is_numeric($component['price'])) {
                $this->setErrorNumeric("$field.$id", $label);
                continue;
            }

            if (strlen($component['price']) > 10) {
                $this->setErrorLengthRange("$field.$id", $label, 0, 10);
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
        $field = 'currency';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Currency');

        if (empty($value)) {
            $this->setErrorRequired($field, $label);
        }

        $currency = $this->currency->get($value);

        if (empty($currency)) {
            $this->setErrorUnavailable($field, $label);
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
        $field = 'comment';
        $value = $this->getSubmitted($field);

        if (isset($value) && mb_strlen($value) > 65535) {
            $this->setErrorLengthRange($field, $this->translation->text('Comment'), 0, 65535);
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
        $field = 'transaction_id';
        $value = $this->getSubmitted($field);

        if (!isset($value)) {
            $this->unsetSubmitted($field);
            return null;
        }

        $label = $this->translation->text('Transaction');

        if (!is_numeric($value)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (empty($value)) {
            return true;
        }

        $transaction = $this->transaction->get($value);

        if (empty($transaction)) {
            $this->setErrorUnavailable($field, $label);
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
        if (mb_strlen($this->getSubmitted('log')) > 255) {
            $this->setErrorLengthRange('log', $this->translation->text('Log'), 0, 255);
            return false;
        }

        return true;
    }

}
