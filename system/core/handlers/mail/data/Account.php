<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail\data;

use gplcart\core\handlers\mail\data\Base as BaseHandler;

class Account extends BaseHandler
{

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @param array $user
     * @return boolean
     */
    public function registeredToAdmin($user)
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
        return array($options['from'], $subject, $message, $options);
    }

    /**
     * @param array $user
     * @return boolean
     */
    public function registeredToCustomer($user)
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
                . $this->signatureText($options);

        $message_text = $this->config->get('email_message_user_registered_customer', $message_default);

        $base = $this->store->url($store);

        $message_arguments = array(
            '!store' => $store_name,
            '!edit' => "$base/account/{$user['user_id']}/edit",
            '!order' => "$base/account/{$user['user_id']}",
            '!status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $message_arguments = array_merge($message_arguments, $this->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        return array($user['email'], $subject, $message, $options);
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
                . $this->signatureText($options);

        $message_text = $this->config->get('email_message_reset_password', $message_default);

        $base = $this->store->url($store);

        $date_format = $this->config->get('date_prefix', 'd.m.Y');
        $date_format .= $this->config->get('date_suffix', ' H:i');

        $message_arguments = array(
            '!store' => $store_name,
            '!expires' => date($date_format, $user['data']['reset_password']['expires']),
            '!link' => "$base/forgot?" . http_build_query(array(
                'key' => $user['data']['reset_password']['token'],
                'user_id' => $user['user_id']
            )),
        );

        $message_arguments = array_merge($message_arguments, $this->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        return array($user['email'], $subject, $message, $options);
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
                . $this->signatureText($options);

        $message_text = $this->config->get('email_message_changed_password', $message_default);
        $message_arguments = array('!store' => $store_name);

        $message_arguments = array_merge($message_arguments, $this->signatureVariables($options));
        $message = $this->language->text($message_text, $message_arguments);

        $options['from'] = array(reset($store['data']['email']), $store_name);
        return array($user['email'], $subject, $message, $options);
    }

}
