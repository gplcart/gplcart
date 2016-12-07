<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\handlers\mail;

use core\Config;
use core\helpers\Url;
use core\models\Mail as MailModel;
use core\models\Store as StoreModel;
use core\models\Price as PriceModel;
use core\models\Order as OrderModel;
use core\models\Language as LanguageModel;

class Order
{

    /**
     * Store model instance
     * @var \core\models\Store $store
     */
    protected $store;

    /**
     * Mail model instance
     * @var \core\models\Mail $mail
     */
    protected $mail;

    /**
     * Language model instance
     * @var \core\models\Language $language
     */
    protected $language;

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Url class instance
     * @var \core\helpers\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    /**
     * Constructor
     * @param StoreModel $store
     * @param MailModel $mail
     * @param LanguageModel $language
     * @param OrderModel $order
     * @param PriceModel $price
     * @param Url $url
     * @param Config $config
     */
    public function __construct(StoreModel $store, MailModel $mail,
            LanguageModel $language, OrderModel $order, PriceModel $price,
            Url $url, Config $config)
    {
        $this->url = $url;
        $this->mail = $mail;
        $this->store = $store;
        $this->price = $price;
        $this->order = $order;
        $this->config = $config;
        $this->language = $language;
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
        $url = $this->store->url($this->store->getDefault(true));

        $message_arguments = array(
            '!store' => $store_name,
            '!total' => $this->price->format($order['total'], $order['currency']),
            '!order' => "$url/admin/sale/order/{$order['order_id']}",
            '!status' => $this->order->getStatusName($order['status']),
        );

        $message = $this->language->text($message_text, $message_arguments);

        return $this->mail->send(array($admin_email), array($subject => $message), $options);
    }

    /**
     * Sends an email to the logged in customer after he/she created an order
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
                . $this->mail->signatureText($options);

        $message_text = $this->config->get('email_message_order_created_customer', $message_default);
        $url = $this->store->url($store);

        $message_arguments = array(
            '!store' => $store_name,
            '!order' => "$url/account/{$order['user_id']}",
            '!status' => $this->order->getStatusName($order['status']),
        );

        $message_arguments = array_merge($message_arguments, $this->mail->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        return $this->mail->send(array($order['user_email']), array($subject => $message), $options);
    }

}
