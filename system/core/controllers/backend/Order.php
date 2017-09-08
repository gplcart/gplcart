<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\controllers\backend\Controller as BackendController;
use gplcart\core\traits\Order as OrderTrait,
    gplcart\core\traits\OrderComponent as OrderComponentTrait;

/**
 * Handles incoming requests and outputs data related to order management
 */
class Order extends BackendController
{

    use OrderTrait,
        OrderComponentTrait;

    /**
     * Code of successfully sent notification
     */
    const NOTIFICATION_SENT = 2;

    /**
     * Code of failed notification
     */
    const NOTIFICATION_ERROR = 1;

    /**
     * Code of disabled notification
     */
    const NOTIFICATION_DISABLED = 0;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

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
     * An array of order data
     * @var array
     */
    protected $data_order = array();

    /**
     * @param OrderModel $order
     * @param AddressModel $address
     * @param PriceModel $price
     * @param PriceRuleModel $pricerule
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(OrderModel $order, AddressModel $address,
            PriceModel $price, PriceRuleModel $pricerule, PaymentModel $payment,
            ShippingModel $shipping)
    {
        parent::__construct();

        $this->order = $order;
        $this->price = $price;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->pricerule = $pricerule;
    }

    /**
     * Displays the order overview page
     * @param integer $order_id
     */
    public function indexOrder($order_id)
    {
        $this->setOrder($order_id);
        $this->submitIndexOrder();

        $this->setTitleIndexOrder();
        $this->setBreadcrumbIndexOrder();

        $this->setData('order', $this->data_order);

        $this->setDataPanelLogsIndexOrder();
        $this->setDataPanelSummaryIndexOrder();
        $this->setDataPanelCommentIndexOrder();
        $this->setDataPanelCustomerIndexOrder();
        $this->setDataPanelComponentsIndexOrder();
        $this->setDataPanelPaymentAddressIndexOrder();
        $this->setDataPanelShippingAddressIndexOrder();

        $this->outputIndexOrder();
    }

    /**
     * Handles a submitted order
     */
    protected function submitIndexOrder()
    {
        if ($this->isPosted('delete')) {
            $this->deleteOrder();
        } else if ($this->isPosted('status') && $this->validateIndexOrder()) {
            $this->updateStatusOrder();
        } else if ($this->isPosted('clone') && $this->validateIndexOrder()) {
            $this->cloneOrder();
        }
    }

    /**
     * Validates a submitted order
     * @return bool
     */
    protected function validateIndexOrder()
    {
        $this->setSubmitted('order');

        return !$this->isError();
    }

    /**
     * Deletes an order
     */
    protected function deleteOrder()
    {
        $this->controlAccess('order_delete');
        if ($this->order->delete($this->data_order['order_id'])) {
            $this->redirect('admin/sale/order', $this->text('Order has been deleted'), 'success');
        }
        $this->redirect('', $this->text('Unable to delete'), 'warning');
    }

    /**
     * Update an order status
     */
    protected function updateStatusOrder()
    {
        $this->controlAccess('order_edit');

        $order_id = $this->data_order['order_id'];
        $submitted = array('status' => $this->getSubmitted('status'));
        $this->order->update($order_id, $submitted);

        $submitted['notify'] = $this->setNotificationUpdateOrder($order_id);
        $this->logUpdateStatusOrder($submitted);

        $messages = $this->getMassagesUpdateOrder();
        list($severity, $text) = $messages[$submitted['notify']];
        $this->redirect('', $this->text($text), $severity);
    }

    /**
     * Returns an array of messages for updated orders
     * @return array
     */
    protected function getMassagesUpdateOrder()
    {
        return array(
            static::NOTIFICATION_DISABLED => array('success', 'Order has been updated'),
            static::NOTIFICATION_ERROR => array('warning', 'Order has been updated, notification has not been sent to customer'),
            static::NOTIFICATION_SENT => array('success', 'Order has been updated, notification has been sent to customer')
        );
    }

    /**
     * Log the status updated event
     * @param array $submitted
     * @return boolean
     */
    protected function logUpdateStatusOrder(array $submitted)
    {
        if ($this->data_order['status'] === $submitted['status']) {
            return false;
        }

        $vars = array('@status' => $submitted['status']);
        $text = $this->text('Update order status to @status', $vars);

        $log = array(
            'text' => $text,
            'user_id' => $this->uid,
            'order_id' => $this->data_order['order_id']
        );

        return (bool) $this->order->addLog($log);
    }

