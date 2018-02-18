<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Address;
use gplcart\core\models\Order;
use gplcart\core\models\Payment;
use gplcart\core\models\PriceRule;
use gplcart\core\models\Shipping;
use gplcart\core\traits\ItemOrder;

/**
 * Handles incoming requests and outputs data related to customer orders
 */
class AccountOrder extends Controller
{

    use ItemOrder;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $price_rule
     */
    protected $price_rule;

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
     * An array of user data
     * @var array
     */
    protected $data_user = array();

    /**
     * An array of order data
     * @var array
     */
    protected $data_order = array();

    /**
     * AccountOrder constructor.
     * @param Address $address
     * @param Order $order
     * @param PriceRule $price_rule
     * @param Payment $payment
     * @param Shipping $shipping
     */
    public function __construct(Address $address, Order $order, PriceRule $price_rule, Payment $payment, Shipping $shipping)
    {
        parent::__construct();

        $this->order = $order;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->price_rule = $price_rule;
    }

    /**
     * Page callback
     * Displays the order overview page
     * @param integer $user_id
     * @param integer $order_id
     */
    public function accountOrder($user_id, $order_id)
    {
        $this->setUserAccountOrder($user_id);
        $this->setOrderAccountOrder($order_id);
        $this->setTitleAccountOrder();
        $this->setBreadcrumbAccountOrder();

        $this->setData('user', $this->data_user);

        $this->setDataPanelSummaryAccountOrder();
        $this->setDataPanelComponentsAccountOrder();
        $this->setDataPanelPaymentAddressAccountOrder();
        $this->setDataPanelShippingAddressAccountOrder();

        $this->outputAccountOrder();
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleAccountOrder()
    {
        $this->setTitle($this->text('Order #@order_id', array('@order_id' => $this->data_order['order_id'])));
    }

    /**
     * Sets breadcrumbs on the order overview page
     */
    protected function setBreadcrumbAccountOrder()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url("account/{$this->data_user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the order overview page
     */
    protected function outputAccountOrder()
    {
        $this->output('account/order/order');
    }

    /**
     * Sets an order data
     * @param integer $order_id
     */
    protected function setOrderAccountOrder($order_id)
    {
        $this->data_order = $this->order->get($order_id);

        if (empty($this->data_order)) {
            $this->outputHttpStatus(404);
        }

        $this->prepareOrderAccountOrder($this->data_order);
    }

    /**
     * Prepare an array of order data
     * @param array $order
     */
    protected function prepareOrderAccountOrder(array &$order)
    {
        $this->setItemTotalFormatted($order, $this->price);
        $this->setItemOrderAddress($order, $this->address);
        $this->setItemOrderStoreName($order, $this->store);
        $this->setItemOrderStatusName($order, $this->order);
        $this->setItemOrderPaymentName($order, $this->payment);
        $this->setItemOrderShippingName($order, $this->shipping);
    }

    /**
     * Prepare order components
     * @param array $order
     */
    protected function prepareOrderComponentsAccountOrder(array &$order)
    {
        $this->setItemOrderCartComponent($order, $this->price);
        $this->setItemOrderPriceRuleComponent($order, $this->price, $this->price_rule);
        $this->setItemOrderPaymentComponent($order, $this->price, $this->payment, $this->order);
        $this->setItemOrderShippingComponent($order, $this->price, $this->shipping, $this->order);

        ksort($order['data']['components']);
    }

    /**
     * Sets a user data
     * @param integer $user_id
     */
    protected function setUserAccountOrder($user_id)
    {
        $this->data_user = $this->user->get($user_id);

        if (empty($this->data_user)) {
            $this->outputHttpStatus(404);
        }

        if (empty($this->data_user['status']) && !$this->access('user')) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Sets the summary panel on the order overview page
     */
    protected function setDataPanelSummaryAccountOrder()
    {
        $this->setData('summary', $this->render('account/order/summary', array('order' => $this->data_order)));
    }

    /**
     * Sets the order components panel on the order overview page
     */
    protected function setDataPanelComponentsAccountOrder()
    {
        $order = $this->data_order;
        $this->prepareOrderComponentsAccountOrder($order);

        $data = array(
            'order' => $order,
            'components' => $order['data']['components']
        );

        $this->setData('components', $this->render('account/order/components', $data));
    }

    /**
     * Sets the shipping address panel on the order overview page
     */
    protected function setDataPanelShippingAddressAccountOrder()
    {
        $html = $this->render('account/order/shipping_address', array('order' => $this->data_order));
        $this->setData('shipping_address', $html);
    }

    /**
     * Sets payment address panel on the order overview page
     */
    protected function setDataPanelPaymentAddressAccountOrder()
    {
        $html = $this->render('account/order/payment_address', array('order' => $this->data_order));
        $this->setData('payment_address', $html);
    }

}
