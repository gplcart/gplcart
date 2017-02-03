<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\validator\condition;

use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\Language as LanguageModel;

/**
 * Contains methods to validate various order conditions
 */
class Order
{

    /**
     * Language model instance
     * @var \gplcart\core\models\Language $language
     */
    protected $language;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Constructor
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     * @param LanguageModel $language
     */
    public function __construct(PaymentModel $payment, ShippingModel $shipping,
            LanguageModel $language)
    {
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->language = $language;
    }

    /**
     * Validates the shipping method condition
     * @param array $values
     * @return boolean|string
     */
    public function shippingMethod(array $values)
    {
        $exists = array_filter($values, function ($method_id) {
            return (bool) $this->shipping->get($method_id);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Shipping'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

    /**
     * Validates the payment method condition
     * @param array $values
     * @return boolean|string
     */
    public function paymentMethod(array $values)
    {
        $exists = array_filter($values, function ($method_id) {
            return (bool) $this->payment->get($method_id);
        });

        if (count($values) != count($exists)) {
            $vars = array('@name' => $this->language->text('Payment'));
            return $this->language->text('@name is unavailable', $vars);
        }

        return true;
    }

}
