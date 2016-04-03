<?php

/**
 * @package GPL Cart core
 * @version $Id$
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */

namespace core\controllers;

use core\Controller;
use core\classes\Tool;
use core\models\State;
use core\models\Order;
use core\models\Price;
use core\models\Product;
use core\models\Address;
use core\models\Country;
use core\models\Bookmark;
use core\models\UserRole;
use core\models\Notification;

class Account extends Controller
{

    /**
     * Address model instance
     * @var \core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \core\models\State $state
     */
    protected $state;

    /**
     * Mail model instance
     * @var \core\models\Mail $mail
     */
    protected $mail;

    /**
     * Order model instance
     * @var \core\models\Order $order
     */
    protected $order;

    /**
     * User role model instance
     * @var \core\models\UserRole $role
     */
    protected $role;

    /**
     * Price model instance
     * @var \core\models\Price $price
     */
    protected $price;

    /**
     * Bookmark model instance
     * @var \core\models\Bookmark $bookmark
     */
    protected $bookmark;

    /**
     * Notification model instance
     * @var \core\models\Notification $notification
     */
    protected $notification;

    /**
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Constructor
     * @param Address $address
     * @param Country $country
     * @param State $state
     * @param Order $order
     * @param Price $price
     * @param Bookmark $bookmark
     * @param Notification $notification
     * @param UserRole $role
     * @param Product $product
     */
    public function __construct(Address $address, Country $country,
                                State $state, Order $order, Price $price,
                                Bookmark $bookmark, Notification $notification,
                                UserRole $role, Product $product)
    {
        parent::__construct();

        $this->role = $role;
        $this->state = $state;
        $this->order = $order;
        $this->price = $price;
        $this->product = $product;
        $this->country = $country;
        $this->address = $address;
        $this->bookmark = $bookmark;
        $this->notification = $notification;
    }

    /**
     * Displays the customer account page
     * @param integer $user_id
     */
    public function account($user_id)
    {
        $user = $this->getUser($user_id);

        $query = $this->getFilterQuery();
        $default_limit = $this->config->get('account_order_limit', 10);
        $limit = $this->setPager($this->getTotalOrders($user_id), $query, $default_limit);

        $filters = array('order_id', 'created', 'total', 'status');
        $this->setFilter($filters, $query);

        $this->data['user'] = $user;
        $this->data['orders'] = $this->getOrders($user_id, $limit, $query);

        $this->setTitleAccount();
        $this->outputAccount();
    }

    /**
     * Displays the customer edit account page
     * @param integer $user_id
     */
    public function edit($user_id)
    {
        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->outputError(404);
        }

        // No access for blocked customers, allow only admins
        if (empty($user['status']) && !$this->access('user_edit')) {
            $this->outputError(404);
        }

        // Only superadmin can edit its own account
        if ($this->user->isSuperadmin($user_id) && !$this->user->isSuperadmin()) {
            $this->outputError(403);
        }

        $this->data['user'] = $user;
        $this->data['roles'] = $this->role->getList();
        $this->data['stores'] = $this->store->getNames();

        if ($this->request->post('delete')) {
            $this->controlAccess('user_delete');
            if ($this->user->delete($user_id)) {
                $this->redirect('admin/user', $this->text('User %name has been deleted', array(
                            '%name' => $user['name'])), 'success');
            }

            $this->redirect('', $this->text('Unable to delete user %name. The most probable reason - it is used by one or more orders', array(
                        '%name' => $user['name'])), 'danger');
        }

        if ($this->request->post('save')) {
            //$this->controlAccess('user_edit');
            $submitted = $this->request->post('user', array(), 'raw');
            $this->validateUser($submitted, $user);

            if ($this->formErrors()) {
                $this->data['user'] = $submitted + array('user_id' => $user_id);
            } else {
                $this->user->update($user_id, $submitted);
                $this->redirect('', $this->text('Account has been updated'), 'success');
            }
        }

        $this->setTitle($this->text('Edit account'), false);

