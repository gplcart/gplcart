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

/**
 * Provides data to the view and interprets user actions related to orders
 */
class Order extends BackendController
{

    use \gplcart\core\traits\ControllerOrder;

    const NOTIFICATION_SENT = 2;
    const NOTIFICATION_ERROR = 1;
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
     * The current order
     * @var array
     */
    protected $data_order = array();

    /**
     * Constructor
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
    public function viewOrder($order_id)
    {
        $this->setOrder($order_id);

        $this->submitOrder();

        $this->setTitleViewOrder();
        $this->setBreadcrumbViewOrder();

        $this->setData('order', $this->data_order);

        $this->setDataPaneLogsOrder();
        $this->setDataPaneSummaryOrder();
        $this->setDataPaneCommentOrder();
        $this->setDataPaneCustomerOrder();
        $this->setDataPaneComponentsOrder();
        $this->setDataPanePaymentAddressOrder();
        $this->setDataPaneShippingAddressOrder();

        $this->setJsSettingsViewOrder();
        $this->outputViewOrder();
    }

    /**
     * Handles submitted data
     * @return null
     */
    protected function submitOrder()
    {
        if ($this->isPosted('delete')) {
            $this->deleteOrder();
            return null;
        }

        if (!$this->validateOrder()) {
            return null;
        }

        if ($this->isPosted('status')) {
            $this->updateOrder();
        } else if ($this->isPosted('clone')) {
            $this->cloneOrder();
        }
    }

    /**
     * Deletes the current order
     */
    protected function deleteOrder()
    {
        $this->controlAccess('order_delete');

        if ($this->order->delete($this->data_order['order_id'])) {
            $message = $this->text('Order has been deleted');
            $this->redirect('admin/sale/order', $message, 'success');
        }

        $this->redirect('', $this->text('Unable to delete this order'), 'warning');
    }

    /**
     * Validates a submitted order
     * @return bool
     */
    protected function validateOrder()
    {
        $this->setSubmitted('order');
        $this->setSubmitted('update', $this->data_order);

        $this->validate('order');
        return !$this->isError();
    }

    /**
     * Update order status
     */
    protected function updateOrder()
    {
        $this->controlAccess('order_edit');

        $submitted = $this->getSubmitted();

        $order_id = $this->data_order['order_id'];
        $this->order->update($order_id, $submitted);

        $submitted['notify'] = $this->setNotificationOrder($order_id);

        $this->logOrder($submitted);

        $messages = array(
            self::NOTIFICATION_DISABLED => array('success', 'Order has been updated'),
            self::NOTIFICATION_ERROR => array('warning', 'Order has been updated, notification has not been sent to customer'),
            self::NOTIFICATION_SENT => array('success', 'Order has been updated, notification has been sent to customer')
        );

        list($severity, $text) = $messages[$submitted['notify']];
        $this->redirect('', $this->text($text), $severity);
    }

    /**
     * Adds order update log message
     * @param array $submitted
     * @return boolean
     */
    protected function logOrder(array $submitted)
    {
        if ($this->data_order['status'] === $submitted['status']) {
            return false;
        }

        $vars = array('@status' => $submitted['status']);
        $text = $this->text('Update order status to @status', $vars);

        $log = array(
            'text' => $text,
            'user_id' => $this->uid,
            'order_id' => $this->data_order['order_id'],
            'data' => array('notify' => $submitted['notify'])
        );

        return (bool) $this->order->addLog($log);
    }

    /**
     * Notify a customer when an order has been updated
     * @param integer $order_id
     * @return integer
     */
    protected function setNotificationOrder($order_id)
    {
        if (!$this->config('order_update_notify_customer', 1)) {
            return self::NOTIFICATION_DISABLED;
        }

        $order = $this->order->get($order_id);

        if ($this->order->setNotificationUpdated($order) === true) {
            return self::NOTIFICATION_SENT;
        }

        return self::NOTIFICATION_ERROR;
    }

