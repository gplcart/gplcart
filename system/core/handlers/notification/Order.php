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
use core\models\Mail;
use core\models\Store;
use core\models\Language;

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
     * Config class instance
     * @var \core\Config $config
     */
    protected $config;

    public function __construct(Store $store, Mail $mail, Language $language, Config $config)
    {
        $this->store = $store;
        $this->mail = $mail;
        $this->language = $language;
        $this->config = $config;
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
    
    public function completeCustomer($order){
        
    }
    
    public function completeAnonymous($order){
        
    }
    
    public function createdAnonymous($order){
        
    }

    public function updatedCustomer($order)
    {
        
    }

}
