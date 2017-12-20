<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;
use gplcart\core\traits\Checkout as CheckoutTrait;

/**
 * Handles incoming requests and outputs data related to checkout complete page
 */
class CheckoutComplete extends FrontendController
{

    use CheckoutTrait;

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
     * The current order
     * @var array
     */
    protected $data_order = array();

    /**
     * @param OrderModel $order
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(OrderModel $order, PaymentModel $payment, ShippingModel $shipping)
    {
        parent::__construct();

        $this->order = $order;
        $this->payment = $payment;
        $this->shipping = $shipping;
    }

    /**
     * Displays the checkout complete page
     * @param int $order_id
     */
    public function checkoutComplete($order_id)
    {
        $this->setOrderCheckoutComplete($order_id);
        $this->controlAccessCheckoutComplete();

        $this->setTitleCheckoutComplete();
        $this->setBreadcrumbCheckoutComplete();

        $this->setData('complete_message', $this->getMessageCheckoutComplete());

        $this->setDataTemplatesCheckoutComplete();
        $this->hook->attach('order.complete.page', $this->data_order, $this->order, $this);
        $this->outputCheckoutComplete();
    }

    /**
     * Sets payment/shipping method templates on the checkout complete page
     */
    protected function setDataTemplatesCheckoutComplete()
    {

        $templates = array(
            'payment' => $this->getPaymentMethodTemplate('complete', $this->data_order, $this->payment),
            'shipping' => $this->getShippingMethodTemplate('complete', $this->data_order, $this->shipping)
        );

        $this->setData('complete_templates', $templates);
    }

    /**
     * Load and set an order from the database
     * @param integer $order_id
     * @return array
     */
    protected function setOrderCheckoutComplete($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_order = $this->prepareOrderCheckoutComplete($order);
    }

    /**
     * Prepare the order data
     * @param array $order
     * @return array
     */
    protected function prepareOrderCheckoutComplete(array $order)
    {
        $this->setItemTotalFormatted($order, $this->price);
        $this->setItemTotalFormattedNumber($order, $this->price);
        return $order;
    }

    /**
     * Controls access to the checkout complete page
     */
    protected function controlAccessCheckoutComplete()
    {
        if ($this->data_order['user_id'] !== $this->cart_uid) {
            $this->outputHttpStatus(403);
        }

        if (!$this->order->isPending($this->data_order)) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Sets titles on the checkout complete page
     */
    protected function setTitleCheckoutComplete()
    {
        $text = $this->text('Created order #@num', array('@num' => $this->data_order['order_id']));
        $this->setTitle($text);
    }

    /**
     * Sets breadcrumbs on the checkout complete page
     */
    protected function setBreadcrumbCheckoutComplete()
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Home')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Outputs the checkout complete page
     */
    protected function outputCheckoutComplete()
    {
        $this->output('checkout/complete');
    }

    /**
     * Returns the checkout complete message
     * @return string
     */
    protected function getMessageCheckoutComplete()
    {
        if (is_numeric($this->data_order['user_id'])) {
            $default = $this->text('Thank you for your order! Order ID: @num, status: @status');
            $message = $this->config('order_complete_message', $default);
        } else {
            $default = $this->text('Thank you for your order! Order ID: @num, status: @status');
            $message = $this->config('order_complete_message_anonymous', $default);
        }

        $vars = array(
            '@num' => $this->data_order['order_id'],
            '@status' => $this->order->getStatusName($this->data_order['status'])
        );

        $message = $this->text($message, $vars);
        $this->hook->attach('order.complete.message', $message, $this->data_order, $this);
        return $message;
    }

}
