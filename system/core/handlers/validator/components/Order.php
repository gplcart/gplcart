<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\components;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\Currency as CurrencyModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\Transaction as TransactionModel;
use gplcart\core\handlers\validator\Component as BaseComponentValidator;

/**
 * Provides methods to validate orders to be stored in the database
 */
class Order extends BaseComponentValidator
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

        if ($this->isExcludedField($field)) {
            return null;
        }

        $module_id = $this->getSubmitted($field);
        $label = $this->translation->text('Payment');

        if ($this->isUpdating() && !isset($module_id)) {
            return null;
        }

        if (empty($module_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $method = $this->payment->get($module_id);

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

        if ($this->isExcludedField($field)) {
            return null;
        }

        $module_id = $this->getSubmitted($field);
        $label = $this->translation->text('Shipping');

        if ($this->isUpdating() && !isset($module_id)) {
            return null;
        }

        if (empty($module_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        $method = $this->shipping->get($module_id);

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
        $status = $this->getSubmitted($field);
        $label = $this->translation->text('Status');

        if (!isset($status)) {
            return null;
        }

        $statuses = $this->order->getStatuses();

        if (empty($statuses[$status])) {
            $this->setErrorUnavailable($field, $label);
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

        if ($this->isExcludedField($field)) {
            return null;
        }

        $address_id = $this->getSubmitted($field);
        $label = $this->translation->text('Shipping address');

        if ($this->isUpdating() && !isset($address_id)) {
            return null;
        }

        if (empty($address_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($address_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $address = $this->address->get($address_id);

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

        if ($this->isExcludedField($field)) {
            return null;
        }

        $address_id = $this->getSubmitted($field);
        $label = $this->translation->text('Payment address');

        if (empty($address_id) && !$this->isError('shipping_address')) {
            $address_id = $this->getSubmitted('shipping_address');
        }

        if (empty($address_id)) {
            $this->setErrorRequired($field, $label);
            return false;
        }

        if (!is_numeric($address_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $address = $this->address->get($address_id);

        if (empty($address)) {
            $this->setErrorUnavailable($field, $label);
            return false;
        }

        $this->setSubmitted($field, $address_id);
        return true;
    }

    /**
     * Validates a creator user ID
     * @return boolean|null
     */
    protected function validateCreatorOrder()
    {
        $field = 'creator';
        $creator = $this->getSubmitted($field);
        $label = $this->translation->text('Creator');

        if (empty($creator)) {
            return null;
        }

        if (!is_numeric($creator)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $user = $this->user->get($creator);

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
        $total = $this->getSubmitted($field);
        $label = $this->translation->text('Total');

        if (!isset($total)) {
            return null;
        }

        if (!is_numeric($total)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        if (strlen($total) > 10) {
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
        $label = $this->translation->text('Price');
        $components = $this->getSubmitted('data.components');

        if (empty($components)) {
            return null;
        }

        foreach ($components as $id => $component) {

            if (!is_numeric($component['price'])) {
                $this->setErrorNumeric("data.components.$id", $label);
                continue;
            }

            if (strlen($component['price']) > 10) {
                $this->setErrorLengthRange("data.components.$id", $label, 0, 10);
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
        $code = $this->getSubmitted($field);

        if (!isset($code)) {
            return null;
        }

        $currency = $this->currency->get($code);

        if (empty($currency)) {
            $this->setErrorUnavailable($field, $this->translation->text('Currency'));
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
        $comment = $this->getSubmitted($field);

        if (isset($comment) && mb_strlen($comment) > 65535) {
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
        $label = $this->translation->text('Transaction');
        $transaction_id = $this->getSubmitted($field);

        if (!isset($transaction_id)) {
            return null;
        }

        if (!is_numeric($transaction_id)) {
            $this->setErrorNumeric($field, $label);
            return false;
        }

        $transaction = $this->transaction->get($transaction_id);

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
