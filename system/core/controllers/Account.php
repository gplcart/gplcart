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
     * Product model instance
     * @var \core\models\Product $product
     */
    protected $product;

    /**
     * Used for validation
     * @var boolean
     */
    protected $check_old_password = false;

    /**
     * Constructor
     * @param Address $address
     * @param Country $country
     * @param State $state
     * @param Order $order
     * @param Price $price
     * @param Bookmark $bookmark
     * @param UserRole $role
     * @param Product $product
     */
    public function __construct(Address $address, Country $country,
                                State $state, Order $order, Price $price,
                                Bookmark $bookmark, UserRole $role,
                                Product $product)
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
        $user = $this->getUser($user_id);

        // Only superadmin can edit its own account
        if ($this->user->isSuperadmin($user_id) && !$this->user->isSuperadmin()) {
            $this->outputError(403);
        }

        $this->data['user'] = $user;
        $this->data['roles'] = $this->role->getList();
        $this->data['stores'] = $this->store->getNames();

        if ($this->request->post('save')) {
            $this->submitEdit($user);
        }

        if ($this->request->post('delete')) {
            $this->submitDelete($user);
        }

        $this->setTitleEdit();
        $this->outputEdit();
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEdit()
    {
        $this->setTitle($this->text('Edit account'), false);
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEdit()
    {
        $this->output('account/edit');
    }

    /**
     * Deletes a user
     * @param array $user
     */
    protected function submitDelete($user)
    {
        $this->controlAccess('user_delete');

        $result = $this->user->delete($user['user_id']);

        $redirect = 'admin/user';
        $message = 'User %name has been deleted';
        $variables = array('%name' => $user['name']);
        $message_type = 'success';

        if (empty($result)) {
            $redirect = '';
            $message = 'Unable to delete user %name. The most probable reason - it is used by one or more orders';
            $message_type = 'danger';
        }

        $this->redirect($redirect, $this->text($message, $variables), $message_type);
    }

    /**
     * Saves user account settings
     * @param array $user
     * @return null
     */
    protected function submitEdit($user)
    {
        $this->submitted = $this->request->post('user', array(), 'raw');
        $this->validateUser($user);

        if ($this->formErrors()) {
            $this->data['user'] = $this->submitted + array('user_id' => $user['user_id']);
            return;
        }

        $this->user->update($user['user_id'], $this->submitted);
        $this->redirect('', $this->text('Account has been updated'), 'success');
    }

    /**
     * Displays the addresses overview page
     * @param integer $user_id
     */
    public function addresses($user_id)
    {
        $user = $this->getUser($user_id);
        $address_id = $this->request->get('delete');

        if (!empty($address_id)) {
            $this->deleteAddress($address_id);
        }

        $this->data['user'] = $user;
        $this->data['addresses'] = $this->getAddresses($user_id);

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
            $message = $this->text('Address cannot be deleted');
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

        if (empty($result)) {
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
     * Returns an array of addresses
     * @param integer $user_id
     * @return array
     */
    protected function getAddresses($user_id)
    {
        return $this->address->getTranslatedList($user_id);
    }

    /**
     * Displays the login page
     */
    public function login()
    {
        if (!empty($this->uid)) {
            $this->url->redirect("account/{$this->uid}");
        }

        if ($this->request->post('login')) {
            $this->submitLogin();
        }

        $this->setTitleLogin();
        $this->setBreadcrumbLogin();
        $this->outputLogin();
    }

    /**
     * Sets titles on the login page
     */
    protected function setTitleLogin()
    {
        $this->setTitle($this->text('Login'));
    }

    /**
     * Sets breadcrumbs on the login page
     */
    protected function setBreadcrumbLogin()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Renders the login page
     */
    protected function outputLogin()
    {
        $this->output('login');
    }

    /**
     * Logs in a user
     */
    protected function submitLogin()
    {
        $this->controlSpam('login');
        $this->submitted = $this->request->post('user', array(), 'raw');

        $result = $this->user->login($this->submitted['email'], $this->submitted['password']);

        if (!empty($result)) {
            $this->redirect($result['redirect'], $result['message'], $result['message_type']);
        }

        $this->data['user'] = $this->submitted;
        $this->setMessage($this->text('Invalid E-mail and/or password'), 'danger');
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
            $this->submitRegister();
        }

        $limits = $this->user->getPasswordLength();

        $this->data['min_password_length'] = $limits['min'];
        $this->data['max_password_length'] = $limits['max'];

        $this->data['roles'] = $this->role->getList();
        $this->data['stores'] = $this->store->getNames();

        $this->setTitleRegister();
        $this->setBreadcrumbRegister();
        $this->outputRegister();
    }

    /**
     * Sets titles on the registration page
     */
    protected function setTitleRegister()
    {
        $this->setTitle($this->text('Register'));
    }

    /**
     * Sets breadcrumbs on the registration page
     */
    protected function setBreadcrumbRegister()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Renders the registration page
     */
    protected function outputRegister()
    {
        $this->output('register');
    }

    /**
     *
     * @return type
     */
    protected function submitRegister()
    {
        $this->controlSpam('register');
        $this->submitted = $this->request->post('user', array(), 'raw');
        $this->validateUser();

        if ($this->formErrors()) {
            $this->data['user'] = $this->submitted;
            return;
        }

        $this->submitted['admin'] = $this->access('user_add');
        $result = $this->user->register($this->submitted);
        $this->redirect($result['redirect'], $result['message'], $result['message_type']);
    }

    /**
     * Displays the user forgotten password page
     */
    public function forgot()
    {
        if (empty($this->uid)) {
            $this->url->redirect("account/{$this->uid}");
        }

        // Check password reset URL
        $user = $this->getRecoverableUser();
        $this->data['recoverable_user'] = $user;

        if ($user === false) {
            $this->redirect('forgot'); // Reset password link expired or invalid
        }

        if ($this->request->post('reset')) {
            $this->submitForgot($user);
        }

        $limits = $this->user->getPasswordLength();

        $this->data['min_password_length'] = $limits['min'];
        $this->data['max_password_length'] = $limits['max'];

        $this->setTitleForgot();
        $this->setBreadcrumbForgot();
        $this->outputForgot();
    }

    /**
     * Sets titles on the password reset page
     */
    protected function setTitleForgot()
    {
        $this->setTitle($this->text('Reset password'));
    }

    /**
     * Sets breadcrumbs on the password reset page
     */
    protected function setBreadcrumbForgot()
    {
        $this->setBreadcrumb(array('text' => $this->text('Home'), 'url' => $this->url('/')));
    }

    /**
     * Renders the password reset page templates
     */
    protected function outputForgot()
    {
        $this->output('forgot');
    }

    /**
     * Restores forgotten password
     * @param array $user
     * @return null
     */
    protected function submitForgot($user)
    {
        $this->controlSpam('forgot');
        $this->submitted = $this->request->post('user', array(), 'raw');

        $this->validateForgot($user);

        if ($this->formErrors()) {
            $this->data['user'] = $this->submitted;
            return;
        }

        if (isset($this->submitted['email'])) {
            return $this->user->resetPassword($this->submitted['user']);
        }
        
        if (isset($this->submitted['password'])) {
            return $this->user->resetPassword($this->submitted['user'], $this->submitted['password']);
        }
        
        return;
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

        if (empty($user)) {
            $this->outputError(404);
        }

        // No access for blocked customers, allow only admins
        if (empty($user['status']) && !$this->access('user_edit')) {
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
     * Validates a user
     * @param array $user
     * @return boolean
     */
    protected function validateUser($user = array())
    {
        // Registration
        if (empty($user['user_id']) && empty($this->uid)) {
            $this->submitted['status'] = $this->config->get('user_registration_status', 1);
            $this->submitted['store_id'] = $this->store_id;
        }

        if (!$this->validateEmail($user)) {
            return false;
        }

        if (empty($user['user_id']) && empty($this->submitted['name'])) {
            $this->submitted['name'] = strtok($this->submitted['email'], '@');
        }

        $this->validateName();
        return $this->validatePasswordBoth();
    }

    /**
     * Validates both old and new passwords
     * @param array $user
     * @return boolean
     */
    protected function validatePasswordBoth($user)
    {
        if (empty($user['user_id']) && empty($this->submitted['password'])) {
            $this->data['form_errors']['password'] = $this->text('Required field');
            return false;
        }

        if (!empty($this->submitted['password']) && isset($user['user_id'])) {
            $this->check_old_password = true;
            $this->validatePassword($this->submitted['password']);
        }

        if (isset($this->data['form_errors'])) {
            return false;
        }

        if (!$this->check_old_password || $this->access('user_edit')) {
            return true;
        }

        if (empty($this->submitted['password_old']) || empty($user['hash'])) {
            $this->data['form_errors']['password_old'] = $this->text('The specified old password does not match the current password');
            return false;
        }

        if (!Tool::hashEquals($user['hash'], Tool::hash($this->submitted['password_old'], $user['hash'], false))) {
            $this->data['form_errors']['password_old'] = $this->text('The specified old password does not match the current password');
            return false;
        }

        return true;
    }

    /**
     * Validates user name
     * @return boolean
     */
    protected function validateName()
    {
        if (empty($this->submitted['name']) || mb_strlen($this->submitted['name']) > 255) {
            $this->data['form_errors']['name'] = $this->text('Content must be %min - %max characters long', array(
                '%min' => 1, '%max' => 255));
            return false;
        }

        return true;
    }

    /**
     * Validates an e-mail
     * @param array $user
     * @return boolean
     */
    protected function validateEmail($user)
    {
        if (isset($this->submitted['email']) && filter_var($this->submitted['email'], FILTER_VALIDATE_EMAIL)) {
            $check_email_exists = true;
            if (isset($user['email']) && ($this->submitted['email'] === $user['email'])) {
                $check_email_exists = false;
            }

            if ($check_email_exists && $this->user->getByEmail($this->submitted['email'])) {
                $this->data['form_errors']['email'] = $this->text('Please provide another E-mail');
                return false;
            }

            $this->check_old_password = $check_email_exists;
            return true;
        }

        $this->data['form_errors']['email'] = $this->text('Invalid E-mail');
        return false;
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
     * @param mixed $user
     * @return boolean
     */
    protected function validateForgot(array $user)
    {
        if (isset($this->submitted['email'])) {
            $user = $this->user->getByEmail($this->submitted['email']);
            if (empty($user['status'])) {
                $this->data['form_errors']['email'] = $this->text('Please provide another E-mail');
                return false;
            }

            $this->submitted['user'] = $user;
            return true;
        }

        if (isset($this->submitted['password'])) {
            $this->validatePassword($this->submitted['password']);
            $this->submitted['user'] = $user;
        }

        return true;
    }
}