    /**
     * Copies an order
     */
    protected function cloneOrder()
    {
        $this->controlAccess('order_edit');
        $this->controlAccess('order_add');

        $update = array('status' => $this->order->getStatusCanceled());
        $this->order->update($this->data_order['order_id'], $update);
        $this->logOrder($update);

        $this->redirect("checkout/clone/{$this->data_order['order_id']}");
    }

    /**
     * Sets JS settings on the view order page
     */
    protected function setJsSettingsViewOrder()
    {
        $map = array('key' => $this->config('gapi_browser_key', ''));

        foreach ($this->data_order['address'] as $type => $address) {
            $translated = $this->address->getTranslated($address);
            $map['address'][$type] = $this->address->getGeocodeQuery($translated);
        }

        $this->setJsSettings('map', $map);
    }

    /**
     * Returns a total logs found for the order
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
     * Renders order overview page templates
     */
    protected function outputViewOrder()
    {
        $this->output('sale/order/order');
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleViewOrder()
    {
        $vars = array('@order_id' => $this->data_order['order_id']);
        $title = $this->text('Order #@order_id', $vars);
        $this->setTitle($title);
    }

    /**
     * Sets breadcrumbs on the order overview page
     */
    protected function setBreadcrumbViewOrder()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'text' => $this->text('Dashboard'),
            'url' => $this->url('admin')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url('admin/sale/order')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Returns an order
     * @param integer $order_id
     * @return array
     */
    protected function setOrder($order_id)
    {
        if (!is_numeric($order_id)) {
            return array();
        }

        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        $this->order->setViewed($order);
        return $this->data_order = $this->prepareOrder($order);
    }

    /**
     * Sets extra user data
     * @param array $order
     * @return array
     */
    protected function prepareOrder(array &$order)
    {
        $this->prepareOrderTrait($this, $order);

        $order['user'] = array();
        if (is_numeric($order['user_id'])) {
            $order['user'] = $this->user->get($order['user_id']);
        }

        $options = array('count' => true, 'user_id' => $order['user_id']);
        $order['user']['total_orders'] = (int) $this->order->getList($options);

        $order['customer'] = $this->text('Anonymous');
        $order['creator_formatted'] = $this->text('Customer');

        if (!empty($order['creator'])) {
            $order['creator_formatted'] = $this->text('Unknown');
            $user = $this->user->get($order['creator']);
            if (isset($user['user_id'])) {
                $order['creator_formatted'] = "{$user['name']} ({$user['email']})";
            }
        }

        return $order;
    }

    /**
     * Adds order logs pane on the order overview page
     */
    protected function setDataPaneLogsOrder()
    {
        $max = $this->config('order_log_limit', 10);

        $total = $this->getTotalLogOrder();
        $limit = $this->setPager($total, null, $max);

        $data = array(
            'order' => $this->data_order,
            'pager' => $this->getPager(),
            'items' => $this->getListLogOrder($limit)
        );

        $html = $this->render('sale/order/panes/log', $data);
        $this->setData('pane_log', $html);
    }

    /**
     * Sets summary pane on the order overview page
     */
    protected function setDataPaneSummaryOrder()
    {
        $data = array('order' => $this->data_order, 'statuses' => $this->order->getStatuses());
        $html = $this->render('sale/order/panes/summary', $data);
        $this->setData('pane_summary', $html);
    }

    /**
     * Sets order comment pane on the order overview page
     */
    protected function setDataPaneCommentOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/comment', $data);
        $this->setData('pane_comment', $html);
    }

