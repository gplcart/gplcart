<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\handlers\mail\data;

use gplcart\core\handlers\mail\data\Base as BaseHandler;

/**
 * Mail data handlers related to user accouts
 */
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
     * Sent to an admin when a user has been registered
     * @param array $user
     * @return boolean
     */
    public function registeredToAdmin(array $user)
    {
        $store = $this->store->get($user['store_id']);

        $vars = array(
            '@name' => $user['name'],
            '@store' => $store['name'],
            '@email' => $user['email'],
            '@user_id' => $user['user_id'],
            '@status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $subject = $this->language->text('New account on @store', $vars);
        $message = $this->language->text("A new account has been created on @store\r\n\r\nE-mail: @email\r\nName: @name\r\nUser ID: @user_id\r\nStatus: @status", $vars);

        $options = array('from' => $this->store->config('email.0', $store));
        return array($options['from'], $subject, $message, $options);
    }

    /**
     * Sent to a user when his account has been created
     * @param array $user
     * @return boolean
     */
    public function registeredToCustomer(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $base = $this->store->url($store);

        $vars = array(
            '@store' => $store_name,
            '@name' => $user['name'],
            '@order' => "$base/account/{$user['user_id']}",
            '@edit' => "$base/account/{$user['user_id']}/edit",
            '@status' => empty($user['status']) ? $this->language->text('Inactive') : $this->language->text('Active')
        );

        $subject = $this->language->text('Account details for @name on @store', $vars);
        $message = $this->language->text("Thank you for registering on @store\r\n\r\nAccount status: @status\r\n\r\nEdit account: @edit\r\nView orders: @order", $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->config('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sent when a user wants to reset his password
     * @param array $user
     */
    public function resetPassword(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);
        $base = $this->store->url($store);

        $date_format = $this->config->get('date_prefix', 'd.m.Y');
        $date_format .= $this->config->get('date_suffix', ' H:i');

        $vars = array(
            '@name' => $user['name'],
            '@store' => $store_name,
            '@expires' => date($date_format, $user['data']['reset_password']['expires']),
            '@link' => "$base/forgot?" . http_build_query(array('key' => $user['data']['reset_password']['token'], 'user_id' => $user['user_id'])),
        );

        $subject = $this->language->text('Password recovery for @name on @store', $vars);
        $message = $this->language->text("You or someone else requested a new password on @store\r\n\r\nTo get the password please click on the following link:\r\n@link\r\n\r\nThis link expires on @expires and nothing will happen if it's not used", $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->config('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

    /**
     * Sent to a user whose password has been changed
     * @param array $user
     */
    public function changedPassword(array $user)
    {
        $store = $this->store->get($user['store_id']);
        $options = $this->store->config(null, $store);
        $store_name = $this->store->getTranslation('title', $this->language->current(), $store);

        $vars = array('@store' => $store_name, '@name' => $user['name']);
        $subject = $this->language->text('Password has been changed for @name on @store', $vars);

        $message = $this->language->text('Your password on @store has been changed', $vars);
        $message .= $this->getSignature($options);

        $options['from'] = $this->store->config('email.0', $store);
        return array($user['email'], $subject, $message, $options);
    }

}
