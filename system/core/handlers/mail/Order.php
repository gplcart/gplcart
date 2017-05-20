<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Order as OrderModel;
use gplcart\core\handlers\mail\Base as BaseHandler;

/**
 * Mail data handlers related to orders
 */
class Order extends BaseHandler
{

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * Price model instance
     * @var \gplcart\core\models\Price $price
     */
    protected $price;

    /**
     * @param OrderModel $order
     * @param PriceModel $price
     */
    public function __construct(OrderModel $order, PriceModel $price)
    {
        parent::__construct();

        $this->price = $price;
        $this->order = $order;
    }

    /**
     * Sends an email to an admin after a customer created an order
     * @param array $order
     * @return boolean
     */
    public function createdToAdmin($order)
    {
        $store = $this->store->get($order['store_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $options = array('from' => $this->store->email($store));

        $default = (array) $this->store->getDefault(true);
        $url = $this->store->url($default);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/admin/sale/order/{$order['order_id']}",
            '@status' => $this->order->getStatusName($order['status']),
            '@total' => $this->price->format($order['total'], $order['currency']),
        );

        $subject = $this->language->text('New order #@order_id on @store', $vars);
        $message = $this->language->text("Order status: @status\r\nTotal: @total\r\nView: @order", $vars);

        return array($options['from'], $subject, $message, $options);
    }

    /**
     * Sends an email to a logged in customer after his order has been created
     * @param array $order
     * @return boolean
     */
    public function createdToCustomer($order)
    {
        $store = $this->store->get($order['store_id']);
        $url = $this->store->url($store);
        $user = $this->user->get($order['user_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $options = $this->store->config(null, $store);
        $options['from'] = $this->store->email($store);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/account/{$order['user_id']}",
            '@status' => $this->order->getStatusName($order['status']),
        );

        $subject = $this->language->text('Order #@order_id on @store', $vars);
        $message = $this->language->text("Thank you for ordering on @store\r\n\r\nOrder ID: @order_id\r\nOrder status: @status\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sends an email to a registered customer after his order has been updated
     * @param array $order
     * @return boolean
     */
    public function updatedToCustomer(array $order)
    {
        $store = $this->store->get($order['store_id']);
        $url = $this->store->url($store);
        $user = $this->user->get($order['user_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $options = $this->store->config(null, $store);
        $options['from'] = $this->store->email($store);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/account/{$order['user_id']}",
            '@status' => $this->order->getStatusName($order['status']),
        );

        $subject = $this->language->text('Order #@order_id on @store', $vars);
        $message = $this->language->text("Your order #@order_id on @store has been updated\r\n\r\nOrder status: @status\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        return array($user['email'], $subject, $message, $options);
    }

}