        $this->output('account/edit');
    }

    /**
     * Displays the addresses overview page
     * @param integer $user_id
     */
    public function addresses($user_id)
    {
        $user = $this->getUser($user_id);
        $address_id = $this->request->get('delete');
        
        if(!empty($address_id)){
           $this->deleteAddress($address_id);
        }

        $this->data['user'] = $user;
        $this->data['can_add'] = $this->address->canAdd($user_id);
        $this->data['addresses'] = $this->address->getTranslatedList($user_id);

        $this->setTitleAddresses();
        $this->outputAddresses();
    }
    
    /**
     * Deletes an address
     * @param integer $address_id
     */
    protected function deleteAddress($address_id)
    {
        $result = $this->address->delete($address_id);

        $message_type = 'success';
        $message = $this->text('Address has been deleted');

        if (empty($result)) {
            $message_type = 'warning';
            $message = $this->text('Address has not been deleted. The most probable reason - it is used in your orders');
        }

        $this->redirect('', $message, $message_type);
    }

    /**
     * Sets titles on the addresses overview page
     */
    protected function setTitleAddresses()
    {
        $this->setTitle($this->text('Addresses'), false);
    }

    /**
     * Renders the addresses overview page
     */
    protected function outputAddresses()
    {
        $this->output('account/address/list');
    }

    /**
     * Displays edit address form
     * @param integer $user_id
     * @param integer $address_id
     */
    public function editAddress($user_id, $address_id = null)
    {
        $user = $this->getUser($user_id);
        $address = $this->getAddress($address_id);

        $this->data['user'] = $user;
        $this->data['address'] = $address;

        if ($this->request->post('save')) {
            $this->submitAddress($user, $address);
        }

        $country = $this->data['address']['country'];
        
        $this->data['countries'] = $this->getCountryNames();
        $this->data['format'] = $this->getCountryFormat($country);
        $this->data['states'] = $this->getCountryStates($country);

        $this->setTitleEditAddress();
        $this->outputEditAddress();
    }

    /**
     * Sets titles on the edit address page
     */
    protected function setTitleEditAddress()
    {
        $this->setTitle($this->text('Add new address'), false);
    }

    /**
     * Renders the edit address page
     */
    protected function outputEditAddress()
    {
        $this->output('account/address/edit');
    }

    /**
     * Returns an address
     * @param integer $address_id
     * @return array
     */
    protected function getAddress($address_id)
    {
        $address = array(
            'country' => $this->country->getDefault()
        );
        
        if (is_numeric($address_id)) {
            $address = $this->address->get($address_id);
            if (empty($address)) {
                $this->outputError(404);
            }
        }

        return $address;
    }

    /**
     * Returns an array of states for a given country code
     * @param string $country
     * @return array
     */
    protected function getCountryStates($country)
    {
        return $this->state->getList(array('country' => $country, 'status' => 1));
    }

    /**
     * Returns an array of the coutry format data
     * @param string $country
     * @return array
     */
    protected function getCountryFormat($country)
    {
        return $this->country->getFormat($country);
    }

    /**
     * Returns an array of country names
     * @return array
     */
    protected function getCountryNames()
    {
        return $this->country->getNames(true);
    }

    /**
     * Saves a user address
     * @param array $user
     * @return null
     */
    protected function submitAddress($user)
    {
        $this->submitted = $this->request->post('address', array());
        
        $this->validateAddress();

        if ($this->formErrors()) {
            $this->data['address'] = $this->submitted;
            return;
        }
        
        $address = $this->submitted + array('user_id' => $user['user_id']);
        $result = $this->addAddress($address);
        
        $message_type = 'success';
        $redirect = "account/{$user['user_id']}/address";
        $message = $this->text('New address has been added');
        
        if(empty($result)){
            $redirect = '';
            $message_type = 'warning';
            $message = $this->text('Address has not been added');
        }
        
        $this->redirect($redirect, $message, $message_type);
    }
    
    /**
     * Adds an address
     * @param array $address
     * @return integer
     */
    protected function addAddress($address)
    {
        $result = $this->address->add($address);
        $this->address->reduceLimit($address['user_id']);
        return $result;
    }
    
    /**
     * 
     * @param type $user_id
     * @return type
     */
    protected function getAddresses($user_id)
    {
        return $this->address->getList(array('user_id' => $user_id));
    }

    /**
     * Displays the login page
     */
    public function login()
    {
        if ($this->uid) {
            $this->url->redirect("account/{$this->uid}");
        }

        if ($this->request->post('login')) {
            $this->controlSpam('login');
            $submitted = $this->request->post('user', array(), 'raw');
            $result = $this->user->login($submitted['email'], $submitted['password']);

            if (!empty($result)) {
                $this->redirect($result['redirect'], $result['message'], $result['message_type']);
            }

            $this->data['user'] = $submitted;
            $this->setMessage($this->text('Invalid E-mail and/or password'), 'danger');
        }

        $this->setTitle($this->text('Login'));
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
        $this->output('login');
    }

    /**
     * Displays the user registration page
     */
    public function register()
    {
        if ($this->uid && !$this->access('user_add')) {
            $this->url->redirect("account/{$this->uid}");
        }

        if ($this->request->post('register')) {
            $this->controlSpam('register');
            $submitted = $this->request->post('user', array(), 'raw');
            $this->validateUser($submitted, array());

            if ($this->formErrors()) {
                $this->data['user'] = $submitted;
            } else {
                $this->registerUser($submitted);
            }
        }

        $this->data['roles'] = $this->role->getList();
        $this->data['stores'] = $this->store->getNames();
        $this->data['min_password_length'] = $this->config->get('user_password_min_length', 8);
        $this->data['max_password_length'] = $this->config->get('user_password_max_length', 255);

        $this->setTitle($this->text('Register'));
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
        $this->output('register');
    }

    /**
     * Displays the user forgotten password page
     */
    public function forgot()
    {
        if ($this->uid) {
            $this->url->redirect("account/{$this->uid}");
        }

        // Check password reset URL
        $recoverable_user = $this->getRecoverableUser();
        $this->data['recoverable_user'] = $recoverable_user;

        if ($recoverable_user === false) {
            // Reset password link expired or invalid
            $this->redirect('forgot');
        }

        $submitted = $this->request->post('user', array(), 'raw');

        if ($submitted) {
            $this->controlSpam('forgot');
            $this->validateForgot($submitted, $recoverable_user);

            if ($this->formErrors()) {
                $this->data['user'] = $submitted;
            } else {
                $this->restorePassword($submitted);
            }
        }

        $this->data['min_password_length'] = $this->config->get('user_password_min_length', 8);
        $this->data['max_password_length'] = $this->config->get('user_password_max_length', 255);

        $this->setTitle($this->text('Reset password'));
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
        $this->output('forgot');
    }

    /**
     * Logs out a user
     */
    public function logout()
    {
        $result = $this->user->logout();

        if (!empty($result)) {
            $this->redirect($result['redirect'], $result['message'], $result['message_type']);
        }

        $this->redirect('/');
    }

    /**
     * Sets titles on the account page
     */
    protected function setTitleAccount()
    {
        $this->setTitle($this->text('Orders'), false);
    }

    /**
     * Renders the account page templates
     */
    protected function outputAccount()
    {
        $this->output('account/account');
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function getUser($user_id)
    {
        $user = $this->user->get($user_id);

        if (empty($user['status'])) {
            $this->outputError(404);
        }

        return $user;
    }

    /**
     * Returns an array of orders for the customer
     * @param integer $user_id
     * @return array
     */
    protected function getOrders($user_id, $limit, $query)
    {
        $query += array('sort' => 'created', 'order' => 'desc', 'limit' => $limit);
        $query['user_id'] = $user_id;

        $orders = $this->order->getList($query);
        return $this->prepareOrders($orders);
    }

    /**
     * Returns a number of total orders for the customer
     * @param integer $user_id
     * @param array $query
     * @return type
     */
    protected function getTotalOrders($user_id)
    {
        return $this->order->getList(array('count' => true, 'user_id' => $user_id));
    }

    /**
     * Modifies an array of orders before rendering
     * @param array $orders
     * @return array
     */
    protected function prepareOrders($orders)
    {
        foreach ($orders as &$order) {

            $address_id = $order['shipping_address'];
            $components = $this->order->getComponents($order);
            $address = $this->address->getTranslated($this->address->get($address_id), true);

            $order['rendered'] = $this->render(
                    'account/order', array(
                        'order' => $order,
                        'components' => $components,
                        'shipping_address' => $address));
        }

        return $orders;
    }

    /**
     * Validates user data
     * @param array $data
     * @param array $user
     * @return null
     */
    protected function validateUser(&$data, $user = array())
    {
        // Registration
        if (empty($user['user_id']) && !$this->uid) {
            $data['status'] = $this->config->get('user_registration_status', 1);
            $data['store_id'] = $this->store_id;
        }

        $check_old_password = false;

        if (isset($data['email']) && filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $check_email_exists = true;
            if (isset($user['email']) && ($data['email'] === $user['email'])) {
                $check_email_exists = false;
            }

            if ($check_email_exists && $this->user->getByEmail($data['email'])) {
                $this->data['form_errors']['email'] = $this->text('Please provide another E-mail');
                return;
            }

            $check_old_password = $check_email_exists;
        } else {
            $this->data['form_errors']['email'] = $this->text('Invalid E-mail');
            return;
        }

        if (empty($user['user_id']) && empty($data['name'])) {
            $data['name'] = strtok($data['email'], '@');
        }

        if (empty($data['name']) || mb_strlen($data['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => 1, '%max' => 255));
        }

        if (empty($user['user_id']) && empty($data['password'])) {
            $this->data['form_errors']['password'] = $this->text('Required field');
            return;
        }

        if (!empty($data['password']) && isset($user['user_id'])) {
            $check_old_password = true;
            $this->validatePassword($data['password']);
        }

        if (isset($this->data['form_errors'])) {
            return;
        }

        if (!$check_old_password || $this->access('user_edit')) {
            return;
        }

        if (empty($data['password_old']) || empty($user['hash'])) {
            $this->data['form_errors']['password_old'] = $this->text('The specified old password does not match the current password');
            return;
        }

        if (!Tool::hashEquals($user['hash'], Tool::hash($data['password_old'], $user['hash'], false))) {
            $this->data['form_errors']['password_old'] = $this->text('The specified old password does not match the current password');
        }
    }

    /**
     * Checks password requirements
     * @param string $password
     */
    protected function validatePassword($password)
    {
        $password_length = mb_strlen($password);
        $limits = $this->user->getPasswordLength();

        if (($limits['min'] <= $password_length) && ($password_length <= $limits['max'])) {
            return true;
        }

        $this->data['form_errors']['password'] = $this->language->text('Password must be %min - %max characters long', array(
            '%min' => $limits['min'], '%max' => $limits['max']));
        return false;
    }

    /**
     * Validates a submitted address
     * @param array $submitted
     */
    protected function validateAddress()
    {
        $this->submitted['status'] = !empty($this->submitted['status']);
        
        foreach ($this->country->getFormat($this->submitted['country'], true) as $field => $info) {
            if (!empty($info['required']) && (empty($this->submitted[$field]) || mb_strlen($this->submitted[$field]) > 255)) {
                $this->data['form_errors'][$field] = $this->text('Content must be %min - %max characters long', array(
                    '%min' => 1, '%max' => 255));
            }
        }
    }

    /**
     * Registers a user and redirects to a certain URL
     * @param array $submitted
     */
    protected function registerUser($submitted)
    {
        $submitted['user_id'] = $this->user->add($submitted);

        // Registerd by an admin
        if ($this->access('user_add')) {
            if ($submitted['notify']) {
                $this->notification->set('user_registered_customer', array($submitted));
            }
            $this->redirect('admin/user', $this->text('User has been added'), 'success');
        }

        // Log the event
        $log = array(
            'message' => 'User %email has been registered',
            'variables' => array('%email' => $submitted['email']));

        $this->logger->log('register', $log); // TODO: move to model
        // Send an e-mail to the customer
        if ($this->config->get('user_registration_email_customer', 1)) {
            $this->notification->set('user_registered_customer', array($submitted));
        }

        // Send an e-mail to admin
        if ($this->config->get('user_registration_email_admin', 1)) {
            $this->notification->set('user_registered_admin', array($submitted));
        }

        $this->session->setMessage($this->text('Your account has been created'), 'success');

        if (!$this->config->get('user_registration_login', 1) || !$this->config->get('user_registration_status', 1)) {
            $this->url->redirect('/');
        }

        $result = $this->user->login($submitted['email'], $submitted['password']);
        $this->redirect($result['redirect'], $result['message'], $result['message_type']);
    }

    /**
     * Returns a user from the current reset password URL
     * @return boolean|array
     */
    protected function getRecoverableUser()
    {
        $token = $this->request->get('key');
        $user_id = $this->request->get('user_id');

        // Data unavailable, exit
        if (empty($token) || empty($user_id)) {
            return;
        }

        $user = $this->user->get($user_id);

        // User blocked or not found
        if (empty($user['status'])) {
            return;
        }

        $data = $user['data'];

        // No recovery data is set
        if (empty($data['reset_password'])) {
            return false;
        }

        // Invalid token
        if (!Tool::hashEquals($data['reset_password']['token'], $token)) {
            return false;
        }

        // Expired
        if ($data['reset_password']['expires'] < GC_TIME) {
            return false;
        }

        return $user;
    }

    /**
     * Validates the forgot password form values
     * @param array $submitted
     * @param mixed $recoverable_user
     * @return null
     */
    protected function validateForgot(&$submitted, $recoverable_user)
    {
        if (isset($submitted['email'])) {
            $user = $this->user->getByEmail($submitted['email']);
            if (empty($user['status'])) {
                $this->data['form_errors']['email'] = $this->text('Please provide another E-mail');
                return;
            }

            $submitted['user'] = $user;
            return;
        }

        if (isset($submitted['password'])) {
            $this->validatePassword($submitted['password']);
            $submitted['user'] = $recoverable_user;
        }

        return;
    }

    /**
     * Either sends a reset password link or changes a password
     * @param array $submitted
     * @return null
     */
    protected function restorePassword($submitted)
    {
        if (isset($submitted['email'])) {
            $this->resetLink($submitted);
            return;
        }

        if (isset($submitted['password'])) {
            $this->newPassword($submitted);
        }

        return;
    }

    /**
     * Sets a reset password data
     * @param array $submitted
     */
    protected function resetLink($submitted)
    {
        $user = $submitted['user'];

        $token = Tool::randomString();
        $lifetime = (int) $this->config->get('user_reset_password_lifespan', 86400);

        $user['data']['reset_password'] = array(
            'token' => $token,
            'expires' => GC_TIME + $lifetime,
        );

        $this->user->update($user['user_id'], array('data' => $user['data']));
        $this->notification->set('user_reset_password', array($user));
        $this->redirect('forgot', $this->text('Password reset link has been sent to %email', array(
                    '%email' => $user['email'])), 'success');
    }

    /**
     * Changes a current user password
     * @param array $submitted
     */
    protected function newPassword($submitted)
    {
        $user = $submitted['user'];
        $user['password'] = $submitted['password'];

        unset($user['data']['reset_password']);
        $this->user->update($user['user_id'], $user);
        $this->notification->set('user_changed_password', array($user));
        $this->redirect('login', $this->text('Your password has been successfully changed'), 'success');
    }

}
