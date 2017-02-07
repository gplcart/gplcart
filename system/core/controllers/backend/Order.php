<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\backend;

use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Price as PriceModel;
use gplcart\core\models\Address as AddressModel;
use gplcart\core\models\Payment as PaymentModel;
use gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\models\PriceRule as PriceRuleModel;
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
     * Shipping addaress
     * @var array
     */
    protected $data_shipping_address = array();

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
     * This method required by trait \gplcart\core\traits\ControllerOrder
     * @param string $name
     * @return object
     * @see \gplcart\core\traits\ControllerOrder
     */
    protected function getInstanceTrait($name)
    {
        return $this->prop($name);
    }

    /**
     * Displays the order overview page
     * @param integer $order_id
     */
    public function viewOrder($order_id)
    {
        $this->setOrder($order_id);
        $this->setShippingAddressOrder();

        $this->submitOrder();

        $this->setTitleViewOrder();
        $this->setBreadcrumbViewOrder();

        $this->setData('order', $this->data_order);

        $this->setDataLogsOrder();
        $this->setDataSummaryOrder();
        $this->setDataCommentOrder();
        $this->setDataCustomerOrder();
        $this->setDataComponentsOrder();
        $this->setDataShippingAddressOrder();

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

        $message = $this->text('Order has not been deleted');
        $this->redirect('', $message, 'warning');
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
     * 
     */
    protected function cloneOrder()
    {
        $this->controlAccess('order_edit');
        $this->controlAccess('order_add');

        $update = array('status' => $this->order->getCanceledStatus());
        $this->order->update($this->data_order['order_id'], $update);
        $this->logOrder($update);
        
        $this->redirect("checkout/clone/{$this->data_order['order_id']}");
    }

    /**
     * Sets JS settings on the view order page
     */
    protected function setJsSettingsViewOrder()
    {
        $translated = $this->address->getTranslated($this->data_shipping_address);

        $map = array(
            'key' => $this->config('gapi_browser_key', ''),
            'address' => $this->address->getGeocodeQuery($translated)
        );

        $this->setJsSettings('map', $map);
    }

    /**
     * Adds order logs pane on the order overview page
     */
    protected function setDataLogsOrder()
    {
        $total = $this->getTotalLogOrder();

        $max = $this->config('order_log_limit', 10);
        $limit = $this->setPager($total, null, $max);
        $items = $this->getListLogOrder($limit);

        $data = array(
            'items' => $items,
            'pager' => $this->getPager(),
            'order' => $this->data_order,
        );

        $html = $this->render('sale/order/panes/log', $data);
        $this->setData('pane_log', $html);
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

        $this->data_order = $this->prepareOrder($order);
        $this->order->setViewed($this->data_order);

        return $this->data_order;
    }

    /**
     * Returns an array of shipping address for the current order
     * @return array
     */
    protected function setShippingAddressOrder()
    {
        $address = $this->address->get($this->data_order['shipping_address']);
        return $this->data_shipping_address = $address;
    }

    /**
     * Adds some extra data to the order array
     * @param array $order
     * @return array
     */
    protected function prepareOrder(array $order)
    {
        $store = $this->store->get($order['store_id']);

        if (!empty($store['name'])) {
            $order['store_name'] = $store['name'];
        }

        $statuses = $this->order->getStatuses();

        if (empty($statuses[$order['status']])) {
            $order['status_name'] = $this->text('Unknown');
        } else {
            $order['status_name'] = $statuses[$order['status']];
        }

        $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);

        $this->prepareOrderUserOrder($order);
        $this->prepareOrderComponentsOrder($order);

        return $order;
    }

    /**
     * Sets extra data to order components
     * @param array $order
     * @return array
     */
    protected function prepareOrderComponentsOrder(array &$order)
    {
        foreach ($order['data']['components']['cart'] as $sku => $price) {
            if ($order['cart'][$sku]['product_store_id'] != $order['store_id']) {
                $order['cart'][$sku]['product_status'] = 0;
            }
            $order['cart'][$sku]['price_formatted'] = $this->price->format($price, $order['currency']);
        }

        return $order;
    }

    /**
     * Sets extra user data
     * @param array $order
     * @return array
     */
    protected function prepareOrderUserOrder(array &$order)
    {
        $order['customer'] = $this->text('Anonymous');
        $order['creator_formatted'] = $this->text('Customer');

        if (!empty($order['creator'])) {
            $order['creator_formatted'] = $this->text('Unknown');
            $user = $this->user->get($order['user_id']);
            if (isset($user['user_id'])) {
                $order['creator_formatted'] = "{$user['name']} ({$user['email']})";
            }
        }

        return $order;
    }

    /**
     * Sets summary pane on the order overview page
     */
    protected function setDataSummaryOrder()
    {
        $data = array(
            'order' => $this->data_order,
            'statuses' => $this->order->getStatuses()
        );

        $html = $this->render('sale/order/panes/summary', $data);
        $this->setData('pane_summary', $html);
    }

    /**
     * Sets order comment pane on the order overview page
     */
    protected function setDataCommentOrder()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('sale/order/panes/comment', $data);
        $this->setData('pane_comment', $html);
    }

    /**
     * Sets customer pane on the order overview page
     */
    protected function setDataCustomerOrder()
    {
        $user_id = $this->data_order['user_id'];
        $user = is_numeric($user_id) ? $this->user->get($user_id) : array();

        $data = array(
            'user' => $user,
            'order' => $this->data_order,
            'placed' => $this->getTotalPlacedOrder($user_id),
        );

        $html = $this->render('sale/order/panes/customer', $data);
        $this->setData('pane_customer', $html);
    }

    /**
     * Returns a number of orders placed by a given user
     * @param integer|string $user_id
     * @return integer
     */
    protected function getTotalPlacedOrder($user_id)
    {
        $options = array('count' => true, 'user_id' => $user_id);
        return (int) $this->order->getList($options);
    }

    /**
     * Sets order components pane on the order overview page
     */
    protected function setDataComponentsOrder()
    {
        $templates = 'sale/order/panes/components';
        $components = $this->prepareOrderComponentsTrait($this->data_order, $templates);

        $data = array(
            'components' => $components,
            'order' => $this->data_order
        );

        $html = $this->render('sale/order/panes/components', $data);
        $this->setData('pane_components', $html);
    }

    /**
     * Returns rendered shipping address pane
     */
    protected function setDataShippingAddressOrder()
    {
        $data = array(
            'order' => $this->data_order,
            'address' => $this->data_shipping_address,
            'items' => $this->address->getTranslated($this->data_shipping_address, true)
        );

        $html = $this->render('sale/order/panes/shipping_address', $data);
        $this->setData('pane_shipping_address', $html);
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
            $vars = array('@id' => implode(',', $failed_notifications));
            $message = $this->text('Failed to notify customers in orders: @id', $vars);
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
