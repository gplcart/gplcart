<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\UserRole as UserRoleModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\traits\Order as OrderTrait,
    gplcart\core\traits\OrderComponent as OrderComponentTrait;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user accounts
 */
class Account extends FrontendController
{

    use OrderTrait,
        OrderComponentTrait;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Order model instance
     * @var \gplcart\core\models\Order $order
     */
    protected $order;

    /**
     * User role model instance
     * @var \gplcart\core\models\UserRole $role
     */
    protected $role;

    /**
     * Price rule model instance
     * @var \gplcart\core\models\PriceRule $pricerule
     */
    protected $pricerule;

    /**
     * Payment model instance
     * @var \gplcart\core\models\Payment $payment
     */
    protected $payment;

    /**
     * Shipping model instance
     * @var \gplcart\core\models\Shipping $shipping
     */
    protected $shipping;

    /**
     * Pager limit
     * @var array
     */
    protected $data_limit;

    /**
     * An array of user data
     * @var array
     */
    protected $data_user = array();

    /**
     * An array of order data
     * @var array
     */
    protected $data_order = array();

    /**
     * An array of address data
     * @var array
     */
    protected $data_address = array();

    /**
     * @param AddressModel $address
     * @param OrderModel $order
     * @param UserRoleModel $role
     * @param PriceRuleModel $pricerule
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(AddressModel $address, OrderModel $order, UserRoleModel $role,
            PriceRuleModel $pricerule, PaymentModel $payment, ShippingModel $shipping)
    {
        parent::__construct();

        $this->role = $role;
        $this->order = $order;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
        $this->pricerule = $pricerule;
    }

    /**
     * Displays the order overview page
     * @param integer $user_id
     * @param integer $order_id
     */
    public function orderAccount($user_id, $order_id)
    {
        $this->setUserAccount($user_id);
        $this->setOrderAccount($order_id);

        $this->setTitleOrderAccount();
        $this->setBreadcrumbOrderAccount();

        $this->setData('user', $this->data_user);

        $this->setDataPanelSummaryOrderAccount();
        $this->setDataPanelComponentsOrderAccount();
        $this->setDataPanelPaymentAddressOrderAccount();
        $this->setDataPanelShippingAddressOrderAccount();

        $this->outputOrderAccount();
    }

    /**
     * Sets the summary panel on the order overview page
     */
    protected function setDataPanelSummaryOrderAccount()
    {
        $this->setData('summary', $this->render('account/order/summary', array('order' => $this->data_order)));
    }

    /**
     * Sets the order components panel on the order overview page
     */
    protected function setDataPanelComponentsOrderAccount()
    {
        $this->prepareOrderComponentCartTrait($this->data_order, $this, $this->price);
        $this->prepareOrderComponentPriceRuleTrait($this->data_order, $this, $this->price, $this->pricerule);
        $this->prepareOrderComponentPaymentTrait($this->data_order, $this, $this->price, $this->payment, $this->order);
        $this->prepareOrderComponentShippingTrait($this->data_order, $this, $this->price, $this->shipping, $this->order);

        ksort($this->data_order['data']['components']);

        $data = array('components' => $this->data_order['data']['components'], 'order' => $this->data_order);
        $html = $this->render('account/order/components', $data);
        $this->setData('components', $html);
    }

    /**
     * Sets the shipping address panel on the order overview page
     */
    protected function setDataPanelShippingAddressOrderAccount()
    {
        $html = $this->render('account/order/shipping_address', array('order' => $this->data_order));
        $this->setData('shipping_address', $html);
    }

