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
    gplcart\core\models\Shipping as ShippingModel;
use gplcart\core\controllers\frontend\Controller as FrontendController;
use gplcart\core\traits\ItemOrder as ItemOrderTrait;

/**
 * Handles incoming requests and outputs data related to user accounts
 */
class Account extends FrontendController
{

    use ItemOrderTrait;

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
     * @param AddressModel $address
     * @param OrderModel $order
     * @param PaymentModel $payment
     * @param ShippingModel $shipping
     */
    public function __construct(AddressModel $address, OrderModel $order, PaymentModel $payment,
            ShippingModel $shipping)
    {
        parent::__construct();

        $this->order = $order;
        $this->address = $address;
        $this->payment = $payment;
        $this->shipping = $shipping;
    }

    /**
     * Page callback
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
        $conditions = array(
            'count' => true,
            'user_id' => $this->data_user['user_id']
        );

        $pager = array(
            'query' => $this->query_filter,
            'total' => (int) $this->order->getList($conditions),
            'limit' => $this->config('account_order_limit', 10)
        );

        return $this->data_limit = $this->setPager($pager);
    }

    /**
     * Returns an array of orders for the user
     * @return array
     */
    protected function getListOrderAccount()
    {
        $conditions = $this->query_filter;
        $conditions['order'] = 'desc';
        $conditions['sort'] = 'created';
        $conditions['limit'] = $this->data_limit;
        $conditions['user_id'] = $this->data_user['user_id'];

        $orders = (array) $this->order->getList($conditions);
        return $this->prepareListOrderAccount($orders);
    }

    /**
     * Prepare an array of orders
     * @param array $orders
     * @return array
     */
    protected function prepareListOrderAccount(array $orders)
    {
        foreach ($orders as &$order) {
            $this->setItemTotalFormatted($order, $this->price);
            $this->setItemOrderAddress($order, $this->address);
            $this->setItemOrderStoreName($order, $this->store);
            $this->setItemOrderStatusName($order, $this->order);
            $this->setItemOrderPaymentName($order, $this->payment);
            $this->setItemOrderShippingName($order, $this->shipping);
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
     * Page callback
     * Displays the edit account page
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
     * Controls the user access to the edit account page
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
     * Validates the submitted data
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

        if ($this->user->update($this->data_user['user_id'], $this->getSubmitted())) {
            $this->redirect('', $this->text('Account has been updated'), 'success');
        }

        $this->redirect('', $this->text('Account has not been updated'), 'warning');
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

}
