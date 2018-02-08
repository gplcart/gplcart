<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

use gplcart\core\handlers\mail\Base as BaseHandler;
use gplcart\core\models\Order as OrderModel;
use gplcart\core\models\Price as PriceModel;

/**
 * Mail handlers related to orders
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
     * Sends an email to admin after a customer created an order
     * @param array $order
     * @return array
     */
    public function createdToAdmin($order)
    {
        $store = $this->store->get($order['store_id']);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);
        $options = array('from' => reset($store['data']['email']));

        $default = (array) $this->store->getDefault(true);
        $url = $this->store->getUrl($default);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/admin/sale/order/{$order['order_id']}",
            '@status' => $this->order->getStatusName($order['status']),
            '@total' => $this->price->format($order['total'], $order['currency']),
        );

        $subject = $this->translation->text('New order #@order_id on @store', $vars);
        $message = $this->translation->text("Order status: @status\r\nTotal: @total\r\nView: @order", $vars);

        return array($options['from'], $subject, $message, $options);
    }

    /**
     * Sends an email to a logged in customer after his order has been created
     * @param array $order
     * @return array
     */
    public function createdToCustomer($order)
    {
        $store = $this->store->get($order['store_id']);
        $url = $this->store->getUrl($store);
        $user = $this->user->get($order['user_id']);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);

        $options = $this->store->getConfig(null, $store);
        $options['from'] = reset($store['data']['email']);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/account/{$order['user_id']}",
            '@status' => $this->order->getStatusName($order['status']),
        );

        $subject = $this->translation->text('Order #@order_id on @store', $vars);
        $message = $this->translation->text("Thank you for ordering on @store\r\n\r\nOrder ID: @order_id\r\nOrder status: @status\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sends an email to a registered customer after his order has been updated
     * @param array $order
     * @return array
     */
    public function updatedToCustomer(array $order)
    {
        $store = $this->store->get($order['store_id']);
        $url = $this->store->getUrl($store);
        $user = $this->user->get($order['user_id']);
        $store_name = $this->store->getTranslation('title', $this->translation->getLangcode(), $store);

        $options = $this->store->getConfig(null, $store);
        $options['from'] = reset($store['data']['email']);

        $vars = array(
            '@store' => $store_name,
            '@order_id' => $order['order_id'],
            '@order' => "$url/account/{$order['user_id']}",
            '@status' => $this->order->getStatusName($order['status']),
        );

        $subject = $this->translation->text('Order #@order_id on @store', $vars);
        $message = $this->translation->text("Your order #@order_id on @store has been updated\r\n\r\nOrder status: @status\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        return array($user['email'], $subject, $message, $options);
    }

}
