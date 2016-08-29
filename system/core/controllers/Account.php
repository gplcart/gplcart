<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace core\controllers;

use core\controllers\Controller as FrontendController;
use core\models\Address as ModelsAddress;
use core\models\Country as ModelsCountry;
use core\models\Order as ModelsOrder;
use core\models\State as ModelsState;
use core\models\UserRole as ModelsUserRole;

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
        $default_limit = $this->config('account_order_limit', 10);

        $query = $this->getFilterQuery();
        $total = $this->getTotalOrderAccount($user_id);
        $limit = $this->setPager($total, $query, $default_limit);
        $orders = $this->getListOrderAccount($user_id, $limit, $query);

        $this->setData('user', $user);
        $this->setData('orders', $orders);

        $filters = array('order_id', 'created', 'total', 'status');
        $this->setFilter($filters, $query);

        $this->setBreadcrumbIndexAccount($user);
        $this->setTitleIndexAccount();
        $this->outputIndexAccount();
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

        if (empty($user['status'])) {
            $this->outputError(403);
        }

        return $user;
    }

    /**
     * Returns a number of total orders for the customer
     * @param integer $user_id
     * @return array
     */
    protected function getTotalOrderAccount($user_id)
    {
        $options = array(
            'count' => true,
            'user_id' => $user_id);

        return $this->order->getList($options);
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
        $query += array(
            'order' => 'desc',
            'limit' => $limit,
            'sort' => 'created'
        );

        $query['user_id'] = $user_id;

        $orders = $this->order->getList($query);

        foreach ($orders as &$order) {

            $address_id = $order['shipping_address'];
            $address = $this->address->get($address_id);
            $components = $this->order->getComponents($order);
            $translated_address = $this->address->getTranslated($address, true);

            $data = array(
                'order' => $order,
                'components' => $components,
                'shipping_address' => $translated_address
            );

            $order['render'] = $this->render('account/order', $data);
        }

        return $orders;
    }

    /**
     * Sets breadcrumbs on the account index page
     * @param array $user
     */
    protected function setBreadcrumbIndexAccount(array $user)
    {
        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the account page
     */
    protected function setTitleIndexAccount()
    {
        $this->setTitle($this->text('Orders'), false);
    }

    /**
     * Renders the account page templates
     */
    protected function outputIndexAccount()
    {
        $this->output('account/account');
    }

    /**
     * Displays the customer edit account page
     * @param integer $user_id
     */
    public function editAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);

        $this->controlAccessEditAccount($user);

        $roles = $this->role->getList();
        $stores = $this->store->getNames();

        $this->setData('user', $user);
        $this->setData('roles', $roles);
        $this->setData('stores', $stores);

        $this->submitEditAccount($user);

        $this->setBreadcrumbEditAccount($user);
        $this->setTitleEditAccount();
        $this->outputEditAccount();
    }

    /**
     * Displays 403 error page if the current user has no access to edit the page
     * @param array $user
     */
    protected function controlAccessEditAccount(array $user)
    {
        if ($this->isSuperadmin($user['user_id']) && !$this->isSuperadmin()) {
            $this->outputError(403);
        }
    }

    /**
     * Saves user account settings
     * @param array $user
     */
    protected function submitEditAccount(array $user)
    {
        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('user', null, 'raw');
        $this->validateAccount($user);

        if (!$this->hasErrors('user')) {
            $this->updateAccount($user);
        }
    }

    /**
     * Validates a user
     * @param array $user
     * @return boolean
     */
    protected function validateAccount(array $user = array())
    {
        $this->addValidator('email', array(
            'required' => array(),
            'email' => array(),
            'user_email_unique' => array()
        ));

        $this->addValidator('name', array(
            'length' => array('min' => 1, 'max' => 255),
            'user_name_unique' => array()
        ));

        $options = array('required' => false);
        $options += $this->user->getPasswordLength();

        $this->addValidator('password', array(
            'length' => $options
        ));

        $password = (string) $this->getSubmitted('password');

        $this->addValidator('password_old', array(
            'user_password' => array('required' => ($password !== ''))
        ));

        $this->setValidators($user);
        $this->setSubmitted('user_id', $user['user_id']);
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
     * Sets breadcrumbs on the account edit form
     * @param array $user
     */
    protected function setBreadcrumbEditAccount(array $user)
    {
        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Account'),
            'url' => $this->url("account/{$user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEditAccount()
    {
        $this->setTitle($this->text('Edit account'), false);
    }

    /**
     * Renders the edit account page templates
     */
    protected function outputEditAccount()
    {
        $this->output('account/edit');
    }

    /**
     * Displays the addresses overview page
     * @param integer $user_id
     */
    public function listAddressAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);
        $addresses = $this->getListAddressAccount($user_id);

        $this->actionAddressAccount($user);

        $this->setData('user', $user);
        $this->setData('addresses', $addresses);

        $this->setBreadcrumbListAddressAccount($user);
        $this->setTitleListAddressAccount();
        $this->outputListAddressAccount();
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
     * Applies an action to user addresses
     * @param array $user
     */
    protected function actionAddressAccount(array $user)
    {
        $address_id = (int) $this->request->get('delete');

        if (!empty($address_id)) {
            $this->deleteAddressAccount($address_id);
        }
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
     * Sets breadcrumbs on the address list page
     * @param array $user
     */
    protected function setBreadcrumbListAddressAccount(array $user)
    {
        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Account'),
            'url' => $this->url("account/{$user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the addresses overview page
     */
    protected function setTitleListAddressAccount()
    {
        $this->setTitle($this->text('Addresses'), false);
    }

    /**
     * Renders the addresses overview page
     */
    protected function outputListAddressAccount()
    {
        $this->output('account/address/list');
    }

    /**
     * Displays edit address form
     * @param integer $user_id
     * @param integer $address_id
     */
    public function editAddressAccount($user_id, $address_id = null)
    {
        $user = $this->getUserAccount($user_id);
        $address = $this->getAddressAccount($address_id);

        $this->setData('user', $user);
        $this->setData('address', $address);

        $this->submitAddressAccount($user, $address);

        $countries = $this->getCountryNamesAccount();
        $states = $this->getListStateAccount($address);
        $format = $this->getCountryFormatAccount($address);

        $this->setData('format', $format);
        $this->setData('states', $states);
        $this->setData('countries', $countries);

        $this->setTitleEditAddressAccount();
        $this->outputEditAddressAccount();
    }

    /**
     * Returns an address
     * @param integer $address_id
     * @return array
     */
    protected function getAddressAccount($address_id)
    {
        if (!is_numeric($address_id)) {
            return array('country' => $this->country->getDefault());
        }

        $address = $this->address->get($address_id);

        if (empty($address)) {
            $this->outputError(404);
        }

        return $address;
    }

    /**
     * Saves a user address
     * @param array $user
     * @return null
     */
    protected function submitAddressAccount(array $user)
    {
        if (!$this->isPosted('save')) {
            return;
        }

        $this->setSubmitted('address');
        $this->validateAddressAccount($user);

        if (!$this->hasErrors('address')) {
            $this->addAddressAccount($user);
        }
    }

    /**
     * Validates a submitted address
     */
    protected function validateAddressAccount(array $user)
    {
        $this->setSubmittedBool('status');
        $this->setSubmitted('user_id', $user['user_id']);

        $this->addValidator('format', array(
            'country_format' => array()
        ));

        $this->setValidators($user);
    }

    /**
     * Adds an address
     * @param array $user
     */
    protected function addAddressAccount(array $user)
    {
        $address = $this->getSubmitted('address');
        $result = $this->address->add($address);
        $this->address->reduceLimit($address['user_id']);

        if (empty($result)) {
            $message = $this->text('Address has not been added');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('New address has been added');
        $this->redirect("account/{$user['user_id']}/address", $message, 'success');
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
     * Returns an array of states for a given country code
     * @param string $address
     * @return array
     */
    protected function getListStateAccount(array $address)
    {
        $options = array(
            'status' => 1,
            'country' => $address['country']
        );

        return $this->state->getList($options);
    }

    /**
     * Returns an array of the coutry format data
     * @param string $address
     * @return array
     */
    protected function getCountryFormatAccount(array $address)
    {
        return $this->country->getFormat($address['country']);
    }

    /**
     * Sets titles on the edit address page
     */
    protected function setTitleEditAddressAccount()
    {
        $this->setTitle($this->text('Add new address'), false);
    }

    /**
     * Renders the edit address page
     */
    protected function outputEditAddressAccount()
    {
        $this->output('account/address/edit');
    }

}