    /**
     * Sets customer pane on the order overview page
     */
    protected function setDataPaneCustomerOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/customer', $data);
        $this->setData('pane_customer', $html);
    }

    /**
     * Sets order components pane on the order overview page
     */
    protected function setDataPaneComponentsOrder()
    {
        $templates = 'sale/order/panes/components';
        $components = $this->prepareOrderComponentsTrait($this, $this->data_order, $templates);

        $data = array('components' => $components, 'order' => $this->data_order);
        $html = $this->render('sale/order/panes/components', $data);
        $this->setData('pane_components', $html);
    }

    /**
     * Sets shipping address pane on the order overview page
     */
    protected function setDataPaneShippingAddressOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/shipping_address', $data);
        $this->setData('pane_shipping_address', $html);
    }

    /**
     * Sets payment address pane on the order overview page
     */
    protected function setDataPanePaymentAddressOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/payment_address', $data);
        $this->setData('pane_payment_address', $html);
    }

    /**
     * Displays the order admin overview page
     */
    public function listOrder()
    {
        $this->actionOrder();

        $this->setTitleListOrder();
        $this->setBreadcrumbListOrder();

        $this->setData('stores', $this->store->getNames());
        $this->setData('statuses', $this->order->getStatuses());

        $query = $this->getFilterQuery();

        $allowed = array('store_id', 'order_id', 'status', 'created',
            'creator', 'user_id', 'total', 'currency');

        $this->setFilter($allowed, $query);

        $total = $this->getTotalOrder($query);
        $limit = $this->setPager($total, $query);
        $this->setData('orders', $this->getListOrder($limit, $query));

        $this->outputListOrder();
    }

    /**
     * Applies an action to the selected orders
     * @return null
     */
    protected function actionOrder()
    {
        $action = (string) $this->request->post('action');

        if (empty($action)) {
            return null;
        }

        $value = (string) $this->request->post('value');
        $selected = (array) $this->request->post('selected', array());

        $deleted = $updated = 0;
        $failed_notifications = array();

        foreach ($selected as $id) {

            if ($action === 'status' && $this->access('order_edit')//
                    && $this->order->update($id, array('status' => $value))) {
                if ($this->setNotificationOrder($id) == self::NOTIFICATION_ERROR) {
                    $failed_notifications[] = $id;
                }
                $updated++;
            }

            if ($action === 'delete' && $this->access('order_delete')) {
                $deleted += (int) $this->order->delete($id);
            }
        }

        if ($updated > 0) {
            $message = $this->text('Orders have been updated');
            $this->setMessage($message, 'success', true);
        }

        if (!empty($failed_notifications)) {
            $vars = array('@list' => implode(',', $failed_notifications));
            $message = $this->text('Failed to notify customers in orders: @list', $vars);
            $this->setMessage($message, 'warning', true);
        }

        if ($deleted > 0) {
            $message = $this->text('Orders have been deleted');
            $this->setMessage($message, 'success', true);
        }
    }

    /**
     * Sets titles on the orders overview page
     */
    protected function setTitleListOrder()
    {
        $this->setTitle($this->text('Orders'));
    }

    /**
     * Sets breadcrumbs on the orders overview page
     */
    protected function setBreadcrumbListOrder()
    {
        $breadcrumb = array(
            'url' => $this->url('admin'),
            'text' => $this->text('Dashboard'));

        $this->setBreadcrumb($breadcrumb);
    }

    /**
     * Renders overview orders page templates
     */
    protected function outputListOrder()
    {
        $this->output('sale/order/list');
    }

    /**
     * Returns total number of orders for pages
     * @param array $query
     * @return integer
     */
    protected function getTotalOrder(array $query)
    {
        $query['count'] = true;
        return (int) $this->order->getList($query);
    }

    /**
     * Returns an array of orders
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListOrder($limit, array $query)
    {
        $query['limit'] = $limit;
        $orders = (array) $this->order->getList($query);
        return $this->prepareListOrder($orders);
    }

    /**
     * Modifies an array of orders
     * @param array $orders
     * @return array
     */
    protected function prepareListOrder(array $orders)
    {
        foreach ($orders as &$order) {
            $order['is_new'] = $this->order->isNew($order);
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);
        }

        return $orders;
    }

}
