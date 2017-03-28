<?php

/**
 * @package GPL Cart core
 * @author Iurii Makukh <gplcart.software@gmail.com>
 * @copyright Copyright (c) 2015, Iurii Makukh
 * @license https://www.gnu.org/licenses/gpl.html GNU/GPLv3
 */

namespace gplcart\core\controllers\frontend;

use gplcart\core\models\Order as OrderModel,
    gplcart\core\models\State as StateModel,
    gplcart\core\models\Payment as PaymentModel,
    gplcart\core\models\Address as AddressModel,
    gplcart\core\models\Country as CountryModel,
    gplcart\core\models\UserRole as UserRoleModel,
    gplcart\core\models\Shipping as ShippingModel,
    gplcart\core\models\PriceRule as PriceRuleModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;

/**
 * Handles incoming requests and outputs data related to user accounts
 */
class Account extends FrontendController
{

    use \gplcart\core\traits\ControllerOrder;

    /**
     * Address model instance
     * @var \gplcart\core\models\Address $address
     */
    protected $address;

    /**
     * Country model instance
     * @var \gplcart\core\models\Country $country
     */
    protected $country;

    /**
     * State model instance
     * @var \gplcart\core\models\State $state
     */
    protected $state;

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
     * The current user
     * @var array
     */
    protected $data_user = array();

    /**
     * The current order
     * @var array
     */
    protected $data_order = array();

    /**
     * The current address
     * @var array
     */
    protected $data_address = array();

    /**
     * Constructor
     * @param AddressModel $address
     * @param CountryModel $country
     * @param StateModel $state
     * @param OrderModel $order
     * @param UserRoleModel $role
     * @param PriceRuleModel $pricerule
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(AddressModel $address, CountryModel $country,
            StateModel $state, OrderModel $order, UserRoleModel $role,
            PriceRuleModel $pricerule, PaymentModel $payment,
            ShippingModel $shipping)
    {
        parent::__construct();

        $this->role = $role;
        $this->state = $state;
        $this->order = $order;
        $this->country = $country;
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

        $this->setDataPaneSummaryAccount();
        $this->setDataPaneComponentsAccount();
        $this->setDataPanePaymentAddressAccount();
        $this->setDataPaneShippingAddressAccount();

        $this->outputOrderAccount();
    }

    /**
     * Sets summary pane on the order overview page
     */
    protected function setDataPaneSummaryAccount()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('account/order/panes/summary', $data);
        $this->setData('pane_summary', $html);
    }

    /**
     * Sets order components pane on the order overview page
     */
    protected function setDataPaneComponentsAccount()
    {
        $templates = 'account/order/components';
        $components = $this->prepareOrderComponentsTrait($this, $this->data_order, $templates);

        $data = array('components' => $components, 'order' => $this->data_order);
        $html = $this->render('account/order/panes/components', $data);
        $this->setData('pane_components', $html);
    }

    /**
     * Sets shipping address pane on the order overview page
     */
    protected function setDataPaneShippingAddressAccount()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('account/order/panes/shipping_address', $data);
        $this->setData('pane_shipping_address', $html);
    }

    /**
     * Sets payment address pane on the order overview page
     */
    protected function setDataPanePaymentAddressAccount()
    {
        $data = array('order' => $this->data_order);
        $html = $this->render('account/order/panes/payment_address', $data);
        $this->setData('pane_payment_address', $html);
    }

    /**
     * Sets titles on the order overview page
     */
    protected function setTitleOrderAccount()
    {
        $vars = array('@order_id' => $this->data_order['order_id']);
        $title = $this->text('Order #@order_id', $vars);
        $this->setTitle($title);
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
     * Outputs the order overview page
     */
    protected function outputOrderAccount()
    {
        $this->output('account/order/order');
    }

    /**
     * Sets the current order
     * @param integer $order_id
     * @return array
     */
    protected function setOrderAccount($order_id)
    {
        $order = $this->order->get($order_id);

        if (empty($order)) {
            $this->outputHttpStatus(404);
        }

        return $this->data_order = $this->prepareOrderTrait($this, $order);
    }

    /**
     * Displays the customer account page
     * @param integer $user_id
     */
    public function indexAccount($user_id)
    {
        $this->setUserAccount($user_id);

        $this->setTitleIndexAccount();
        $this->setBreadcrumbIndexAccount();

        $query = $this->getFilterQuery();
        $total = $this->getTotalOrderAccount();

        $default_limit = $this->config('account_order_limit', 10);
        $limit = $this->setPager($total, $query, $default_limit);

        $this->setData('user', $this->data_user);
        $this->setData('orders', $this->getListOrderAccount($limit, $query));

        $this->outputIndexAccount();
    }

    /**
     * Returns a user
     * @param integer $user_id
     * @return array
     */
    protected function setUserAccount($user_id)
    {
        $user = $this->user->get($user_id);

        if (empty($user)) {
            $this->outputHttpStatus(404);
        }

        if (empty($user['status']) && !$this->access('user')) {
            $this->outputHttpStatus(403);
        }

        return $this->data_user = $user;
    }

    /**
     * Returns a number of total orders for the customer
     * @return integer
     */
    protected function getTotalOrderAccount()
    {
        $options = array(
            'count' => true,
            'user_id' => $this->data_user['user_id']
        );

        return (int) $this->order->getList($options);
    }

    /**
     * Returns an array of orders for the customer
     * @param array $limit
     * @param array $conditions
     * @return array
     */
    protected function getListOrderAccount(array $limit, array $conditions)
    {
        $conditions += array(
            'order' => 'desc',
            'limit' => $limit,
            'sort' => 'created'
        );

        $conditions['user_id'] = $this->data_user['user_id'];
        $orders = (array) $this->order->getList($conditions);

        foreach ($orders as &$order) {
            $this->prepareOrderTrait($this, $order);
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
        $this->setData('roles', $this->role->getList());
        $this->setData('stores', $this->store->getNames());

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
     * Saves submitted user account settings
     */
    protected function submitEditAccount()
    {
        if ($this->isPosted('save') && $this->validateAccount()) {
            $this->updateAccount();
        }
    }

    /**
     * Validates a user
     * @return boolean
     */
    protected function validateAccount()
    {
        $this->setSubmitted('user', null, 'raw');
        $this->setSubmitted('update', $this->data_user);
        $this->setSubmitted('user_id', $this->data_user['user_id']);

        $this->validate('user');

        return !$this->hasErrors('user');
    }

    /**
     * Updates a user with submitted values
     */
    protected function updateAccount()
    {
        $this->controlAccessEditAccount();

        $values = $this->getSubmitted();
        $this->user->update($this->data_user['user_id'], $values);

        $message = $this->text('Account has been updated');
        $this->redirect('', $message, 'success');
    }

    /**
     * Sets breadcrumbs on the account edit form
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
        $this->setUserAccount($user_id);

        $this->setTitleListAddressAccount();
        $this->setBreadcrumbListAddressAccount();

        $this->deleteAddressAccount();

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
     * @return null
     */
    protected function deleteAddressAccount()
    {
        $address_id = (int) $this->request->get('delete');

        if (empty($address_id)) {
            return null;
        }

        $this->controlAccessEditAccount();

        $deleted = $this->address->delete($address_id);

        if ($deleted) {
            $message = $this->text('Address has been deleted');
            $this->redirect('', $message, 'success');
        }

        $message = $this->text('Unable to delete this address');
        $this->redirect('', $message, 'warning');
    }

    /**
     * Sets breadcrumbs on the address list page
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
        $this->output('account/addresses');
    }

}
