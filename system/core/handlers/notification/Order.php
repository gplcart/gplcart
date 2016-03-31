<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\handlers\notification;

use core\Config;
use core\classes\Url;
use core\models\Mail;
use core\models\Store;
use core\models\Language;
use core\models\Order as O;

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
     * Url class instance
     * @var \core\classes\Url $url
     */
    protected $url;

    /**
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;
    
    /**
     * Constructor
     * @param Store $store
     * @param Mail $mail
     * @param Language $language
     * @param O $order
     * @param Url $url
     * @param Config $config
     */
    public function __construct(Store $store, Mail $mail, Language $language, O $order, Url $url, Config $config)
    {
        $this->url = $url;
        $this->mail = $mail;
        $this->store = $store;
        $this->order = $order;
        $this->config = $config;
        $this->language = $language;
    }

    public function status($order)
    {
        
    }

    public function createdAdmin($order)
    {
        
    }

    public function createdCustomer($order)
    {
        $store = $this->store->get($order['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $subject_default = "Order #@order_id at @store";

        $subject_text = $this->config->get('email_subject_order_created_customer', $subject_default);
        $subject_arguments = array('@order_id' => $order['order_id'], '@store' => $store_name);
        $subject = $this->language->text($subject_text, $subject_arguments);

        /*
        $message_default = "Thank you ordering at !store\n\n"
                . "Account status: !status\n\n"
                . "Edit account: !edit\n"
                . "View orders: !order\n"
                . $this->mail->signatureText($options);
         * 
         */
        
        $message_default = "Thank you ordering at !store\n\n"
                . $this->mail->signatureText($options);

        $message_text = $this->config->get('email_message_order_created_customer', $message_default);

        $base = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", '/');

        $message_arguments = array(
            '!store' => $store_name,
            '!edit' => "$base/account/{$user['user_id']}/edit",
            '!order' => "$base/account/{$user['user_id']}",
            //'!status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $message_arguments = array_merge($message_arguments, $this->mail->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        //$to = array($user['email']);
        
        $to = '';
        
        return $this->mail->send($to, array($subject => $message), $options);
    }
    
    /**
     * Returns a text to be shown on the order complete page for a logged in customer
     * @param array $order
     * @return string
     */
    public function completeCustomer($order)
    {
        $default = 'Thank you for your order! Order ID: <a href="!url">!order_id</a>, status: !status';
        $message = $this->config->get('order_complete_message', $default);

        $variables = array(
            '!order_id' => $order['order_id'],
            '!url' => $this->url->get("account/{$order['user_id']}"),
            '!status' => $this->order->getStatusName($order['status'])
        );
        
        return $this->language->text($message, $variables);
    }

    /**
     * Returns a text to be shown on the order complete page for an anonymous
     * @param array $order
     * @return string
     */
    public function completeAnonymous($order)
    {
        $default = 'Thank you for your order! Order ID: !order_id, status: !status';
        $message = $this->config->get('order_complete_message_anonymous', $default);
        
        $variables = array(
            '!order_id' => $order['order_id'],
            '!status' => $this->order->getStatusName($order['status'])
        );
        
        return $this->language->text($message, $variables);
    }

    public function createdAnonymous($order){
        
    }

    public function updatedCustomer($order)
    {
        
    }

}
