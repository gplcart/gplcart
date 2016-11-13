<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\validator;

use core\models\Order as ModelsOrder;
use core\models\Payment as ModelsPayment;
use core\models\Address as ModelsAddress;
use core\models\Currency as ModelsCurrency;
use core\models\Shipping as ModelsShipping;
use core\models\Transaction as ModelsTransaction;
use core\handlers\validator\Base as BaseValidator;

/**
 * Provides methods to validate orders to be stored in the database
 */
class Order extends BaseValidator
{

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Payment model instance
     * @var \core\models\Payment $payment
     */
    protected $payment;

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Currency model instance
     * @var \core\models\Currency $currency
     */
    protected $currency;

    /**
     * Transaction model instance
     * @var \core\models\Transaction $transaction
     */
    protected $transaction;

    /**
     * Constructor
     * @param ModelsOrder $order
     * @param ModelsPayment $payment
     * @param ModelsShipping $shipping
     * @param ModelsAddress $address
     * @param ModelsCurrency $currency
     * @param ModelsTransaction $transaction
     */
    public function __construct(ModelsOrder $order, ModelsPayment $payment,
            ModelsShipping $shipping, ModelsAddress $address,
            ModelsCurrency $currency, ModelsTransaction $transaction)
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
     */
    public function order(array &$submitted, array $options = array())
    {
        $this->validateOrder($submitted);
        $this->validateStoreId($submitted);
        $this->validatePaymentOrder($submitted);
        $this->validateShippingOrder($submitted);
        $this->validateStatusOrder($submitted);
        $this->validateAddressOrder($submitted);
        $this->validateUserCartId($submitted);
        $this->validateCreatorOrder($submitted);
        $this->validateTotalOrder($submitted);
        $this->validateCurrencyOrder($submitted);
        $this->validateCommentOrder($submitted);
        $this->validateTransactionOrder($submitted);
        $this->validateLogOrder($submitted);

        return empty($this->errors) ? true : $this->errors;
    }

    /**
     * Validates an order to be updated
     * @param array $submitted
     * @return boolean
     */
    protected function validateOrder(array &$submitted)
    {
        if (!empty($submitted['update']) && is_numeric($submitted['update'])) {

            $data = $this->order->get($submitted['update']);

            if (empty($data)) {
                $this->errors['update'] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text('Order')));
                return false;
            }

            $submitted['update'] = $data;
        }

        return true;
    }

    /**
     * Validates a payment method
     * @param array $submitted
     * @return boolean|null
     */
    protected function validatePaymentOrder(array &$submitted)
    {
        if (empty($submitted['payment'])) {
            return null;
        }

        $method = $this->payment->get($submitted['payment']);

        if (empty($method)) {
            $this->errors['payment'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Payment')));
            return false;
        }

        return true;
    }

    /**
     * Validates a shipping method
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateShippingOrder(array &$submitted)
    {
        if (empty($submitted['shipping'])) {
            return null;
        }

        $method = $this->shipping->get($submitted['shipping']);

        if (empty($method)) {
            $this->errors['shipping'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Shipping')));
            return false;
        }

        return true;
    }

    /**
     * Validates a status
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateStatusOrder(array &$submitted)
    {
        if (empty($submitted['status'])) {
            return null;
        }

        $statuses = $this->order->getStatuses();

        if (isset($statuses[$submitted['status']])) {
            return true;
        }

        $this->errors['status'] = $this->language->text('Object @name does not exist', array(
            '@name' => $this->language->text('Status')));
        return false;
    }

    /**
     * Validates order addresses
     * @param array $submitted
     * @return boolean
     */
    protected function validateAddressOrder(array &$submitted)
    {
        $types = $this->address->getTypes();

        $error = false;
        foreach ($types as $type) {

            $field = $type . '_address';

            if (!isset($submitted[$field])) {
                continue;
            }

            $name = ucfirst(str_replace('_', ' ', $submitted[$field]));

            if (!is_numeric($submitted[$field])) {
                $error = true;
                $options = array('@field' => $this->language->text($name));
                $this->errors[$field] = $this->language->text('@field must be numeric', $options);
                continue;
            }

            if (empty($submitted[$field])) {
                continue;
            }

            $address = $this->address->get($submitted[$field]);

            if (empty($address)) {
                $error = true;
                $this->errors[$field] = $this->language->text('Object @name does not exist', array(
                    '@name' => $this->language->text($name)));
            }
        }

        return !$error;
    }

    /**
     * Validates a creator user ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCreatorOrder(array &$submitted)
    {
        if (!empty($submitted['update']) && !isset($submitted['creator'])) {
            return null;
        }

        if (empty($submitted['creator'])) {
            $this->errors['creator'] = $this->language->text('@field is required', array(
                '@field' => $this->language->text('Creator')
            ));
            return false;
        }

        if (!is_numeric($submitted['creator'])) {
            $options = array('@field' => $this->language->text('Creator'));
            $this->errors['creator'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $user = $this->user->get($submitted['creator']);

        if (empty($user)) {
            $this->errors['creator'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Creator')));
            return false;
        }

        return true;
    }

    /**
     * Validates order total
     * @param array $submitted
     * @return boolean
     */
    protected function validateTotalOrder(array $submitted)
    {
        if (!isset($submitted['total'])) {
            return null;
        }

        if (!is_numeric($submitted['total'])) {
            $options = array('@field' => $this->language->text('Total'));
            $this->errors['total'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        if (strlen($submitted['total']) > 10) {
            $options = array('@max' => 10, '@field' => $this->language->text('Total'));
            $this->errors['total'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a currency code
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateCurrencyOrder(array $submitted)
    {
        if (!isset($submitted['currency'])) {
            return null;
        }

        $currency = $this->currency->get($submitted['currency']);

        if (empty($currency)) {
            $this->errors['currency'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Currency')));
            return false;
        }

        return true;
    }

    /**
     * Validates an order comment
     * @param array $submitted
     * @return boolean
     */
    protected function validateCommentOrder(array &$submitted)
    {
        if (isset($submitted['comment']) && mb_strlen($submitted['comment']) > 65535) {
            $options = array('@max' => 65535, '@field' => $this->language->text('Comment'));
            $this->errors['comment'] = $this->language->text('@field must not be longer than @max characters', $options);
            return false;
        }

        return true;
    }

    /**
     * Validates a transaction ID
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateTransactionOrder(array &$submitted)
    {
        if (!isset($submitted['transaction_id'])) {
            return null;
        }

        if (!is_numeric($submitted['transaction_id'])) {
            $options = array('@field' => $this->language->text('Transaction'));
            $this->errors['transaction_id'] = $this->language->text('@field must be numeric', $options);
            return false;
        }

        $transaction = $this->transaction->get($submitted['transaction_id']);

        if (empty($transaction)) {
            $this->errors['transaction_id'] = $this->language->text('Object @name does not exist', array(
                '@name' => $this->language->text('Transaction')));
            return false;
        }

        return true;
    }

    /**
     * Validates a log message
     * @param array $submitted
     * @return boolean|null
     */
    protected function validateLogOrder(array &$submitted)
    {
        if (empty($submitted['update'])) {
            return null;
        }

        if (empty($submitted['log']) || mb_strlen($submitted['log']) > 255) {
            $options = array('@min' => 1, '@max' => 255, '@field' => $this->language->text('Log'));
            $this->errors['log'] = $this->language->text('@field must be @min - @max characters long', $options);
            return false;
        }

        return true;
    }

}
