<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\classes\Tool;
use core\models\State as ModelsState;
use core\models\Order as ModelsOrder;
use core\models\Address as ModelsAddress;
use core\models\Country as ModelsCountry;
use core\models\UserRole as ModelsUserRole;
use core\controllers\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user accounts
 */
class Account extends FrontendController
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
     * Used for validation
     * @var boolean
     */
    protected $check_old_password = false;

    /**
     * Constructor
     * @param ModelsAddress $address
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsOrder $order
     * @param ModelsUserRole $role
     */
    public function __construct(ModelsAddress $address, ModelsCountry $country,
            ModelsState $state, ModelsOrder $order, ModelsUserRole $role)
    {
        parent::__construct();

        $this->role = $role;
        $this->state = $state;
        $this->order = $order;
        $this->country = $country;
        $this->address = $address;
    }

    /**
     * Displays the customer account page
     * @param integer $user_id
     */
    public function indexAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);
        $default_limit = $this->config->get('account_order_limit', 10);
        
        $query = $this->getFilterQuery();
        $total = $this->getTotalOrderAccount($user_id);
        $limit = $this->setPager($total, $query, $default_limit);
        $orders = $this->getListOrderAccount($user_id, $limit, $query);
        
        $this->setData('user', $user);
        $this->setData('orders', $orders);

        $filters = array('order_id', 'created', 'total', 'status');
        $this->setFilter($filters, $query);

        $this->setTitleIndexAccount();
        $this->outputIndexAccount();
    }

    /**
     * Displays the customer edit account page
     * @param integer $user_id
     */
    public function editAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);

        $this->controlAccessEditAccount();
        
        $roles = $this->role->getList();
        $stores = $this->store->getNames();
        
        $this->setData('user', $user);
        $this->setData('roles', $roles);
        $this->setData('stores', $stores);

        $this->submitEditAccount($user);

        $this->setTitleEditAccount();
        $this->outputEditAccount();
    }
    
    /**
     * Displays 403 error page if the current user has no access to edit the page
     * @param integer $user_id
     */
    protected function controlAccessEditAccount($user_id)
    {
        if ($this->isSuperadmin($user_id) && !$this->isSuperadmin()) {
            $this->outputError(403);
        }
    }

    /**
     * Displays the addresses overview page
     * @param integer $user_id
     */
    public function listAddressAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);
        $addresses = $this->getListAddressAccount($user_id);
        
        $this->actionAccount($user);
        
        $this->setData('user', $user);
        $this->setData('addresses', $addresses);

        $this->setTitleListAddressAccount();
        $this->outputListAddressAccount();
    }
    
    /**
     * Applies an action to user addresses
     * @param array $user
     */
    protected function actionAccount(array $user)
    {
        $address_id = (int) $this->request->get('delete');

        if (!empty($address_id)) {
            $this->deleteAddressAccount($address_id);
        }
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEditAccount()
    {
        $this->output('account/edit');
    }

    /**
     * Renders the addresses overview page
     */
    protected function outputListAddressAccount()
    {
        $this->output('account/address/list');
    }

    /**
     * Renders the edit address page
     */
    protected function outputEditAddressAccount()
    {
        $this->output('account/address/edit');
    }

    /**
     * Renders the account page templates
     */
    protected function outputIndexAccount()
    {
        $this->output('account/account');
    }

    /**
     * Returns an address
     * @param integer $address_id
     * @return array
     */
    protected function getAddressAccount($address_id)
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
    protected function getListStateAccount($country)
    {
        $options = array(
            'status' => 1,
            'country' => $country
        );
        
        return $this->state->getList($options);
    }

    /**
     * Returns an array of the coutry format data
     * @param string $country
     * @return array
     */
    protected function getCountryFormatAccount($country)
    {
        return $this->country->getFormat($country);
    }

    /**
     * Returns an array of country names
     * @return array
     */
    protected function getCountryNamesAccount()
    {
        return $this->country->getNames(true);
    }

    /**
     * Returns an array of addresses
     * @param integer $user_id
     * @return array
     */
    protected function getListAddressAccount($user_id)
    {
        return $this->address->getTranslatedList($user_id);
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function getUserAccount($user_id)
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
     * @param mixed $user_id
     * @param array $limit
     * @param array $query
     * @return array
     */
    protected function getListOrderAccount($user_id, array $limit, array $query)
    {
        $query += array('sort' => 'created', 'order' => 'desc', 'limit' => $limit);
        $query['user_id'] = $user_id;

        $orders = $this->order->getList($query);
        return $this->prepareOrders($orders);
    }

    /**
     * Returns a number of total orders for the customer
     * @param integer $user_id
     * @return array
     */
    protected function getTotalOrderAccount($user_id)
    {
        return $this->order->getList(array('count' => true, 'user_id' => $user_id));
    }

    /**
     * Sets titles on the account page
     */
    protected function setTitleIndexAccount()
    {
        $this->setTitle($this->text('Orders'), false);
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEditAccount()
    {
        $this->setTitle($this->text('Edit account'), false);
    }

    /**
     * Sets titles on the addresses overview page
     */
    protected function setTitleListAddressAccount()
    {
        $this->setTitle($this->text('Addresses'), false);
    }

    /**
     * Sets titles on the edit address page
     */
    protected function setTitleEditAddressAccount()
    {
        $this->setTitle($this->text('Add new address'), false);
    }

    /**
     * Validates a user
     * @param array $user
     * @return boolean
     */
    protected function validateAccount(array $user = array())
    {
        
        if(isset($user['user_id'])){
            $this->setSubmitted('user_id', $user['user_id']);
        }
        
        
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
        return $this->validatePasswordBoth($user);
    }

    /**
     * Validates both old and new passwords
     * @param array $user
     * @return boolean
     */
    protected function validatePasswordBoth(array $user)
    {
        if (empty($user['user_id'])) {
            if (empty($this->submitted['password'])) {
                $this->errors['password'] = $this->text('Required field');
                return false;
            }
        } elseif (!empty($this->submitted['password'])) {
            $this->check_old_password = true;
            $this->validatePassword($this->submitted['password']);
        }

        $errors = $this->getErrors();

        if (!empty($errors)) {
            return false;
        }

        if (!$this->check_old_password || $this->access('user_edit')) {
            return true;
        }

        if (empty($this->submitted['password_old']) || empty($user['hash'])) {
            $this->errors['password_old'] = $this->text('The specified old password does not match the current password');
            return false;
        }

        if (!Tool::hashEquals($user['hash'], Tool::hash($this->submitted['password_old'], $user['hash'], false))) {
            $this->errors['password_old'] = $this->text('The specified old password does not match the current password');
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
            $this->errors['name'] = $this->text('Content must be %min - %max characters long', array(
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
    protected function validateEmail(array $user)
    {
        if (isset($this->submitted['email']) && filter_var($this->submitted['email'], FILTER_VALIDATE_EMAIL)) {
            $check_email_exists = true;
            if (isset($user['email']) && ($this->submitted['email'] === $user['email'])) {
                $check_email_exists = false;
            }

            if ($check_email_exists && $this->user->getByEmail($this->submitted['email'])) {
                $this->errors['email'] = $this->text('Please provide another E-mail');
                return false;
            }

            //$this->check_old_password = $check_email_exists;
            return true;
        }

        $this->errors['email'] = $this->text('Invalid E-mail');
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

        $this->errors['password'] = $this->language->text('Password must be %min - %max characters long', array(
            '%min' => $limits['min'], '%max' => $limits['max']));
        return false;
    }

    /**
     * Validates a submitted address
     */
    protected function validateAddressAccount()
    {
        $this->submitted['status'] = !empty($this->submitted['status']);

        foreach ($this->country->getFormat($this->submitted['country'], true) as $field => $info) {
            if (!empty($info['required']) && (empty($this->submitted[$field]) || mb_strlen($this->submitted[$field]) > 255)) {
                $this->errors[$field] = $this->text('Content must be %min - %max characters long', array(
                    '%min' => 1, '%max' => 255));
            }
        }
    }

    /**
     * Saves user account settings
     * @param array $user
     */
    protected function submitAccount(array $user)
    {
        if(!$this->isPosted('save')){
            return;
        }
        
        $this->setSubmitted('user', null, 'raw');
        $this->validateAccount($user);
        
        if(!$this->hasErrors('user')){
            $this->updateAccount($user);
        }
    }
    
    /**
     * Updates a user with submitted values
     * @param array $user
     */
    protected function updateAccount(array $user)
    {
        $values = $this->getSubmitted();
        $this->user->update($user['user_id'], $values);

        $message = $this->text('Account has been updated');
        $this->redirect('', $message, 'success');
    }

    /**
     * Saves a user address
     * @param array $user
     * @return null
     */
    protected function submitAddressAccount(array $user)
    {
        $this->submitted = $this->request->post('address', array());

        $this->validateAddressAccount();

        $errors = $this->getErrors();

        if (!empty($errors)) {
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
     * Modifies an array of orders before rendering
     * @param array $orders
     * @return array
     */
    protected function prepareOrdersAccount(array $orders)
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
     * Deletes an address
     * @param integer $address_id
     */
    protected function deleteAddressAccount($address_id)
    {
        $deleted = $this->address->delete($address_id);
        
        if ($deleted) {
            $message = $this->text('Address cannot be deleted');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('Address has been deleted');
        $this->redirect('', $message, 'success');
    }

    /**
     * Displays edit address form
     * @param integer $user_id
     * @param integer $address_id
     */
    public function editAddressAccount($user_id, $address_id = null)
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
     * Adds an address
     * @param array $address
     * @return integer
     */
    protected function addAddressAccount(array $address)
    {
        $result = $this->address->add($address);
        $this->address->reduceLimit($address['user_id']);
        return $result;
    }

}