    /**
     * Notify a customer when an order has been updated
     * @param integer $order_id
     * @return integer
     */
    protected function setNotificationUpdateOrder($order_id)
    {
        if (!$this->config('order_update_notify_customer', 1)) {
            return static::NOTIFICATION_DISABLED;
        }

        $order = $this->order->get($order_id);
        if ($this->order->setNotificationUpdated($order) === true) {
            return static::NOTIFICATION_SENT;
        }
        return static::NOTIFICATION_ERROR;
    }

    /**
     * Copy an order
     */
    protected function cloneOrder()
    {
        $this->controlAccess('order_edit');
        $this->controlAccess('order_add');

        $this->createTempCartOrder();

        $update = array('status' => $this->order->getStatusCanceled());
        $this->order->update($this->data_order['order_id'], $update);

        $this->logUpdateStatusOrder($update);

        $this->redirect("checkout/clone/{$this->data_order['order_id']}");
    }

    /**
     * Creates temporary cart for the current admin
     */
    protected function createTempCartOrder()
    {
        // Remove all previous cart items
        $this->cart->clear($this->uid);

        // Copy order's cart items
        foreach ($this->data_order['cart'] as $item) {
            unset($item['cart_id']);
            $item['user_id'] = $this->uid;
            $item['order_id'] = 0;
            $this->cart->add($item);
        }
    }

    /**
     * Returns a total log records found for the order
     * @return integer
     */
    protected function getTotalLogOrder()
    {
        $options = array(
            'count' => true,
            'order_id' => $this->data_order['order_id']
        );

        return (int) $this->order->getLogList($options);
    }

    /**
     * Returns an array of log records for the order
     * @param array $limit
     * @return array
     */
    protected function getListLogOrder(array $limit)
    {
        $options = array(
            'limit' => $limit,
            'order_id' => $this->data_order['order_id']
        );

        return (array) $this->order->getLogList($options);
    }

    /**
     * Render and output the order overview page
     */
    protected function outputIndexOrder()
    {
        $this->output('sale/order/order');
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleIndexOrder()
    {
        $vars = array('@order_id' => $this->data_order['order_id']);
        $this->setTitle($this->text('Order #@order_id', $vars));
    }

    /**
     * Sets bread crumbs on the order overview page
     */
    protected function setBreadcrumbIndexOrder()
    {
        $this->setBreadcrumbHome();

        $breadcrumb = array(
            'text' => $this->text('Orders'),
            'url' => $this->url('admin/sale/order')
        );

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Returns an order
     * @param integer $order_id
     */
    protected function setOrder($order_id)
    {
        if (is_numeric($order_id)) {

            $order = $this->order->get($order_id);

            if (empty($order)) {
                $this->outputHttpStatus(404);
            }

            $this->order->setViewed($order);
            $this->data_order = $this->prepareOrder($order);
        }
    }

    /**
     * Prepare an array of order data
     * @param array $order
     * @return array
     */
    protected function prepareOrder(array &$order)
    {
        $this->prepareOrderTotalTrait($order, $this->price);
        $this->prepareOrderAddressTrait($order, $this->address);
        $this->prepareOrderStoreTrait($order, $this->store, $this);
        $this->prepareOrderStatusTrait($order, $this->order, $this);
        $this->prepareOrderPaymentTrait($order, $this->payment, $this);
        $this->prepareOrderShippingTrait($order, $this->shipping, $this);

        $order['user'] = array();
        if (is_numeric($order['user_id'])) {
            $order['user'] = $this->user->get($order['user_id']);
        }

        $options = array('count' => true, 'user_id' => $order['user_id']);
        $order['user']['total_orders'] = (int) $this->order->getList($options);

        $order['customer'] = $this->text('Anonymous');
        $order['creator_formatted'] = $this->text('Customer');

        if (empty($order['creator'])) {
            return $order;
        }

        $order['creator_formatted'] = $this->text('Unknown');
        $user = $this->user->get($order['creator']);

        if (isset($user['user_id'])) {
            $order['creator_formatted'] = "{$user['name']} ({$user['email']})";
        }

        return $order;
    }

    /**
     * Adds the order logs panel on the order overview page
     */
    protected function setDataPanelLogsIndexOrder()
    {
        $total = $this->getTotalLogOrder();
        $max = $this->config('order_log_limit', 5);

        $data = array(
            'order' => $this->data_order,
            'pager' => $this->getPager($total, null, $max),
            'items' => $this->getListLogOrder($this->getPagerLimit())
        );

        $this->setData('pane_log', $this->render('sale/order/panes/log', $data));
    }

    /**
     * Adds the summary panel on the order overview page
     */
    protected function setDataPanelSummaryIndexOrder()
    {
        $data = array('order' => $this->data_order, 'statuses' => $this->order->getStatuses());
        $this->setData('pane_summary', $this->render('sale/order/panes/summary', $data));
    }

    /**
     * Adds the order comment panel on the order overview page
     */
    protected function setDataPanelCommentIndexOrder()
    {
        $data = array('order' => $this->data_order);
        $this->setData('pane_comment', $this->render('sale/order/panes/comment', $data));
    }

    /**
     * Adds the customer panel on the order overview page
     */
    protected function setDataPanelCustomerIndexOrder()
    {
        $data = array('order' => $this->data_order);
        $this->setData('pane_customer', $this->render('sale/order/panes/customer', $data));
    }

    /**
     * Adds the order components panel on the order overview page
     */
    protected function setDataPanelComponentsIndexOrder()
    {
        $this->prepareOrderComponentCartTrait($this->data_order, $this, $this->price);
        $this->prepareOrderComponentPriceRuleTrait($this->data_order, $this, $this->price, $this->pricerule);
        $this->prepareOrderComponentPaymentMethodTrait($this->data_order, $this, $this->price, $this->payment, $this->order);
        $this->prepareOrderComponentShippingMethodTrait($this->data_order, $this, $this->price, $this->shipping, $this->order);

        ksort($this->data_order['data']['components']);

        $data = array('components' => $this->data_order['data']['components'], 'order' => $this->data_order);
        $this->setData('pane_components', $this->render('sale/order/panes/components', $data));
    }

    /**
     * Adds the shipping address panel on the order overview page
     */
    protected function setDataPanelShippingAddressIndexOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/shipping_address', $data);
        $this->setData('pane_shipping_address', $html);
    }

    /**
     * Adds the payment address panel on the order overview page
     */
    protected function setDataPanelPaymentAddressIndexOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/payment_address', $data);
        $this->setData('pane_payment_address', $html);
    }

