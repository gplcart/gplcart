<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail;

use gplcart\core\helpers\Url as UrlHelper;
use gplcart\core\models\Price as PriceModel,
    gplcart\core\models\Order as OrderModel;
use gplcart\core\handlers\mail\Base as BaseHandler;

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
     * Url class instance
     * @var \gplcart\core\helpers\Url $url
     */
    protected $url;

    /**
     * Constructor
     * @param OrderModel $order
     * @param PriceModel $price
     * @param UrlHelper $url
     */
    public function __construct(OrderModel $order, PriceModel $price,
            UrlHelper $url)
    {
        parent::__construct();

        $this->url = $url;
        $this->price = $price;
        $this->order = $order;
    }

    /**
     * Sends an email to admin after a customer created an order
     * @param array $order
     * @return boolean
     */
    public function createdToAdmin($order)
    {
        $store = $this->store->get($order['store_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $options = $this->store->config(null, $store);
        $admin_email = $this->store->email($store);
        $options['from'] = array($admin_email, $store_name);

        $subject_default = "New order #!order_id at !store";
        $subject_text = $this->config->get('email_subject_order_created_admin', $subject_default);

        $subject_arguments = array(
            '!order_id' => $order['order_id'],
            '!store' => $store_name);

        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "Order status: !status\n"
                . "Total: !total\n"
                . "View: !order\n";

        $message_text = $this->config->get('email_message_order_created_admin', $message_default);

        $default = (array) $this->store->getDefault(true);
        $url = $this->store->url($default);

        $message_arguments = array(
            '!store' => $store_name,
            '!total' => $this->price->format($order['total'], $order['currency']),
            '!order' => "$url/admin/sale/order/{$order['order_id']}",
            '!status' => $this->order->getStatusName($order['status']),
        );

        $message = $this->language->text($message_text, $message_arguments);
        return array($admin_email, $subject, $message, $options);
    }

    /**
     * Sends an email to a logged in customer after the order has been created
     * @param array $order
     * @return boolean
     */
    public function createdToCustomer($order)
    {
        $store = $this->store->get($order['store_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $options = $this->store->config(null, $store);
        $options['from'] = array($this->store->email($store), $store_name);

        $subject_default = "Order #!order_id at !store";
        $subject_text = $this->config->get('email_subject_order_created_customer', $subject_default);

        $subject_arguments = array(
            '!order_id' => $order['order_id'],
            '!store' => $store_name);

        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "Thank you for ordering at !store\n\n"
                . "Order status: !status\n"
                . "View orders: !order\n"
                . $this->signatureText($options);

        $message_text = $this->config->get('email_message_order_created_customer', $message_default);
        $url = $this->store->url($store);

        $message_arguments = array(
            '!store' => $store_name,
            '!order' => "$url/account/{$order['user_id']}",
            '!status' => $this->order->getStatusName($order['status']),
        );

        $message_arguments = array_merge($message_arguments, $this->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);
        return array($order['user_email'], $subject, $message, $options);
    }

    /**
     * Sends an email to a registered customer after the order has been updated
     * @param array $order
     * @return boolean
     */
    public function updatedToCustomer(array $order)
    {
        $store = $this->store->get($order['store_id']);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $options = $this->store->config(null, $store);
        $options['from'] = array($this->store->email($store), $store_name);

        $subject_default = "Order #!order_id at !store";
        $subject_text = $this->config->get('email_subject_order_updated_customer', $subject_default);

        $subject_arguments = array('!order_id' => $order['order_id'], '!store' => $store_name);
        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "Your order at !store has been updated\n\n"
                . "Order status: !status\n"
                . "View orders: !order\n"
                . $this->signatureText($options);

        $message_text = $this->config->get('email_message_order_updated_customer', $message_default);
        $url = $this->store->url($store);

        $message_arguments = array(
            '!store' => $store_name,
            '!order' => "$url/account/{$order['user_id']}",
            '!status' => $this->order->getStatusName($order['status']),
        );

        $message_arguments = array_merge($message_arguments, $this->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);
        return array($order['user_email'], $subject, $message, $options);
    }

}
