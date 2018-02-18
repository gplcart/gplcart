<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Order;
use gplcart\core\models\Payment;
use gplcart\core\models\Shipping;
use gplcart\core\traits\Checkout as CheckoutTrait;

/**
 * Handles incoming requests and outputs data related to checkout complete page
 */
class CheckoutComplete extends Controller
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
     * CheckoutComplete constructor.
     * @param Order $order
     * @param Payment $payment
     * @param Shipping $shipping
     */
    public function __construct(Order $order, Payment $payment, Shipping $shipping)
    {
        parent::__construct();

        $this->order = $order;
        $this->payment = $payment;
        $this->shipping = $shipping;
    }

    /**
     * Page callback
     * Displays the checkout complete page
     * @param int $order_id
     */
    public function checkoutComplete($order_id)
    {
        $this->setOrderCheckoutComplete($order_id);
        $this->controlAccessCheckoutComplete();
        $this->setTitleCheckoutComplete();
        $this->setBreadcrumbCheckoutComplete();

        $this->setData('message', $this->getMessageCheckoutComplete());

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

        $this->setData('rendered', $templates);
    }

    /**
     * Load and set an order from the database
     * @param integer $order_id
     */
    protected function setOrderCheckoutComplete($order_id)
    {
        $this->data_order = $this->order->get($order_id);

        if (empty($this->data_order)) {
            $this->outputHttpStatus(404);
        }

        $this->prepareOrderCheckoutComplete($this->data_order);
    }

    /**
     * Prepare the order data
     * @param array $order
     */
    protected function prepareOrderCheckoutComplete(array &$order)
    {
        $this->setItemTotalFormatted($order, $this->price);
        $this->setItemTotalFormattedNumber($order, $this->price);
    }

    /**
     * Controls access to the checkout complete page
     */
    protected function controlAccessCheckoutComplete()
    {
        if (empty($this->data_order['user_id']) || $this->data_order['user_id'] !== $this->cart_uid) {
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
        return $this->order->getCompleteMessage($this->data_order);
    }

}