    /**
     * Displays the order list page
     */
    public function listOrder()
    {
        $this->actionListOrder();

        $this->setTitleListOrder();
        $this->setBreadcrumbListOrder();

        $this->setData('statuses', $this->order->getStatuses());

        $this->setFilterListOrder();
        $this->setTotalListOrder();
        $this->setPagerLimit();

        $this->setData('orders', $this->getListOrder());

        $this->outputListOrder();
    }

    /**
     * Set filter on the order list page
     */
    protected function setFilterListOrder()
    {
        $allowed = array('store_id', 'order_id', 'status', 'created',
            'creator', 'user_id', 'total', 'currency', 'customer');

        $this->setFilter($allowed);
    }

    /**
     * Sets a total number of orders on the order list page
     */
    protected function setTotalListOrder()
    {
        $query = $this->query_filter;
        $query['count'] = true;
        $this->total = (int) $this->order->getList($query);
    }

    /**
     * Applies an action to the selected orders
     */
    protected function actionListOrder()
    {
        list($selected, $action, $value) = $this->getPostedAction();

        if (empty($action)) {
            return null;
        }

        $deleted = $updated = 0;
        $failed_notifications = array();

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('order_edit')) {

                $updated = (bool) $this->order->update($id, array('status' => $value));

                if ($updated && $this->setNotificationUpdateOrder($id) == static::NOTIFICATION_ERROR) {
                    $failed_notifications[] = $id;
                }

                $updated++;
            }

            if ($action === 'delete' && $this->access('order_delete')) {
                $deleted += (int) $this->order->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Updated %num items', array('%num' => $updated));
            $this->setMessage($message, 'success', true);
        }

        if (!empty($failed_notifications)) {
            $vars = array('@list' => implode(',', $failed_notifications));
            $message = $this->text('Failed to notify customers in orders: @list', $vars);
            $this->setMessage($message, 'warning', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Deleted %num items', array('%num' => $deleted));
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets titles on the order list page
     */
    protected function setTitleListOrder()
    {
        $this->setTitle($this->text('Orders'));
    }

    /**
     * Sets breadcrumbs on the order list page
     */
    protected function setBreadcrumbListOrder()
    {
        $this->setBreadcrumbHome();
    }

    /**
     * Render and output the order list page
     */
    protected function outputListOrder()
    {
        $this->output('sale/order/list');
    }

    /**
     * Returns an array of orders
     * @return array
     */
    protected function getListOrder()
    {
        $query = $this->query_filter;
        $query['limit'] = $this->limit;
        $orders = (array) $this->order->getList($query);

        return $this->prepareListOrder($orders);
    }

    /**
     * Prepare an array of orders
     * @param array $orders
     * @return array
     */
    protected function prepareListOrder(array $orders)
    {
        foreach ($orders as &$order) {
            $this->prepareOrderNewTrait($order, $this->order);
            $this->prepareOrderTotalTrait($order, $this->price);
        }

        return $orders;
    }

}