    /**
     * Sets payment address panel on the order overview page
     */
    protected function setDataPanelPaymentAddressOrderAccount()
    {
        $html = $this->render('account/order/payment_address', array('order' => $this->data_order));
        $this->setData('payment_address', $html);
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleOrderAccount()
    {
        $this->setTitle($this->text('Order #@order_id', array('@order_id' => $this->data_order['order_id'])));
    }

    /**
     * Sets breadcrumbs on the order overview page
     */
    protected function setBreadcrumbOrderAccount()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Orders'),
            'url' => $this->url("account/{$this->data_user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Render and output the order overview page
     */
    protected function outputOrderAccount()
    {
        $this->output('account/order/order');
    }

    /**
     * Sets an order data
     * @param integer $order_id
     */
    protected function setOrderAccount($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        $this->prepareOrderAccount($order);
        $this->data_order = $order;
    }

    /**
     * Prepare an array of order data
     * @param array $order
     */
    protected function prepareOrderAccount(array &$order)
    {
        $this->prepareOrderTotalTrait($order, $this->price);
        $this->prepareOrderAddressTrait($order, $this->address);
        $this->prepareOrderStoreTrait($order, $this->store, $this);
        $this->prepareOrderStatusTrait($order, $this->order, $this);
        $this->prepareOrderPaymentTrait($order, $this->payment, $this);
        $this->prepareOrderShippingTrait($order, $this->shipping, $this);
    }

    /**
     * Displays the user account page
     * @param integer $user_id
     */
    public function indexAccount($user_id)
    {
        $this->setUserAccount($user_id);

        $this->setTitleIndexAccount();
        $this->setBreadcrumbIndexAccount();

        $this->setFilter();
        $this->setPagerOrderIndexAccount();

        $this->setData('user', $this->data_user);
        $this->setData('orders', $this->getListOrderAccount());

        $this->outputIndexAccount();
    }

    /**
     * Sets a user data
     * @param integer $user_id
     */
    protected function setUserAccount($user_id)
    {
        $this->data_user = $this->user->get($user_id);

        if (empty($this->data_user)) {
            $this->outputHttpStatus(404);
        }

        if (empty($this->data_user['status']) && !$this->access('user')) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Sets pager
     * @return array
     */
    protected function setPagerOrderIndexAccount()
    {
        $options = array(
            'count' => true,
            'user_id' => $this->data_user['user_id']
        );

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->order->getList($options),
            'limit' => $this->config('account_order_limit', 10)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of orders for the customer
     * @return array
     */
    protected function getListOrderAccount()
    {
        $conditions = array(
            'order' => 'desc',
            'sort' => 'created',
            'limit' => $this->data_limit) + $this->query_filter;

        $conditions['user_id'] = $this->data_user['user_id'];
        $orders = (array) $this->order->getList($conditions);

        foreach ($orders as &$order) {
            $this->prepareOrderAccount($order);
        }

        return $orders;
    }

    /**
     * Sets breadcrumbs on the account page
     */
    protected function setBreadcrumbIndexAccount()
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
        $this->setTitle($this->text('Orders'));
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
        $this->setUserAccount($user_id);
        $this->controlAccessEditAccount();

        $this->setTitleEditAccount();
        $this->setBreadcrumbEditAccount();

        $this->setData('user', $this->data_user);

        $this->submitEditAccount();
        $this->outputEditAccount();
    }

    /**
     * Controls user access to the edit account page
     */
    protected function controlAccessEditAccount()
    {
        if ($this->data_user['user_id'] != $this->uid && !$this->access('user_edit')) {
            $this->outputHttpStatus(403);
        }
    }

    /**
     * Handles the submitted user account settings
     */
    protected function submitEditAccount()
    {
        if ($this->isPosted('save') && $this->validateEditAccount()) {
            $this->updateAccount();
        }
    }

    /**
     * Validates a submitted user
     * @return boolean
     */
    protected function validateEditAccount()
    {
        $this->setSubmitted('user', null, false);

        $this->filterSubmitted(array('name', 'email', 'password', 'password_old'));

        $this->setSubmitted('update', $this->data_user);
        $this->setSubmitted('user_id', $this->data_user['user_id']);

        $this->validateComponent('user');

        return !$this->hasErrors();
    }

    /**
     * Updates a user
     */
    protected function updateAccount()
    {
        $this->controlAccessEditAccount();

        $this->user->update($this->data_user['user_id'], $this->getSubmitted());
        $this->redirect('', $this->text('Account has been updated'), 'success');
    }

    /**
     * Sets breadcrumbs on the edit account page
     */
    protected function setBreadcrumbEditAccount()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Account'),
            'url' => $this->url("account/{$this->data_user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the edit account page
     */
    protected function setTitleEditAccount()
    {
        $this->setTitle($this->text('Edit account'));
    }

    /**
     * Render and output the edit account page
     */
    protected function outputEditAccount()
    {
        $this->output('account/edit');
    }

    /**
     * Displays the address overview page
     * @param integer $user_id
     */
    public function listAddressAccount($user_id)
    {
        $this->setUserAccount($user_id);

        $this->setTitleListAddressAccount();
        $this->setBreadcrumbListAddressAccount();

        $this->actionAddressAccount();

        $this->setData('user', $this->data_user);
        $this->setData('addresses', $this->getListAddressAccount());

        $this->outputListAddressAccount();
    }

    /**
     * Returns an array of addresses
     * @return array
     */
    protected function getListAddressAccount()
    {
        $addresses = $this->address->getTranslatedList($this->data_user['user_id']);

        $prepared = array();
        foreach ($addresses as $address_id => $items) {
            $prepared[$address_id]['items'] = $items;
            $prepared[$address_id]['locked'] = !$this->address->canDelete($address_id);
        }

        return $prepared;
    }

    /**
     * Deletes an address
     */
    protected function actionAddressAccount()
    {
        $key = 'delete';
        $this->controlToken($key);
        $address_id = $this->getQuery($key);

        if (empty($address_id)) {
            return null;
        }

        $this->controlAccessEditAccount();

        if ($this->address->delete($address_id)) {
            $message = $this->text('Address has been deleted');
            $this->redirect('', $message, 'success');
        }

        $message = $this->text('Unable to delete');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Sets breadcrumbs on the address overview page
     */
    protected function setBreadcrumbListAddressAccount()
    {
        $breadcrumbs = array();

        $breadcrumbs[] = array(
            'url' => $this->url('/'),
            'text' => $this->text('Shop')
        );

        $breadcrumbs[] = array(
            'text' => $this->text('Account'),
            'url' => $this->url("account/{$this->data_user['user_id']}")
        );

        $this->setBreadcrumbs($breadcrumbs);
    }

    /**
     * Sets titles on the address overview page
     */
    protected function setTitleListAddressAccount()
    {
        $this->setTitle($this->text('Addresses'));
    }

    /**
     * Render and output the address overview page
     */
    protected function outputListAddressAccount()
    {
        $this->output('account/addresses');
    }

}
