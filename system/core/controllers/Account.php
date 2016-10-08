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
use core\models\PriceRule as ModelsPriceRule;
use core\models\Payment as ModelsPayment;
use core\models\Shipping as ModelsShipping;

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
     * Price rule model instance
     * @var \core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Payment model instance
     * @var \core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Constructor
     * @param ModelsAddress $address
     * @param ModelsCountry $country
     * @param ModelsState $state
     * @param ModelsOrder $order
     * @param ModelsUserRole $role
     * @param ModelsPriceRule $pricerule
     * @param ModelsPayment $payment
     * @param ModelsShipping $shipping
     */
    public function __construct(ModelsAddress $address, ModelsCountry $country,
            ModelsState $state, ModelsOrder $order, ModelsUserRole $role,
            ModelsPriceRule $pricerule, ModelsPayment $payment,
            ModelsShipping $shipping)
    {
        parent::__construct();

        $this->role = $role;
        $this->state = $state;
        $this->order = $order;
        $this->country = $country;
        $this->address = $address;
        $this->pricerule = $pricerule;
        $this->payment = $payment;
        $this->shipping = $shipping;
    }

    /**
     * Displays the customer account page
     * @param integer $user_id
     */
    public function indexAccount($user_id)
    {
        $user = $this->getUserAccount($user_id);
        $default_limit = $this->config('account_order_limit', 5);

        $query = $this->getFilterQuery();
        $total = $this->getTotalOrderAccount($user_id);
        $limit = $this->setPager($total, $query, $default_limit);
        $orders = $this->getListOrderAccount($user_id, $limit, $query);

        $this->setData('user', $user);
        $this->setData('orders', $orders);

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
            'user_id' => $user_id
        );

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

        foreach ($orders as $order_id => &$order) {

            $order['cart'] = $this->cart->getList(array('order_id' => $order_id));
            $order['total_formatted'] = $this->price->format($order['total'], $order['currency']);

            $components = $this->getOrderComponentsAccount($order);
            $address = $this->address->get($order['shipping_address']);
            $translated_address = $this->address->getTranslated($address, true);

            $data = array(
                'order' => $order,
                'components' => $components,
                'shipping_address' => $translated_address
            );

            $order['rendered'] = $this->render('account/order', $data);
        }

        return $orders;
    }

    /**
     * Returns an array of order components
     * @param array $order
     * @return array
     */
    public function getOrderComponentsAccount(array $order)
    {
        $components = array();
        foreach ($order['data']['components'] as $type => $value) {
            $this->setComponentCartAccount($components, $type, $value, $order);
            $this->setComponentMethodAccount($components, $type, $value, $order);
            $this->setComponentRuleAccount($components, $type, $value);
        }

        ksort($components);
        return $components;
    }

    /**
     * Sets rendered component "Cart"
     * @param array $components
     * @param string $type
     * @param array $component_cart
     * @param array $order
     */
    protected function setComponentCartAccount(array &$components, $type,
            $component_cart, array $order)
    {
        if ($type === 'cart') {

            foreach ($component_cart as $sku => $price) {
                $order['cart'][$sku]['price_formatted'] = $this->price->format($price, $order['currency']);
            }

            $html = $this->render('backend|sale/order/panes/components/cart', array('order' => $order));
            $components['cart'] = $html;
        }
    }

    /**
     * Sets a rendered payment/shipping component
     * @param array $components
     * @param string $type
     * @param integer $price
     * @param array $order
     * @return null
     */
    protected function setComponentMethodAccount(array &$components, $type,
            $price, array $order)
    {
        if (!in_array($type, array('shipping', 'payment'))) {
            return null;
        }

        $method = $this->{$type}->getMethod();
        $method['name'] = isset($method['name']) ? $method['name'] : $this->text('Unknown');

        if (abs($price) == 0) {
            $price = 0; // No negative values
        }

        $method['price_formatted'] = $this->price->format($price, $order['currency']);
        $html = $this->render('backend|sale/order/panes/components/method', array('method' => $method));

        $components[$type] = $html;
        return null;
    }

    /**
     * Sets a rendered price rule component
     * @param array $components
     * @param integer $rule_id
     * @param integer $price
     * @return null
     */
    protected function setComponentRuleAccount(array &$components, $rule_id,
            $price)
    {
        if (!is_numeric($rule_id)) {
            return null;
        }

        if (abs($price) == 0) {
            $price = 0;
        }

        $rule = $this->pricerule->get($rule_id);

        $data = array(
            'rule' => $rule,
            'price' => $this->price->format($price, $rule['currency'])
        );

        $html = $this->render('backend|sale/order/panes/components/rule', $data);
        $components['rule'][$rule_id] = $html;
        return null;
    }

    /**
     * Sets breadcrumbs on the account page
     * @param array $user
     */
    protected function setBreadcrumbIndexAccount(array $user)
    {
        $breadcrumb = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $this->setBreadcrumb($breadcrumb);
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
     * Controls user access to the edit account page
     * @param array $user
     */
    protected function controlAccessEditAccount(array $user)
    {
        if ($this->isSuperadmin($user['user_id']) && !$this->isSuperadmin()) {
            $this->outputError(403);
        }
    }

    /**
     * Saves submitted user account settings
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
        $breadcrumbs = array();

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

        $this->deleteAddressAccount();

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
     * Deletes an address
     */
    protected function deleteAddressAccount()
    {
        $address_id = (int) $this->request->get('delete');

        if (empty($address_id)) {
            return;
        }

        $deleted = $this->address->delete($address_id);

        if ($deleted) {
            $message = $this->text('Address has been deleted');
            $this->redirect('', $message, 'success');
        }

        $message = $this->text('Address cannot be deleted');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Sets breadcrumbs on the address list page
     * @param array $user
     */
    protected function setBreadcrumbListAddressAccount(array $user)
    {
        $breadcrumbs = array();

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

        $this->outputEditAddressFormAccount();

        $this->setData('user', $user);
        $this->setData('address', $address);

        $this->submitAddressAccount($user, $address);

        $this->setDataEditAddressAccount();

        $this->setTitleEditAddressAccount();
        $this->outputEditAddressAccount();
    }

    /**
     * Sets template variables
     */
    protected function setDataEditAddressAccount()
    {
        $address = $this->getData('address');
        $form = $this->getEditAddressFormAccount($address);
        $this->setData('address_form', $form);
    }

    /**
     * Returns rendered address edit form
     * @param array $address
     * @return string
     */
    protected function getEditAddressFormAccount($address)
    {
        $country = isset($address['country']) ? $address['country'] : '';

        $format = $this->country->getFormat($country, true);
        $countries = $this->country->getNames(true);

        $options = array('status' => 1, 'country' => $country);
        $states = $this->state->getList($options);

        $format_country = empty($format['country']) ? array() : $format['country'];
        $format_state_id = empty($format['state_id']) ? array() : $format['state_id'];

        unset($format['country'], $format['state_id']);

        $data = array(
            'states' => $states,
            'format' => $format,
            'address' => $address,
            'countries' => $countries,
            'format_country' => $format_country,
            'format_state_id' => $format_state_id
        );

        return $this->render('account/address/form', $data);
    }

    /**
     * Displays edit address form
     */
    protected function outputEditAddressFormAccount()
    {
        $code = (string) $this->request->post('country');

        if (empty($code)) {
            return;
        }

        $country = $this->country->get($code);

        if (empty($country['status'])) {
            return;
        }

        $form = $this->getEditAddressFormAccount(array('country' => $code));
        $this->response->html($form);
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
        // Add submitted values to the "format" key
        $this->setSubmitted('format', $this->getSubmitted());

        $this->addValidator('format', array(
            'country_format' => array()
        ));

        $this->setValidators($user);

        // The "format" was used only for validation
        $this->unsetSubmitted('format');

        $this->setSubmitted('store_id', $this->store_id);
        $this->setSubmitted('user_id', $user['user_id']);
    }

    /**
     * Adds an address
     * @param array $user
     */
    protected function addAddressAccount(array $user)
    {
        $address = $this->getSubmitted();

        $result = $this->address->add($address);
        $this->address->controlLimit($user['user_id']);

        if (empty($result)) {
            $message = $this->text('Address has not been added');
            $this->redirect('', $message, 'warning');
        }

        $message = $this->text('New address has been added');
        $this->redirect("account/{$user['user_id']}/address", $message, 'success');
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
