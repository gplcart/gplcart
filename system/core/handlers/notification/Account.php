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

class Account
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

    /**
     * Sends an e-mail to admin when a new customer is registered
     * @param array $user
     * @return boolean
     */
    public function registeredAdmin($user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);

        $default_message = "A new account has been created at !store\n\n"
                . "E-mail: !email\nName: !name\nUser ID: !user_id\nStatus: !status\n\n";

        $subject_text = $this->config->get('email_subject_user_registered_admin', 'New account at !store');
        $subject_arguments = array('!store' => $store['name']);

        $message_text = $this->config->get('email_message_user_registered_admin', $default_message);

        $message_arguments = array(
            '!store' => $store['name'],
            '!email' => $user['email'],
            '!name' => $user['name'],
            '!user_id' => $user['user_id'],
            '!status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $subject = $this->language->text($subject_text, $subject_arguments);
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store['name']);
        $to = array($options['from']);
        
        return $this->mail->send($to, array($subject => $message), $options);
    }

    /**
     * Sends an e-mail to a customer upon registration
     * @param array $user
     * @return boolean
     */
    public function registeredCustomer($user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $subject_default = "Account details for !name at !store";

        $subject_text = $this->config->get('email_subject_user_registered_customer', $subject_default);
        $subject_arguments = array('!name' => $user['name'], '!store' => $store_name);
        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "Thank you for registering at !store\n\n"
                . "Account status: !status\n\n"
                . "Edit account: !edit\n"
                . "View orders: !order\n"
                . $this->mail->signatureText($options);

        $message_text = $this->config->get('email_message_user_registered_customer', $message_default);

        $base = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", '/');

        $message_arguments = array(
            '!store' => $store_name,
            '!edit' => "$base/account/{$user['user_id']}/edit",
            '!order' => "$base/account/{$user['user_id']}",
            '!status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $message_arguments = array_merge($message_arguments, $this->mail->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        $to = array($user['email']);
        return $this->mail->send($to, array($subject => $message), $options);
    }

    /**
     * Sends to a user password reset link
     * @param array $user
     */
    public function resetPassword($user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $subject_default = "Password recovery for !name at !store";
        $subject_text = $this->config->get('email_subject_reset_password', $subject_default);
        $subject_arguments = array('!name' => $user['name'], '!store' => $store_name);
        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "You or someone else requested a new password at !store\n\n"
                . "To get the password please click on the following link:\n"
                . "!link\n\n"
                . "This link expires on !expires and nothing will happen if it's not used\n\n"
                . $this->mail->signatureText($options);

        $message_text = $this->config->get('email_message_reset_password', $message_default);

        $base = rtrim("{$store['scheme']}{$store['domain']}/{$store['basepath']}", '/');

        $message_arguments = array(
            '!store' => $store_name,
            '!expires' => date($this->config->get('date_format', 'd.m.Y H:i'), $user['data']['reset_password']['expires']),
            '!link' => "$base/forgot?" . http_build_query(array(
                'key' => $user['data']['reset_password']['token'],
                'user_id' => $user['user_id']
            )),
        );

        $message_arguments = array_merge($message_arguments, $this->mail->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        $to = array($user['email']);
        return $this->mail->send($to, array($subject => $message), $options);
    }

    /**
     * Sends the password changed message via E-mail
     * @param array $user
     */
    public function changedPassword($user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $subject_default = "Password has been changed for !name at !store";
        $subject_text = $this->config->get('email_subject_changed_password', $subject_default);
        $subject_arguments = array('!name' => $user['name'], '!store' => $store_name);
        $subject = $this->language->text($subject_text, $subject_arguments);

        $message_default = "Your password at !store has been changed\n\n"
                . $this->mail->signatureText($options);

        $message_text = $this->config->get('email_message_changed_password', $message_default);
        $message_arguments = array('!store' => $store_name);

        $message_arguments = array_merge($message_arguments, $this->mail->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        $to = array($user['email']);
        return $this->mail->send($to, array($subject => $message), $options);
    }

}
